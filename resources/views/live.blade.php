<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Layar Live Scoring</title>
    <link rel="icon" type="image/png" href="{{ asset('logo/setneg.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('logo/setneg.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
</head>
<body class="bg-gray-900 antialiased">
    
    <livewire:live-score />

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            let currentDisplayedScore = 0;
            let targetScore = 0;
            let judgesCount = 0;
            let totalJudges = 3;
            let hasFiredConfetti = false;
            
            const rollSpeed = 0.5; // Kecepatan putar stopwatch angka

            // 1. Fetch data dari API secara mandiri setiap 1 detik tanpa reload Livewire
            setInterval(async () => {
                try {
                    let response = await fetch('/api/live-score');
                    let data = await response.json();

                    if (data.participant) {
                        let newTarget = parseFloat(data.currentScore) || 0;
                        
                        // Jika peserta diganti atau nilai turun, reset instan
                        if (newTarget < targetScore || window.currentParticipantId !== data.participant.id) {
                            currentDisplayedScore = newTarget;
                            hasFiredConfetti = false;
                            window.currentParticipantId = data.participant.id;
                        }

                        targetScore = newTarget;
                        judgesCount = data.judgesCount;
                        totalJudges = data.totalJudges;

                        // Render ulang elemen HTML di layar live
                        renderLiveScreen(data.participant, judgesCount, totalJudges);
                    } else {
                        targetScore = 0;
                        currentDisplayedScore = 0;
                        judgesCount = 0;
                        hasFiredConfetti = false;
                        window.currentParticipantId = null;
                        renderStandbyScreen();
                    }
                } catch (error) {
                    console.error("Gagal mengambil data live score:", error);
                }
            }, 1000);

            // 2. Loop Animasi 60fps untuk Stopwatch Angka
            function animateLoop() {
                const scoreElement = document.getElementById('animated-score');

                if (currentDisplayedScore < targetScore) {
                    currentDisplayedScore += rollSpeed;
                    if (currentDisplayedScore > targetScore) {
                        currentDisplayedScore = targetScore;
                    }
                } else if (currentDisplayedScore > targetScore) {
                    currentDisplayedScore = targetScore;
                }

                if (scoreElement) {
                    scoreElement.innerText = currentDisplayedScore.toFixed(2);
                }

                // 3. Logika Confetti saat semua juri selesai menilai
                if (judgesCount === totalJudges && currentDisplayedScore === targetScore && !hasFiredConfetti && targetScore > 0) {
                    hasFiredConfetti = true;
                    
                    let duration = 3000;
                    let end = Date.now() + duration;

                    (function frame() {
                        confetti({ particleCount: 5, angle: 60, spread: 55, origin: { x: 0 }, colors: ['#4F46E5', '#9333EA', '#10B981', '#F59E0B'] });
                        confetti({ particleCount: 5, angle: 120, spread: 55, origin: { x: 1 }, colors: ['#4F46E5', '#9333EA', '#10B981', '#F59E0B'] });

                        if (Date.now() < end) {
                            requestAnimationFrame(frame);
                        }
                    }());
                }

                if (judgesCount < totalJudges) {
                    hasFiredConfetti = false;
                }

                requestAnimationFrame(animateLoop);
            }

            requestAnimationFrame(animateLoop);

            // Helper Render Tampilan Aktif
            function renderLiveScreen(participant, jCount, tJudges) {
                const container = document.getElementById('live-container');
                if (!container) return;

                // Cek apakah struktur utama sudah ada agar tidak mereset animasi DOM yang sedang berjalan
                if (!document.getElementById('animated-score')) {
                    container.innerHTML = `
                        <div class="mb-12 inline-flex items-center gap-3 px-6 py-2 bg-red-500/20 border border-red-500 text-red-400 rounded-full text-xl font-bold tracking-widest uppercase">
                            <span class="w-4 h-4 bg-red-500 rounded-full animate-ping"></span>
                            LIVE PERFORMANCES
                        </div>
                        <h1 id="p-name" class="text-7xl font-black mb-4 tracking-tight drop-shadow-lg text-transparent bg-clip-text bg-gradient-to-r from-white to-gray-400"></h1>
                        <p id="p-song" class="text-4xl font-medium text-indigo-300 mb-16 drop-shadow-md"></p>
                        <div class="bg-gray-800/85 backdrop-blur-xl border-4 border-gray-700 rounded-3xl p-16 shadow-2xl max-w-4xl mx-auto">
                            <p class="text-2xl text-gray-400 font-bold tracking-widest uppercase mb-4">TOTAL SKOR SEMENTARA</p>
                            <div class="text-[12rem] leading-none font-black text-transparent bg-clip-text bg-gradient-to-br from-indigo-400 to-purple-500 drop-shadow-2xl">
                                <span id="animated-score">0.00</span>
                            </div>
                            <div id="judge-indicators" class="mt-12 flex justify-center gap-6"></div>
                            <p id="judge-status-text" class="mt-6 text-xl text-gray-400 font-semibold"></p>
                        </div>
                        <div id="finished-badge"></div>
                    `;
                }

                // Update teks dinamis tanpa merusak elemen skor
                document.getElementById('p-name').innerText = participant.name;
                document.getElementById('p-song').innerText = `"${participant.song_title || '-'}"`;
                
                // Render indikator juri
                let indicatorsHtml = '';
                for (let i = 1; i <= tJudges; i++) {
                    let active = i <= jCount;
                    indicatorsHtml += `
                        <div class="w-16 h-16 rounded-full flex items-center justify-center text-2xl font-bold transition-all duration-500 ${active ? 'bg-green-500 text-white scale-110 shadow-[0_0_20px_rgba(34,197,94,0.5)]' : 'bg-gray-700 text-gray-500'}">
                            ${active ? '✓' : '?'}
                        </div>
                    `;
                }
                document.getElementById('judge-indicators').innerHTML = indicatorsHtml;
                document.getElementById('judge-status-text').innerText = `${jCount} dari ${tJudges} Juri telah menilai`;

                // Badge selesai
                let finishedEl = document.getElementById('finished-badge');
                if (jCount === tJudges && tJudges > 0) {
                    finishedEl.innerHTML = `<div class="mt-10 text-3xl font-black text-green-400 animate-bounce drop-shadow-lg">PENILAIAN SELESAI!</div>`;
                } else {
                    finishedEl.innerHTML = '';
                }
            }

            // Helper Render Tampilan Standby
            function renderStandbyScreen() {
                const container = document.getElementById('live-container');
                if (!container || container.dataset.status === 'standby') return;
                
                container.dataset.status = 'standby';
                container.innerHTML = `
                    <div class="text-center">
                        <img src="https://laravel.com/img/logomark.min.svg" alt="Logo" class="w-48 h-48 mx-auto mb-12 opacity-50">
                        <h1 class="text-6xl font-black text-gray-300 mb-6 tracking-tight">KARAOKE COMPETITION</h1>
                        <p class="text-3xl text-indigo-400 animate-pulse">Menunggu peserta memasuki panggung...</p>
                    </div>
                `;
            }
        });
    </script>
</body>
</html>