<?php

use Livewire\Component;
use App\Models\Participant;
use App\Models\Vote;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Cache;

new class extends Component
{
    public $participants;
    public $hasVoted = false;
    public $votedFor = null;
    public $voting_is_open = true;

    public function mount()
    {
        $this->voting_is_open = Cache::get('voting_is_open', true);
        $this->participants = Participant::orderBy('order_number')->get();
        $this->checkVote();
    }

    public function checkStatus()
    {
        $this->voting_is_open = Cache::get('voting_is_open', true);
    }

    public function checkVote()
    {
        $token = request()->cookie('voter_token');
        
        if ($token) {
            $vote = Vote::where('device_token', $token)->first();
            
            if ($vote) {
                $this->hasVoted = true;
                $this->votedFor = $vote->participant->name;
            }
        }
    }

    public function castVote($participantId)
    {
        $this->voting_is_open = Cache::get('voting_is_open', true);
        if (!$this->voting_is_open) return;
        if ($this->hasVoted) return;

        $token = request()->cookie('voter_token');
        if (!$token) {
            $token = (string) Str::uuid();
            Cookie::queue('voter_token', $token, 60 * 24 * 30);
        }

        if (Vote::where('device_token', $token)->exists()) {
            $this->checkVote();
            return;
        }

        Vote::create([
            'participant_id' => $participantId,
            'device_token' => $token,
            'ip_address' => request()->ip(),
        ]);

        $this->checkVote();
    }
};
?>

<div class="min-h-screen bg-gradient-to-br from-slate-900 via-indigo-950 to-blue-950 p-6 flex flex-col items-center" 
     wire:poll.3s="checkStatus" 
     x-data="voteHandler()">
    
    <!-- Load SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Alpine.js Logic (Cepat & Bebas dari klik 2x) -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('voteHandler', () => ({
                async submitVote(participantId, participantName) {
                    const result = await Swal.fire({
                        title: 'Konfirmasi Vote',
                        text: 'Yakin ingin memberikan vote untuk ' + participantName + '? Pilihan tidak bisa diubah!',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#db2777', 
                        cancelButtonColor: '#475569',  
                        confirmButtonText: 'Ya, Berikan Vote!',
                        cancelButtonText: 'Batal',
                        background: '#1e293b', 
                        color: '#ffffff'
                    });

                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Mengirim...',
                            allowOutsideClick: false,
                            showConfirmButton: false,
                            background: '#1e293b', 
                            color: '#ffffff',
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        await this.$wire.castVote(participantId);
                        
                        Swal.close();
                    }
                }
            }));
        });
    </script>

    <div class="w-full max-w-2xl mt-8 mb-12 text-center">
        <h1 class="text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-pink-300 to-indigo-300 mb-2">VOTING PESERTA FAVORIT</h1>
        <p class="text-indigo-200">Dukung peserta jagoanmu! (1 Perangkat hanya bisa memilih 1 kali)</p>
    </div>

    @if(!$voting_is_open)
        <div class="w-full max-w-2xl bg-red-900/40 backdrop-blur-md border border-red-500/50 rounded-3xl p-10 text-center shadow-2xl animate-fade-in-up">
            <div class="text-6xl mb-4">🛑</div>
            <h2 class="text-3xl font-black text-white mb-2">Voting Telah Ditutup!</h2>
            <p class="text-xl text-red-200">Terima kasih atas antusiasmenya. Sesi voting penonton sudah berakhir dan hasil sedang dihitung.</p>
        </div>
        
    @elseif($hasVoted)
        <div class="w-full max-w-2xl bg-white/10 backdrop-blur-md border border-green-400/50 rounded-3xl p-10 text-center shadow-2xl animate-fade-in-up">
            <div class="text-6xl mb-4">🎉</div>
            <h2 class="text-3xl font-black text-white mb-2">Terima Kasih!</h2>
            <p class="text-xl text-green-200">Voting Anda untuk <span class="font-bold text-white bg-green-600 px-3 py-1 rounded-lg mx-1">{{ $votedFor }}</span> telah masuk ke dalam sistem.</p>
        </div>
        
    @else
        <div class="w-full max-w-2xl grid grid-cols-1 gap-4">
            @foreach($participants as $p)
                <div class="bg-white/10 hover:bg-white/20 backdrop-blur-sm border border-white/10 p-4 rounded-2xl flex justify-between items-center transition">
                    <div>
                        <h3 class="text-xl font-bold text-white">{{ $p->order_number }}. {{ $p->name }}</h3>
                        <p class="text-indigo-300 text-sm">🎵 {{ $p->song_title ?: 'Lagu Pilihan' }}</p>
                    </div>
                    
                    <button type="button" 
                        x-on:click="submitVote({{ $p->id }}, '{{ addslashes($p->name) }}')" 
                        class="bg-pink-600 hover:bg-pink-500 text-white font-bold py-3 px-6 rounded-xl shadow-lg transform transition hover:scale-105 active:scale-95">
                        VOTE
                    </button>
                </div>
            @endforeach
        </div>
    @endif
</div>