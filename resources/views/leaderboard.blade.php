<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard Pemenang Lomba</title>
    <link rel="icon" type="image/png" href="{{ asset('logo/setneg.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('logo/setneg.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
    <style>
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        .animate-float { animation: float 3s ease-in-out infinite; }
        
        .reveal-step {
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.8s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .reveal-step.active {
            opacity: 1;
            transform: translateY(0);
        }

        /* Mekanisme Fixed 16:9 Aspect Ratio Container */
        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            background: #0f172a;
        }

        .screen-scaler {
            width: 1920px;
            height: 1080px;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            transform-origin: center center;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 50px 80px;
            box-sizing: border-box;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-900 via-indigo-900 to-slate-900 text-white font-sans">

    <!-- Wadah Tetap 1920x1080 (Rasio 16:9 Sempurna Tanpa Scroll) -->
    <div id="scaler" class="screen-scaler">

        <!-- Header Judul -->
        <div class="text-center">
            <h1 class="text-6xl font-black tracking-wider uppercase bg-clip-text text-transparent bg-gradient-to-r from-yellow-300 via-pink-300 to-white drop-shadow-lg">
                🏆 SETWAPRES KARAOKE FEST 2026 🏆
            </h1>
            <p class="text-blue-200 text-2xl font-medium mt-2">Leaderboard Pemenang Lomba Karaoke</p>
        </div>

        <!-- Container Utama Tata Letak Podium -->
        <div class="w-full flex flex-col items-center gap-6 my-auto">
            
            <!-- LEVEL 1: JUARA 1 (Tengah Atas) -->
            <div id="step-1" class="reveal-step w-[500px] bg-slate-800/90 backdrop-blur-md border-4 border-yellow-400 rounded-3xl p-6 text-center shadow-2xl ring-8 ring-yellow-400/20">
                <div class="inline-block bg-yellow-400 text-yellow-950 font-black px-6 py-1.5 rounded-full text-lg uppercase tracking-widest mb-3 shadow-lg animate-pulse">
                    🥇 JUARA 1
                </div>
                <div class="text-6xl animate-float mb-2">🏆</div>
                <h2 id="c1-name" class="text-4xl font-black mb-1 text-yellow-300 truncate">-</h2>
                <p id="c1-song" class="text-yellow-200/70 text-sm mb-4 italic truncate">-</p>
                <div class="bg-slate-900/90 w-full py-3 rounded-2xl border border-yellow-500/50">
                    <span class="text-xs text-yellow-400 block font-bold tracking-widest">SKOR AKHIR</span>
                    <span id="c1-score" class="text-4xl font-black text-yellow-400">0.00</span>
                </div>
            </div>

            <!-- LEVEL 2: JUARA 2 & 3 (Kanan Kiri) -->
            <div class="grid grid-cols-2 gap-12 w-full max-w-4xl">
                <!-- JUARA 2 (Kiri) -->
                <div id="step-2" class="reveal-step bg-slate-800/80 backdrop-blur-md border-4 border-slate-400 rounded-3xl p-5 text-center shadow-xl flex flex-col items-center">
                    <div class="bg-slate-400 text-slate-950 font-black px-5 py-1 rounded-full text-base uppercase tracking-widest mb-3 shadow">
                        🥈 JUARA 2
                    </div>
                    <div class="text-5xl animate-float mb-2">🏆</div>
                    <h2 id="c2-name" class="text-3xl font-black mb-1 text-slate-200 truncate w-full">-</h2>
                    <p id="c2-song" class="text-slate-400 text-xs mb-4 italic truncate w-full">-</p>
                    <div class="mt-auto bg-slate-900/80 w-full py-2.5 rounded-xl border border-slate-700">
                        <span class="text-xs text-slate-400 block tracking-widest">SKOR AKHIR</span>
                        <span id="c2-score" class="text-3xl font-black text-slate-300">0.00</span>
                    </div>
                </div>

                <!-- JUARA 3 (Kanan) -->
                <div id="step-3" class="reveal-step bg-slate-800/80 backdrop-blur-md border-4 border-amber-600 rounded-3xl p-5 text-center shadow-xl flex flex-col items-center">
                    <div class="bg-amber-600 text-white font-black px-5 py-1 rounded-full text-base uppercase tracking-widest mb-3 shadow">
                        🥉 JUARA 3
                    </div>
                    <div class="text-5xl animate-float mb-2">🏆</div>
                    <h2 id="c3-name" class="text-3xl font-black mb-1 text-amber-200 truncate w-full">-</h2>
                    <p id="c3-song" class="text-amber-400/70 text-xs mb-4 italic truncate w-full">-</p>
                    <div class="mt-auto bg-slate-900/80 w-full py-2.5 rounded-xl border border-amber-800">
                        <span class="text-xs text-amber-400 block tracking-widest">SKOR AKHIR</span>
                        <span id="c3-score" class="text-3xl font-black text-amber-300">0.00</span>
                    </div>
                </div>
            </div>

            <!-- LEVEL 3: JUARA FAVORIT (Tengah Bawah) -->
            <div id="step-4" class="reveal-step w-[420px] bg-slate-800/80 backdrop-blur-md border-4 border-yellow-500 rounded-3xl p-4 text-center shadow-xl">
                <div class="inline-block bg-gradient-to-r from-yellow-500 to-amber-600 text-slate-950 font-black px-5 py-0.5 rounded-full text-xs uppercase tracking-widest mb-2 shadow">
                    🌟 JUARA FAVORIT
                </div>
                <div class="text-4xl animate-float mb-1">⭐</div>
                <h2 id="cfav-name" class="text-2xl font-black mb-0.5 text-yellow-300 truncate">Belum Ditentukan</h2>
                <p id="cfav-song" class="text-slate-400 text-xs italic truncate">-</p>
            </div>

        </div>

        <!-- Footer Kosong Penyeimbang Ruang -->
        <div class="h-4"></div>

    </div>

    <!-- Skrip Otomatis Skala 16:9 & Animasi -->
    <script>
        // Skrip Skala Otomatis agar pas di semua ukuran layar monitor/proyektor
        function autoResizeScreen() {
            const scaler = document.getElementById('scaler');
            const targetWidth = 1920;
            const targetHeight = 1080;
            
            let windowWidth = window.innerWidth;
            let windowHeight = window.innerHeight;
            
            let scaleX = windowWidth / targetWidth;
            let scaleY = windowHeight / targetHeight;
            let scale = Math.min(scaleX, scaleY);
            
            scaler.style.transform = `translate(-50%, -50%) scale(${scale})`;
        }

        window.addEventListener('resize', autoResizeScreen);
        window.addEventListener('DOMContentLoaded', autoResizeScreen);

        // Skrip Animasi & Fetch Data Leaderboard
        let hasTriggeredSequence = false;
        let lastChampionId = null;

        function startContinuousRain() {
            setInterval(() => {
                confetti({
                    particleCount: 300,
                    angle: 90,
                    spread: 120,
                    origin: { x: Math.random(), y: -0.1 },
                    colors: ['#F59E0B', '#EF4444', '#3B82F6', '#10B981', '#8B5CF6'],
                    ticks: 300,
                    gravity: 0.8,
                    scalar: 1.2
                });
            }, 1000);
        }

        async function fetchLeaderboard() {
            try {
                let response = await fetch('/api/leaderboard-data');
                let data = await response.json();

                if (data.champion1) {
                    if (lastChampionId !== data.champion1.id) {
                        lastChampionId = data.champion1.id;
                        hasTriggeredSequence = false;
                        resetAnimationSteps();
                    }

                    document.getElementById('c1-name').innerText = data.champion1.name;
                    document.getElementById('c1-song').innerText = `"${data.champion1.song_title || '-'}"`;
                    document.getElementById('c1-score').innerText = parseFloat(data.champion1.average_score).toFixed(2);
                }

                if (data.champion2) {
                    document.getElementById('c2-name').innerText = data.champion2.name;
                    document.getElementById('c2-song').innerText = `"${data.champion2.song_title || '-'}"`;
                    document.getElementById('c2-score').innerText = parseFloat(data.champion2.average_score).toFixed(2);
                }

                if (data.champion3) {
                    document.getElementById('c3-name').innerText = data.champion3.name;
                    document.getElementById('c3-song').innerText = `"${data.champion3.song_title || '-'}"`;
                    document.getElementById('c3-score').innerText = parseFloat(data.champion3.average_score).toFixed(2);
                }

                if (data.favorite) {
                    document.getElementById('cfav-name').innerText = data.favorite.name;
                    document.getElementById('cfav-song').innerText = `"${data.favorite.song_title || '-'}"`;
                } else {
                    document.getElementById('cfav-name').innerText = 'Belum Dipilih';
                    document.getElementById('cfav-song').innerText = '-';
                }

                if (data.champion1 && !hasTriggeredSequence) {
                    hasTriggeredSequence = true;
                    runRevealSequence();
                }

            } catch (error) {
                console.error("Gagal memuat leaderboard:", error);
            }
        }

        function resetAnimationSteps() {
            document.querySelectorAll('.reveal-step').forEach(el => el.classList.remove('active'));
        }

        function runRevealSequence() {
            // 0.3 Detik: Juara 1 Muncul Duluan di Tengah Atas
            setTimeout(() => {
                document.getElementById('step-1').classList.add('active');
                confetti({ particleCount: 120, spread: 80, origin: { y: 0.4 } });
            }, 300);

            // 2.2 Detik: Menyusul Juara 2 di Kiri
            setTimeout(() => {
                document.getElementById('step-2').classList.add('active');
                confetti({ particleCount: 60, spread: 90, origin: { x: 0.3, y: 0.6 } });
            }, 2200);

            // 4.0 Detik: Menyusul Juara 3 di Kanan (Ada jeda dramatis dari Juara 2)
            setTimeout(() => {
                document.getElementById('step-3').classList.add('active');
                confetti({ particleCount: 60, spread: 90, origin: { x: 0.7, y: 0.6 } });
            }, 4000);

            // 5.8 Detik: Terakhir Juara Favorit di Bawah + Hujan Confetti Abadi Dimulai
            setTimeout(() => {
                document.getElementById('step-4').classList.add('active');
                confetti({ particleCount: 100, spread: 120, origin: { y: 0.8 } });
                startContinuousRain();
            }, 5800);
        }

        fetchLeaderboard();
        setInterval(fetchLeaderboard, 2000);
    </script>
</body>
</html>