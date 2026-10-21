<?php

use Livewire\Component;
use Illuminate\Support\Facades\Cache;

new class extends Component
{
    public $lastTriggerTime = 0;
    public $lastStateUpdate = 0;

    public $sessionActive = false;
    public $participantName = '';
    public $round = 1;
    public $step = 'idle'; // 'idle', 'spin_genre', 'genre_chosen', 'spin_song', 'decision', 'completed'
    public $genre = null;
    public $song = null;
    public $round1Genre = null;
    public $round1Song = null;
    public $eventTitle = 'KORPRI MUSICAL CHALLENGE 2026';
    public $items = [];

    // Status animasi putaran roda (Anti-Spoiler)
    public $isSpinning = false;
    public $spinEndTime = 0;
    public $spinType = null;

    public function mount()
    {
        $this->loadState();
    }

    public function loadState()
    {
        $state = Cache::get('challenge_state');
        if ($state) {
            $this->sessionActive = $state['sessionActive'] ?? false;
            $this->participantName = $state['participant'] ?? '';
            $this->round = $state['round'] ?? 1;
            $this->step = $state['step'] ?? 'idle';
            $this->genre = $state['genre'] ?? null;
            $this->song = $state['song'] ?? null;
            $this->round1Genre = $state['round1_genre'] ?? null;
            $this->round1Song = $state['round1_song'] ?? null;
            $this->eventTitle = $state['title'] ?? Cache::get('challenge_event_title', 'KORPRI MUSICAL CHALLENGE 2026');
            $this->items = $state['items'] ?? [];
            $this->lastStateUpdate = $state['updated_at'] ?? microtime(true);
        } else {
            // Standby awal: 6 genre
            $this->eventTitle = Cache::get('challenge_event_title', 'KORPRI MUSICAL CHALLENGE 2026');
            $genres = ['POP 1', 'POP 2', 'ROCK', 'DANGDUT', 'NUSANTARA MIX', 'SKA/REGGAE'];
            $colors = ['#ec4899', '#f43f5e', '#ef4444', '#f59e0b', '#10b981', '#8b5cf6'];
            $this->items = [];
            foreach ($genres as $i => $g) {
                $this->items[] = [
                    'id' => $g,
                    'name' => $g,
                    'color' => $colors[$i % count($colors)]
                ];
            }
        }
    }

    public function checkState()
    {
        // 1. Cek trigger putaran roda dari Admin
        $trigger = Cache::get('challenge_trigger');
        if ($trigger && $trigger['time'] > $this->lastTriggerTime) {
            $this->lastTriggerTime = $trigger['time'];
            $duration = (float)($trigger['duration'] ?? 6);
            $this->isSpinning = true;
            $this->spinEndTime = microtime(true) + $duration;
            $this->spinType = $trigger['step']; // 'genre' or 'song'

            $this->dispatch('start-challenge-spin', [
                'step' => $trigger['step'],
                'round' => $trigger['round'] ?? 1,
                'winnerId' => $trigger['winner_id'],
                'winnerName' => $trigger['winner_name'],
                'duration' => $duration,
                'participant' => $trigger['participant'] ?? $this->participantName,
                'genre' => $trigger['genre'] ?? $this->genre
            ]);
        }

        // Cek apakah animasi putaran roda sudah selesai
        if ($this->isSpinning && microtime(true) >= $this->spinEndTime) {
            $this->isSpinning = false;
            $this->spinType = null;
        }

        // 2. Cek perubahan data state (pergantian peserta / perpindahan fase genre ke lagu)
        $state = Cache::get('challenge_state');
        if ($state && ($state['updated_at'] ?? 0) > $this->lastStateUpdate) {
            $this->loadState();
            
            // Dispatch event jika item pada roda berganti
            $this->dispatch('items-updated', [
                'items' => $this->items,
                'step' => $this->step,
                'round' => $this->round
            ]);
        }
    }

    public function spinFinished()
    {
        $this->isSpinning = false;
        $this->spinType = null;
        $this->spinEndTime = 0;
    }
};
?>

<div class="h-screen max-h-screen overflow-hidden bg-slate-950 text-white flex flex-col justify-between items-center relative py-2.5 px-4 select-none"
     wire:poll.1s="checkState"
     x-data="challengeWheelApp(@js($items))">

    <!-- Efek Dekorasi Latar Glowing Panggung -->
    <div class="absolute -top-32 -left-32 w-80 h-80 bg-pink-600/15 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="absolute -bottom-32 -right-32 w-80 h-80 bg-indigo-600/15 rounded-full blur-[120px] pointer-events-none"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[550px] h-[550px] bg-purple-900/10 rounded-full blur-[140px] pointer-events-none"></div>

    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- TOP BAR: Header & Status Panggung (Dibuat Pas Untuk Layar Monitor PC) -->
    <header class="w-full max-w-5xl z-20 text-center shrink-0">
        <!-- Judul Acara Pill -->
        <div class="inline-flex items-center gap-2 bg-indigo-500/10 border border-indigo-500/30 text-indigo-300 px-4 py-1 rounded-full text-xs font-extrabold tracking-widest uppercase mb-2 shadow-md">
            <span>✨</span>
            <span>{{ $eventTitle }}</span>
            <span>✨</span>
        </div>

        <!-- Banner Info Peserta & Fase Aktif -->
        @if($sessionActive && $participantName)
            <div class="bg-slate-900/90 backdrop-blur-md border border-slate-700/80 rounded-xl p-3 shadow-xl max-w-3xl mx-auto flex flex-col sm:flex-row justify-between items-center gap-3">
                <div class="text-center sm:text-left">
                    <div class="flex items-center justify-center sm:justify-start gap-2">
                        <span class="text-[10px] uppercase font-extrabold text-slate-400 tracking-wider">Peserta</span>
                        <span class="text-[10px] font-black uppercase px-2 py-0.5 rounded-full {{ $round === 1 ? 'bg-amber-500/20 text-amber-300 border border-amber-500/30' : 'bg-rose-500/20 text-rose-300 border border-rose-500/30' }}">
                            {{ $round === 1 ? 'Kesempatan 1' : 'Kesempatan 2 (Terakhir)' }}
                        </span>
                    </div>
                    <h2 class="text-xl md:text-2xl font-black bg-clip-text text-transparent bg-gradient-to-r from-white via-indigo-100 to-pink-200 truncate max-w-md">
                        {{ $participantName }}
                    </h2>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <!-- Indikator Anti-Spoiler Saat Roda Sedang Berputar -->
                    <template x-if="isSpinning">
                        <span class="bg-gradient-to-r from-amber-500/30 via-rose-500/30 to-indigo-500/30 border border-amber-400 text-amber-200 font-extrabold px-3.5 py-1.5 rounded-lg text-xs tracking-wide flex items-center gap-2 shadow-lg animate-pulse">
                            <span class="animate-spin text-sm inline-block">🎲</span>
                            <span x-text="spinningType === 'song' ? 'MEMUTAR RODA LAGU...' : 'MEMUTAR RODA GENRE...'"></span>
                        </span>
                    </template>

                    <!-- Status Reguler (Hanya Tampil Saat Roda Sedang Diam / Tidak Berputar) -->
                    <div x-show="!isSpinning" :class="isSpinning ? 'hidden' : 'flex'" class="items-center gap-2">
                        @if($isSpinning)
                            <span class="bg-gradient-to-r from-amber-500/30 via-rose-500/30 to-indigo-500/30 border border-amber-400 text-amber-200 font-extrabold px-3.5 py-1.5 rounded-lg text-xs tracking-wide flex items-center gap-2 shadow-lg animate-pulse">
                                <span class="animate-spin text-sm inline-block">🎲</span>
                                <span>{{ $spinType === 'song' ? 'MEMUTAR RODA LAGU...' : 'MEMUTAR RODA GENRE...' }}</span>
                            </span>
                        @else
                            @if($step === 'spin_genre')
                                <span class="bg-amber-500/20 border border-amber-400 text-amber-300 font-extrabold px-3 py-1.5 rounded-lg text-xs tracking-wide flex items-center gap-1.5 animate-pulse">
                                    <span>🎲</span> TAHAP 1: SPIN GENRE
                                </span>
                            @elseif($step === 'genre_chosen')
                                <span class="bg-amber-500/30 border border-amber-300 text-amber-200 font-extrabold px-3 py-1.5 rounded-lg text-xs tracking-wide flex items-center gap-1.5 shadow-sm">
                                    <span>✓</span> GENRE: {{ $genre }}
                                </span>
                            @elseif($step === 'spin_song')
                                <span class="bg-pink-500/20 border border-pink-400 text-pink-300 font-extrabold px-3 py-1.5 rounded-lg text-xs tracking-wide flex items-center gap-1.5 animate-pulse">
                                    <span>🎵</span> TAHAP 2: SPIN LAGU ({{ $genre }})
                                </span>
                            @elseif($step === 'decision')
                                <span class="bg-amber-500/30 border border-amber-300 text-amber-200 font-extrabold px-3 py-1.5 rounded-lg text-xs tracking-wide flex items-center gap-1.5 shadow-sm">
                                    <span>🤔</span> KEPUTUSAN TIM
                                </span>
                            @elseif($step === 'completed')
                                <span class="bg-emerald-500/20 border border-emerald-400 text-emerald-300 font-extrabold px-3 py-1.5 rounded-lg text-xs tracking-wide flex items-center gap-1.5 shadow-sm">
                                    <span>🎉</span> TANTANGAN FINAL TERKUNCI
                                </span>
                            @endif
                        @endif
                    </div>
                </div>
            </div>

            <!-- Tampilan Ringkas Hasil Final (Hanya muncul jika sudah final/selesai DAN roda tidak sedang berputar) -->
            @if($step === 'completed' && !$isSpinning)
                <div x-show="!isSpinning" :class="isSpinning ? 'hidden' : ''" class="mt-2 bg-gradient-to-r from-emerald-950/80 via-slate-900/90 to-emerald-950/80 border border-emerald-400/70 rounded-xl px-4 py-2 max-w-xl mx-auto shadow-lg backdrop-blur-md">
                    <div class="text-sm md:text-base font-black text-white">
                        FINAL: <span class="text-amber-300">{{ $genre }}</span> — <span class="text-pink-300">{{ $song }}</span>
                    </div>
                    <div class="text-[11px] text-emerald-200/80">
                        Lagu resmi terkunci & dihilangkan dari daftar untuk peserta selanjutnya.
                    </div>
                </div>
            @endif

        @else
            <div class="bg-slate-900/60 border border-slate-800 rounded-xl py-2 px-5 max-w-md mx-auto text-slate-400 text-xs">
                <span>🎤</span> Menunggu Admin memulai sesi peserta di panggung...
            </div>
        @endif
    </header>

    <!-- AREA RODA (CANVAS) - DISESUAIKAN DENGAN UKURAN MONITOR PC -->
    <main wire:ignore class="flex items-center justify-center relative w-full flex-1 my-1">
        
        <!-- Ukuran Roda Disesuaikan: 48vh / Max 430px agar pas 1 layar monitor PC tanpa scroll -->
        <div class="relative flex items-center justify-center" style="width: 48vh; height: 48vh; max-width: 430px; max-height: 430px;">
            
            <!-- PENUNJUK JARUM (DI SISI KANAN RODA, UJUNG TAJAM MENUSUK MASUK KE DALAM RODA) -->
            <div class="absolute -right-4 md:-right-5 top-1/2 -translate-y-1/2 z-30 drop-shadow-[0_0_25px_rgba(251,191,36,0.9)] pointer-events-none">
                <svg width="54" height="54" viewBox="0 0 24 24" class="drop-shadow-2xl">
                    <path d="M21 3L3 12L21 21L17 12L21 3Z" fill="#fbbf24" stroke="#ffffff" stroke-width="2" stroke-linejoin="round"/>
                </svg>
            </div>

            <!-- FRAME LUAR RODA (METALLIC RING) -->
            <div class="relative rounded-full border-[12px] md:border-[16px] border-slate-200 shadow-[0_0_60px_rgba(99,102,241,0.25)] overflow-hidden bg-slate-900 flex items-center justify-center w-full h-full">
                
                <canvas id="wheelCanvas" width="1200" height="1200" class="w-full h-full object-contain" x-ref="canvas" style="transition-timing-function: cubic-bezier(0.2, 0.8, 0.2, 1);"></canvas>
                
                <!-- PIN TENGAH DEKORATIF DENGAN LOGO MUSIK -->
                <div class="absolute w-14 h-14 md:w-16 md:h-16 bg-gradient-to-tr from-amber-400 via-amber-200 to-amber-500 rounded-full shadow-[0_0_20px_rgba(251,191,36,0.6)] z-20 border-2 border-white flex items-center justify-center">
                    <span class="text-xl md:text-2xl">🎤</span>
                </div>
            </div>

        </div>
    </main>

    <!-- FOOTER: Navigasi, Status Audio & Fullscreen -->
    <footer class="w-full max-w-4xl z-20 text-center px-4 flex justify-between items-center text-[11px] text-slate-500 shrink-0 py-1">
        <div>
            &copy; 2026 KORPRI Musical Challenge • Lomba Karaoke Setwapres
        </div>
        <div class="flex items-center gap-3">
            <!-- Indikator / Tombol Tes Suara -->
            <button onclick="soundManager.testAudio()" class="font-bold bg-amber-950/80 border border-amber-500/50 px-2.5 py-1 rounded-lg transition flex items-center gap-1 text-[11px] text-amber-300 animate-pulse cursor-pointer" title="Klik sekali untuk memastikan audio aktif di browser">
                <span>🔊</span> <span id="audioStatusText">Klik Layar untuk Suara</span>
            </button>

            <!-- Tombol Forward ke Daftar Hasil Lagu Peserta -->
            <a href="/display/challenge-results" class="hover:text-white font-bold bg-gradient-to-r from-pink-600/80 to-indigo-600/80 hover:from-pink-600 hover:to-indigo-600 border border-indigo-400/50 text-indigo-100 px-3 py-1 rounded-lg transition flex items-center gap-1.5 text-[11px] shadow-sm">
                <span>📋</span> <span>Daftar Lagu Peserta</span> <span>→</span>
            </a>

            <!-- Tombol Fullscreen -->
            <button onclick="toggleFullScreen()" class="hover:text-slate-300 font-bold bg-slate-900/80 border border-slate-800 px-2.5 py-1 rounded-lg transition flex items-center gap-1 text-[11px]">
                <span>⛶</span> Fullscreen (F11)
            </button>
        </div>
    </footer>

    <!-- AUDIO FILES LOKAL PRELOAD -->
    <audio id="spinSound" preload="auto" loop src="/mp3/drumroll.mp3"></audio>
    <audio id="winSound" preload="auto" src="/mp3/hooray.mp3"></audio>

    <script>
        // Fullscreen toggle helper
        function toggleFullScreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(() => {});
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                }
            }
        }

        // Dedicated Sound Manager untuk menangani autoplay restrictions browser
        const soundManager = {
            spinAudio: new Audio('/mp3/drumroll.mp3'),
            winAudio: new Audio('/mp3/hooray.mp3'),
            isUnlocked: false,

            init() {
                this.spinAudio.loop = true;
                this.spinAudio.preload = 'auto';
                this.winAudio.preload = 'auto';

                const unlock = () => {
                    if (this.isUnlocked) return;
                    this.isUnlocked = true;
                    
                    // Silent playback warm-up untuk membuka izin audio browser
                    this.spinAudio.volume = 0;
                    this.winAudio.volume = 0;
                    const p1 = this.spinAudio.play();
                    const p2 = this.winAudio.play();

                    Promise.allSettled([p1, p2]).then(() => {
                        this.spinAudio.pause();
                        this.spinAudio.currentTime = 0;
                        this.spinAudio.volume = 1.0;

                        this.winAudio.pause();
                        this.winAudio.currentTime = 0;
                        this.winAudio.volume = 1.0;

                        this.updateIndicator(true);
                    });
                };

                window.addEventListener('click', unlock, { passive: true });
                window.addEventListener('keydown', unlock, { passive: true });
                window.addEventListener('pointerdown', unlock, { passive: true });
            },

            playSpin() {
                try {
                    this.winAudio.pause();
                    this.winAudio.currentTime = 0;
                    this.spinAudio.currentTime = 0;
                    this.spinAudio.volume = 1.0;
                    const p = this.spinAudio.play();
                    if (p !== undefined) {
                        p.catch(e => {
                            // Fallback jika elemen audio lokal tersedia
                            const s = document.getElementById('spinSound');
                            if (s) { s.currentTime = 0; s.play().catch(() => {}); }
                        });
                    }
                } catch(e) {
                    console.error('Play spin error:', e);
                }
            },

            stopSpin() {
                try {
                    this.spinAudio.pause();
                    this.spinAudio.currentTime = 0;
                    const s = document.getElementById('spinSound');
                    if (s) { s.pause(); s.currentTime = 0; }
                } catch(e) {}
            },

            playWin() {
                try {
                    this.stopSpin();
                    this.winAudio.currentTime = 0;
                    this.winAudio.volume = 1.0;
                    const p = this.winAudio.play();
                    if (p !== undefined) {
                        p.catch(e => {
                            const w = document.getElementById('winSound');
                            if (w) { w.currentTime = 0; w.play().catch(() => {}); }
                        });
                    }
                } catch(e) {
                    console.error('Play win error:', e);
                }
            },

            testAudio() {
                this.isUnlocked = true;
                this.spinAudio.volume = 1.0;
                this.winAudio.volume = 1.0;
                this.winAudio.currentTime = 0;
                this.winAudio.play().then(() => {
                    this.updateIndicator(true);
                    setTimeout(() => {
                        this.winAudio.pause();
                        this.winAudio.currentTime = 0;
                    }, 1500);
                }).catch(e => console.warn(e));
            },

            updateIndicator(active) {
                const el = document.getElementById('audioStatusText');
                if (el) {
                    el.innerText = active ? 'Suara Aktif' : 'Klik Layar untuk Suara';
                    el.parentElement.className = active 
                        ? 'font-bold bg-emerald-950/80 border border-emerald-500/50 px-2.5 py-1 rounded-lg transition flex items-center gap-1 text-[11px] text-emerald-300'
                        : 'font-bold bg-amber-950/80 border border-amber-500/50 px-2.5 py-1 rounded-lg transition flex items-center gap-1 text-[11px] text-amber-300 animate-pulse cursor-pointer';
                }
            }
        };

        soundManager.init();

        document.addEventListener('alpine:init', () => {
            Alpine.data('challengeWheelApp', (initialItems) => ({
                items: initialItems || [],
                isSpinning: false,
                spinningType: null,
                currentRotation: 0,
                
                init() {
                    this.$nextTick(() => {
                        this.drawWheel();
                    });

                    // Event saat trigger putaran diterima dari Admin
                    window.addEventListener('start-challenge-spin', (e) => {
                        const data = e.detail[0] || e.detail; 
                        this.isSpinning = true;
                        this.spinningType = data.step;
                        this.spinWheel(data);
                    });

                    // Event saat item di roda berganti
                    window.addEventListener('items-updated', (e) => {
                        if (this.isSpinning) return;

                        const data = e.detail[0] || e.detail;
                        this.items = data.items || [];
                        this.currentRotation = 0;
                        const canvas = this.$refs.canvas;
                        if (canvas) {
                            canvas.style.transitionDuration = '0s';
                            canvas.style.transform = `rotate(0deg)`;
                        }
                        this.$nextTick(() => {
                            this.drawWheel();
                        });
                    });
                },

                drawWheel() {
                    const canvas = this.$refs.canvas;
                    if (!canvas || !this.items || this.items.length === 0) return;
                    
                    const ctx = canvas.getContext('2d');
                    const width = canvas.width;
                    const height = canvas.height;
                    const radius = width / 2;
                    
                    ctx.clearRect(0, 0, width, height);
                    ctx.save();
                    ctx.translate(radius, radius);
                    
                    const count = this.items.length;
                    const sliceAngle = (2 * Math.PI) / count;
                    const colors = [
                        '#e11d48', '#2563eb', '#059669', '#d97706', '#7c3aed', 
                        '#db2777', '#0891b2', '#4f46e5', '#0d9488', '#ea580c',
                        '#9333ea', '#16a34a', '#0284c7', '#c026d3', '#65a30d'
                    ];

                    this.items.forEach((item, index) => {
                        const startAngle = index * sliceAngle;
                        const endAngle = (index + 1) * sliceAngle;

                        // Gambar Irisan
                        ctx.beginPath();
                        ctx.fillStyle = item.color || colors[index % colors.length];
                        ctx.moveTo(0, 0);
                        ctx.arc(0, 0, radius, startAngle, endAngle);
                        ctx.closePath();
                        ctx.fill();

                        // Garis pemisah irisan
                        ctx.beginPath();
                        ctx.moveTo(0, 0);
                        ctx.arc(0, 0, radius, startAngle, endAngle);
                        ctx.lineWidth = 4;
                        ctx.strokeStyle = '#ffffff';
                        ctx.stroke();

                        // Teks Label
                        ctx.save();
                        ctx.rotate(startAngle + (sliceAngle / 2));
                        ctx.textAlign = "right";
                        ctx.textBaseline = "middle";
                        ctx.fillStyle = "#ffffff";

                        // Ukuran font adaptif tajam
                        let fontSize = 38;
                        if (count > 7) fontSize = 24;
                        if (count > 14) fontSize = 18;
                        
                        ctx.font = `800 ${fontSize}px 'Plus Jakarta Sans', sans-serif`;
                        
                        // Efek shadow teks agar sangat jelas dibaca
                        ctx.shadowColor = "rgba(0, 0, 0, 0.75)";
                        ctx.shadowBlur = 8;
                        ctx.shadowOffsetX = 2;
                        ctx.shadowOffsetY = 2;

                        let displayName = item.name;
                        if (displayName.length > 28) {
                            displayName = displayName.substring(0, 26) + '...';
                        }

                        ctx.fillText(displayName, radius - 45, 0);
                        ctx.restore();
                    });

                    ctx.restore();
                },

                spinWheel(data) {
                    if (!this.items || this.items.length === 0) return;
                    this.isSpinning = true;
                    this.spinningType = data.step;

                    // Cari index pemenang berdasarkan id atau name
                    let winnerIndex = this.items.findIndex(i => String(i.id) === String(data.winnerId));
                    if (winnerIndex === -1) {
                        winnerIndex = this.items.findIndex(i => i.name.toLowerCase() === String(data.winnerName).toLowerCase());
                    }
                    if (winnerIndex === -1) winnerIndex = 0;

                    const winnerName = this.items[winnerIndex].name;
                    const duration = data.duration || 5;

                    // MAINKAN SUARA PUTARAN (DRUMROLL)
                    soundManager.playSpin();

                    // Hitung derajat rotasi agar tepat ke jarum di kanan (0 deg)
                    const sliceDeg = 360 / this.items.length;
                    const winnerCenterDeg = (winnerIndex * sliceDeg) + (sliceDeg / 2);
                    
                    const extraSpins = 9 * 360; // 9 putaran penuh
                    const targetRotation = this.currentRotation + extraSpins + (360 - winnerCenterDeg) - (this.currentRotation % 360);
                    
                    this.currentRotation = targetRotation;

                    const canvas = this.$refs.canvas;
                    canvas.style.transitionDuration = `${duration}s`;
                    canvas.style.transform = `rotate(${targetRotation}deg)`;

                    // Callback saat roda berhenti
                    setTimeout(() => {
                        // MAINKAN SUARA MENANG (HOORAY)
                        soundManager.playWin();
                        
                        // Confetti perayaan
                        confetti({
                            particleCount: 220,
                            spread: 130,
                            origin: { y: 0.6 },
                            zIndex: 999999,
                            colors: ['#f43f5e', '#ec4899', '#8b5cf6', '#3b82f6', '#10b981', '#fbbf24']
                        });

                        this.isSpinning = false;
                        this.spinningType = null;
                        if (this.$wire && typeof this.$wire.spinFinished === 'function') {
                            this.$wire.spinFinished();
                        }

                        const isGenre = (data.step === 'genre');

                        if (isGenre) {
                            // Pop-up Genre: HANYA TAMPILKAN NAMA GENRE SAJA (misal: "ROCK")
                            Swal.fire({
                                title: `<div class="text-xl md:text-2xl font-extrabold text-amber-300 tracking-wide">🎲 GENRE 🎲</div>`,
                                html: `
                                    <div class="text-4xl md:text-6xl font-black text-transparent bg-clip-text bg-gradient-to-r from-pink-400 via-rose-300 to-amber-300 my-4 drop-shadow-xl leading-tight">
                                        ${winnerName}
                                    </div>
                                `,
                                background: '#090d16',
                                backdrop: `rgba(2, 6, 23, 0.90)`,
                                showConfirmButton: true,
                                confirmButtonText: 'Tutup',
                                confirmButtonColor: '#4f46e5',
                                timer: 10000,
                                timerProgressBar: true
                            });
                        } else {
                            // Pop-up Lagu: Tampilkan Judul Lagu serta Genre & Nama Peserta
                            Swal.fire({
                                title: `<div class="text-xl md:text-2xl font-extrabold text-amber-300 tracking-wide">🎵 LAGU 🎵</div>`,
                                html: `
                                    <div class="text-3xl md:text-5xl font-black text-transparent bg-clip-text bg-gradient-to-r from-pink-400 via-rose-300 to-amber-300 mt-4 mb-3 drop-shadow-xl leading-tight">
                                        ${winnerName}
                                    </div>
                                    <div class="text-slate-300 text-sm md:text-base mt-3">
                                        Genre: <strong class="text-amber-400">${data.genre || ''}</strong> <br>Peserta: <strong class="text-white">${data.participant || ''}</strong>
                                    </div>
                                `,
                                background: '#090d16',
                                backdrop: `rgba(2, 6, 23, 0.90)`,
                                showConfirmButton: true,
                                confirmButtonText: 'Tutup',
                                confirmButtonColor: '#4f46e5',
                                timer: 12000,
                                timerProgressBar: true
                            });
                        }

                    }, duration * 1000);
                }
            }));
        });
    </script>
</div>
