<?php

use Livewire\Component;
use App\Models\Participant;

new class extends Component
{
    // Variabel untuk menyimpan riwayat total nilai
    public $lastTotalScore = -1; 

    // Fungsi ini dipanggil setiap detik oleh wire:poll
    public function checkUpdates()
    {
        $currentTotal = 0;
        $participants = Participant::with('scores')->get();
        
        // Jumlahkan semua nilai dari semua juri untuk semua peserta
        foreach ($participants as $p) {
            $currentTotal += $p->scores->sum('total_score');
        }

        // Jika ini bukan load pertama (bukan -1) DAN total nilai bertambah (ada nilai masuk baru)
        if ($this->lastTotalScore !== -1 && $currentTotal > $this->lastTotalScore) {
            // Perintahkan browser untuk meledakkan confetti
            $this->dispatch('trigger-confetti'); 
        }

        // Perbarui riwayat total nilai
        $this->lastTotalScore = $currentTotal;
    }

    public function with(): array
    {
        $participants = Participant::with('scores')
            ->orderBy('order_number')
            ->get()
            ->map(function ($participant) {
                $participant->final_score = $participant->scores->count() > 0 
                    ? $participant->scores->avg('total_score') 
                    : 0;
                return $participant;
            })
            ->sortByDesc('final_score')
            ->values();

        return [
            'leaderboard' => $participants
        ];
    }
};
?>

<!-- wire:poll.1s sekarang memanggil fungsi checkUpdates() setiap detiknya -->
<div class="min-h-screen bg-slate-900 py-12 px-4 sm:px-6 lg:px-8 flex flex-col items-center relative" wire:poll.1s="checkUpdates">
    
    <!-- Load Library Confetti -->
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
    <script>
        // Mendengarkan perintah dari Livewire
        window.addEventListener('trigger-confetti', () => {
            // Ledakan confetti dari bagian atas layar (y: 0.1)
            confetti({
                particleCount: 150,
                spread: 120,
                origin: { y: 0.1 }, 
                zIndex: 99999,
                colors: ['#facc15', '#f97316', '#ef4444', '#3b82f6', '#ec4899']
            });
        });
    </script>

    <!-- Judul Header -->
    <div class="text-center mb-12 w-full">
        <h1 class="text-5xl md:text-6xl font-black text-transparent bg-clip-text bg-gradient-to-r from-yellow-400 via-orange-500 to-red-500 drop-shadow-lg uppercase tracking-widest mb-4 animate-pulse">
            LIVE LEADERBOARD
        </h1>
        <p class="text-xl text-slate-400 font-semibold tracking-wide">Klasemen Sementara Peserta Defile Setwapres</p>
    </div>

    <!-- Daftar Klasemen Semua Peserta -->
    <div class="w-full max-w-5xl space-y-4">
        @foreach($leaderboard as $index => $p)
            @php
                $rankColor = 'bg-slate-700 text-slate-300'; 
                $borderGlow = 'border-slate-700 hover:border-slate-500';
                $barColor = 'bg-blue-500';

                if ($p->final_score > 0) {
                    if ($index === 0) {
                        $rankColor = 'bg-gradient-to-br from-yellow-300 to-yellow-600 text-slate-900 shadow-[0_0_20px_rgba(253,224,71,0.5)]'; 
                        $borderGlow = 'border-yellow-500/50 shadow-[0_0_15px_rgba(253,224,71,0.2)] z-10 scale-[1.01]';
                        $barColor = 'bg-gradient-to-r from-yellow-400 to-orange-500';
                    } elseif ($index === 1) {
                        $rankColor = 'bg-gradient-to-br from-slate-300 to-slate-500 text-slate-900 shadow-[0_0_15px_rgba(148,163,184,0.4)]'; 
                        $borderGlow = 'border-slate-400/50 z-10';
                        $barColor = 'bg-gradient-to-r from-slate-300 to-slate-400';
                    } elseif ($index === 2) {
                        $rankColor = 'bg-gradient-to-br from-amber-600 to-orange-800 text-white shadow-[0_0_15px_rgba(217,119,6,0.4)]'; 
                        $borderGlow = 'border-amber-700/50 z-10';
                        $barColor = 'bg-gradient-to-r from-amber-500 to-orange-600';
                    }
                }
            @endphp

            <!-- Kartu Peserta -->
            <div class="bg-slate-800 p-5 rounded-2xl border {{ $borderGlow }} transition-all duration-700 ease-in-out transform flex items-center gap-6 relative">
                
                <!-- Peringkat -->
                <div class="{{ $rankColor }} font-black h-16 w-16 shrink-0 flex items-center justify-center rounded-2xl text-3xl transition-colors duration-500">
                    {{ $index + 1 }}
                </div>

                <!-- Detail & Progress Bar -->
                <div class="flex-1">
                    <div class="flex justify-between items-end mb-2">
                        <div>
                            <div class="flex items-center gap-3">
                                <h3 class="text-2xl font-bold text-white uppercase">{{ $p->name }}</h3>
                                <span class="bg-slate-700 text-slate-300 text-xs font-bold px-2 py-1 rounded-md">
                                    Tampil #{{ $p->order_number }}
                                </span>
                            </div>
                            <p class="text-slate-400 text-sm font-semibold">{{ $p->song_title ?? 'Lagu Belum Ditentukan' }}</p>
                        </div>
                        
                        <div class="text-3xl font-black {{ $p->final_score > 0 ? 'text-white' : 'text-slate-600' }} transition-colors">
                            {{ number_format($p->final_score, 2) }} <span class="text-sm {{ $p->final_score > 0 ? 'text-slate-400' : 'text-slate-700' }}"> / 100</span>
                        </div>
                    </div>

                    <div class="w-full bg-slate-900 rounded-full h-4 border border-slate-700 overflow-hidden relative">
                        <div class="{{ $barColor }} h-full rounded-full transition-all duration-1000 ease-out relative" 
                             style="width: {{ $p->final_score }}%;">
                             
                             @if($p->final_score > 0)
                                 <div class="absolute top-0 left-0 bottom-0 right-0 bg-white/20 w-full animate-pulse"></div>
                             @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>