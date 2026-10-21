<?php

use Livewire\Component;
use App\Models\ChallengeParticipant;

new class extends Component
{
    public function with(): array
    {
        $participants = ChallengeParticipant::orderBy('order_number')->get();
        $total = $participants->count();
        $completed = $participants->where('status', 'completed')->count();
        $pending = $total - $completed;

        return [
            'participants' => $participants,
            'total' => $total,
            'completed' => $completed,
            'pending' => $pending,
        ];
    }
};
?>

<!-- wire:poll.2s membuat halaman ini live update otomatis setiap 2 detik -->
<div class="h-screen max-h-screen overflow-hidden bg-gradient-to-br from-slate-950 via-indigo-950 to-slate-900 text-white flex flex-col justify-between p-3 md:p-5 relative select-none" wire:poll.2s>

    <!-- Efek Dekorasi Latar Glowing -->
    <div class="absolute -top-32 -left-32 w-80 h-80 bg-pink-600/15 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="absolute -bottom-32 -right-32 w-80 h-80 bg-indigo-600/15 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-purple-900/10 rounded-full blur-[140px] pointer-events-none"></div>

    <div class="w-full max-w-7xl mx-auto flex-1 flex flex-col min-h-0 relative z-10">

        <!-- TOP BAR: Header, Stats Ringkas & Tombol Navigasi -->
        <header class="shrink-0 flex flex-col sm:flex-row justify-between items-center gap-3 pb-3 border-b border-slate-800/80">
            <!-- Sisi Kiri: Judul Acara -->
            <div class="text-center sm:text-left">
                <div class="flex items-center justify-center sm:justify-start gap-2">
                    <span class="inline-flex items-center gap-1.5 bg-indigo-500/10 border border-indigo-500/30 text-indigo-300 px-3 py-0.5 rounded-full text-[10px] md:text-xs font-extrabold tracking-widest uppercase shadow-sm">
                        <span>✨</span> KORPRI MUSICAL CHALLENGE 2026
                    </span>
                </div>
                <h1 class="text-2xl md:text-3xl font-black bg-clip-text text-transparent bg-gradient-to-r from-white via-indigo-100 to-pink-200 tracking-tight mt-0.5">
                    Daftar Lagu Peserta
                </h1>
            </div>

            <!-- Sisi Tengah/Kanan: Statistik Ringkas Inline -->
            <div class="flex flex-wrap items-center justify-center gap-2 text-xs">
                <div class="bg-slate-900/90 border border-slate-700/80 px-3 py-1.5 rounded-xl shadow backdrop-blur-md flex items-center gap-2">
                    <span class="text-slate-400">Total:</span>
                    <strong class="text-white font-black text-sm">{{ $total }} Tim</strong>
                </div>

                <div class="bg-slate-900/90 border border-emerald-500/40 px-3 py-1.5 rounded-xl shadow backdrop-blur-md flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="text-emerald-300">Dapat Lagu:</span>
                    <strong class="text-emerald-400 font-black text-sm">{{ $completed }} Tim</strong>
                </div>

                <div class="bg-slate-900/90 border border-amber-500/40 px-3 py-1.5 rounded-xl shadow backdrop-blur-md flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                    <span class="text-amber-300">Menunggu:</span>
                    <strong class="text-amber-300 font-black text-sm">{{ $pending }} Tim</strong>
                </div>
            </div>

            <!-- Sisi Kanan: Tombol Aksi -->
            <div class="flex items-center gap-2 shrink-0">
                <a href="/display/challenge" class="inline-flex items-center gap-1.5 bg-gradient-to-r from-pink-500 to-indigo-600 hover:from-pink-600 hover:to-indigo-700 text-white font-bold px-3 py-1.5 rounded-xl shadow-lg transition transform hover:scale-105 text-xs">
                    <span>←</span> <span>Roda Spin</span>
                </a>

                <button onclick="toggleFullScreen()" class="hover:text-slate-200 font-bold bg-slate-900/90 border border-slate-700 px-3 py-1.5 rounded-xl transition flex items-center gap-1.5 text-xs text-slate-300 shadow">
                    <span>⛶</span> Fullscreen (F11)
                </button>
            </div>
        </header>

        <!-- TABEL REKAP PANGGUNG (PAS 1 HALAMAN LAYAR PENUH) -->
        <main class="flex-1 min-h-0 flex flex-col bg-slate-900/80 rounded-xl border border-slate-800 shadow-2xl overflow-hidden backdrop-blur-md my-2.5">
            @if($participants->isEmpty())
                <div class="flex-1 flex flex-col items-center justify-center p-8 text-center text-slate-400">
                    <div class="text-4xl mb-2">🎤</div>
                    <h3 class="text-lg font-bold text-slate-300">Belum Ada Peserta Terdaftar</h3>
                </div>
            @else
                <div class="flex-1 overflow-y-auto">
                    <table class="w-full text-left border-collapse">
                        <thead class="sticky top-0 z-10 bg-slate-950/95 shadow-md">
                            <tr class="text-slate-400 uppercase text-[11px] font-bold tracking-wider border-b border-slate-800">
                                <th class="py-2.5 px-3 text-center w-14">No</th>
                                <th class="py-2.5 px-4">Nama Peserta / Instansi</th>
                                <th class="py-2.5 px-3 text-center w-28">Status</th>
                                <th class="py-2.5 px-4 w-36">Genre</th>
                                <th class="py-2.5 px-4">Lagu Terpilih</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60">
                            @foreach($participants as $idx => $p)
                                @php
                                    $isDone = ($p->status === 'completed');
                                    // Warna Badge Genre
                                    $genreBadge = 'bg-slate-800 text-slate-400 border-slate-700';
                                    if ($p->genre === 'POP') $genreBadge = 'bg-pink-500/20 text-pink-300 border-pink-500/50';
                                    elseif ($p->genre === 'ROCK') $genreBadge = 'bg-rose-500/20 text-rose-300 border-rose-500/50';
                                    elseif ($p->genre === 'DANGDUT') $genreBadge = 'bg-amber-500/20 text-amber-300 border-amber-500/50';
                                    elseif ($p->genre === 'NUSANTARA MIX') $genreBadge = 'bg-emerald-500/20 text-emerald-300 border-emerald-500/50';
                                    elseif ($p->genre === 'SKA/REGGAE') $genreBadge = 'bg-purple-500/20 text-purple-300 border-purple-500/50';
                                @endphp
                                <tr class="hover:bg-slate-800/40 transition {{ $isDone ? 'bg-slate-900/30' : '' }}">
                                    <!-- No -->
                                    <td class="py-2 md:py-2.5 px-3 text-center font-bold text-slate-500 text-xs">
                                        #{{ $p->order_number ?: ($idx + 1) }}
                                    </td>

                                    <!-- Nama Peserta -->
                                    <td class="py-2 md:py-2.5 px-4 font-black text-white text-sm md:text-base leading-tight">
                                        {{ $p->name }}
                                    </td>

                                    <!-- Status -->
                                    <td class="py-2 md:py-2.5 px-3 text-center whitespace-nowrap">
                                        @if($isDone)
                                            <span class="inline-flex items-center gap-1 bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 px-2.5 py-0.5 rounded-full text-[11px] font-bold">
                                                <span>✓</span> Terkunci
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 bg-slate-800 text-slate-400 border border-slate-700 px-2.5 py-0.5 rounded-full text-[11px] font-semibold">
                                                <span>⏳</span> Menunggu
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Genre -->
                                    <td class="py-2 md:py-2.5 px-4 whitespace-nowrap">
                                        @if($p->genre)
                                            <span class="inline-block px-2.5 py-0.5 rounded-lg text-xs font-black uppercase tracking-wider border {{ $genreBadge }}">
                                                {{ $p->genre }}
                                            </span>
                                        @else
                                            <span class="text-slate-600 font-bold">—</span>
                                        @endif
                                    </td>

                                    <!-- Lagu Terpilih -->
                                    <td class="py-2 md:py-2.5 px-4">
                                        @if($p->song_title)
                                            <div class="font-extrabold text-pink-300 text-sm md:text-base flex items-center gap-2 leading-tight">
                                                <span class="text-xs">🎵</span>
                                                <span>{{ $p->song_title }}</span>
                                            </div>
                                        @else
                                            <span class="text-slate-600 font-semibold italic text-xs">Belum diundi</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </main>

        <!-- FOOTER RINGKAS -->
        <footer class="shrink-0 text-center text-[11px] text-slate-500 py-1">
            &copy; 2026 KORPRI Musical Challenge • Lomba Karaoke Setwapres
        </footer>

    </div>

    <script>
        function toggleFullScreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(() => {});
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                }
            }
        }
    </script>
</div>
