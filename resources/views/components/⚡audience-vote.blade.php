<?php

use Livewire\Component;
use App\Models\Participant;
use App\Models\Vote;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\RateLimiter;
use App\Services\VotingSessionService;

new class extends Component
{
    public $participants;
    public $hasVoted = false;
    public $votedFor = null;
    public $voting_is_open = true;

    public function mount()
    {
        $this->voting_is_open = app(VotingSessionService::class)->status()['is_open'];
        $this->participants = Participant::orderBy('order_number')->get();
        $this->checkVote();
    }

    public function checkStatus()
    {
        $this->voting_is_open = app(VotingSessionService::class)->status()['is_open'];
        $this->participants = Participant::orderBy('order_number')->get();
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

    public function castVote($participantId, $deviceToken = null)
    {
        $this->voting_is_open = app(VotingSessionService::class)->status()['is_open'];
        if (!$this->voting_is_open) return;
        if ($this->hasVoted) return;

        $token = $deviceToken ?: request()->cookie('voter_token');
        if (!$token) {
            $token = (string) Str::uuid();
        }

        abort_unless(Str::isUuid($token), 422, 'Identitas perangkat tidak valid.');
        abort_unless(Participant::whereKey($participantId)->exists(), 404);

        $rateLimitKey = 'audience-vote:' . request()->ip();
        abort_if(
            RateLimiter::tooManyAttempts($rateLimitKey, 60),
            429,
            'Terlalu banyak percobaan voting. Silakan tunggu sebentar.'
        );
        RateLimiter::hit($rateLimitKey, 60);

        Cookie::queue(cookie(
            'voter_token',
            $token,
            60 * 24 * 180,
            '/',
            null,
            request()->isSecure(),
            true,
            false,
            'Lax'
        ));

        $existingVote = Vote::with('participant')->where('device_token', $token)->first();
        if ($existingVote) {
            $this->hasVoted = true;
            $this->votedFor = $existingVote->participant->name;
            return;
        }

        try {
            $vote = Vote::create([
                'participant_id' => $participantId,
                'device_token' => $token,
                'ip_address' => request()->ip(),
            ]);
        } catch (UniqueConstraintViolationException) {
            $vote = Vote::where('device_token', $token)->first();
        }

        if ($vote) {
            $this->hasVoted = true;
            $this->votedFor = $vote->participant->name;
        }
    }
};
?>

<div class="min-h-screen bg-gradient-to-br from-slate-900 via-indigo-950 to-blue-950 px-4 py-6 sm:p-6 flex flex-col items-center"
     wire:poll.3s="checkStatus" 
     x-data="voteHandler()">
    
    <!-- Load SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Alpine.js Logic (Cepat & Bebas dari klik 2x) -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('voteHandler', () => ({
                deviceToken() {
                    let token = localStorage.getItem('korpri_voter_token');
                    if (!token) {
                        token = globalThis.crypto?.randomUUID?.()
                            ?? 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, character => {
                                const random = Math.random() * 16 | 0;
                                return (character === 'x' ? random : (random & 0x3 | 0x8)).toString(16);
                            });
                        localStorage.setItem('korpri_voter_token', token);
                    }
                    return token;
                },
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

                        await this.$wire.castVote(participantId, this.deviceToken());
                        
                        Swal.close();
                    }
                }
            }));
        });
    </script>

    <div class="w-full max-w-2xl mt-4 mb-8 text-center sm:mt-8 sm:mb-12">
        <h1 class="text-3xl leading-tight font-black text-transparent bg-clip-text bg-gradient-to-r from-pink-300 to-indigo-300 mb-3 sm:text-4xl">VOTING PESERTA FAVORIT</h1>
        <p class="text-sm leading-relaxed text-indigo-200 sm:text-base">KORPRI Musical Challenge 2026 <span class="hidden sm:inline">•</span><span class="block sm:inline"> Dukung peserta jagoanmu!</span> <span class="block sm:inline">(1 perangkat hanya bisa memilih 1 kali)</span></p>
    </div>

    @if(!$voting_is_open)
        <div class="w-full max-w-2xl bg-red-900/40 backdrop-blur-md border border-red-500/50 rounded-3xl p-10 text-center shadow-2xl animate-fade-in-up">
            <div class="text-6xl mb-4">🛑</div>
            <h2 class="text-3xl font-black text-white mb-2">Voting Telah Ditutup!</h2>
            <p class="text-xl text-red-200">Terima kasih atas antusiasmenya. Sesi voting penonton sudah berakhir dan hasil sedang dihitung.</p>
        </div>
        
    @elseif($hasVoted)
        <div class="w-full max-w-2xl bg-white/10 backdrop-blur-md border border-green-400/50 rounded-3xl px-5 py-8 text-center shadow-2xl animate-fade-in-up sm:p-10">
            <div class="text-5xl mb-4 sm:text-6xl">🎉</div>
            <h2 class="text-3xl font-black text-white mb-5 sm:text-4xl">Terima Kasih!</h2>

            <div class="mx-auto flex max-w-xl flex-col items-center gap-3">
                <p class="text-lg leading-relaxed text-green-200 sm:text-xl">Voting Anda telah diberikan kepada:</p>
                <div class="w-full max-w-lg rounded-2xl border border-green-300/30 bg-green-600 px-4 py-3 shadow-lg shadow-green-950/20 sm:px-6">
                    <p class="break-words text-xl font-black leading-snug text-white sm:text-2xl">
                        {{ $votedFor }}
                    </p>
                </div>
                <p class="text-base leading-relaxed text-green-200 sm:text-xl">Pilihan Anda telah berhasil masuk ke dalam sistem.</p>
            </div>
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
