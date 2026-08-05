<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Penilaian Lomba Karaoke</title>
    <link rel="icon" type="image/png" href="{{ asset('logo/setneg.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('logo/setneg.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-br from-slate-900 via-indigo-950 to-blue-950 min-h-screen text-white font-sans flex flex-col items-center justify-center p-6">

    <div class="max-w-4xl w-full text-center">
        <div class="mb-12">
            <span class="bg-indigo-500/20 border border-indigo-500 text-indigo-300 px-4 py-1.5 rounded-full text-sm font-bold tracking-widest uppercase">
                Selamat Datang
            </span>
            <h1 class="text-5xl md:text-7xl font-black mt-4 tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-white via-indigo-200 to-indigo-400">
                LOMBA DEFILE SETWAPRES 2026
            </h1>
            <p class="text-gray-400 text-lg mt-3">Silakan pilih menu navigasi di bawah ini sesuai peran Anda.</p>
        </div>

        <!-- Grid Menu Pilihan -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-2xl mx-auto">
            
            <!-- Menu Juri -->
            <a href="/juri" class="group bg-slate-800/80 hover:bg-indigo-600 border border-slate-700 hover:border-indigo-500 p-8 rounded-3xl shadow-xl transition-all duration-300 transform hover:-translate-y-1 text-left flex items-center gap-6">
                <div class="text-5xl bg-indigo-500/20 p-4 rounded-2xl group-hover:bg-white/20 transition">👨‍⚖️</div>
                <div>
                    <h2 class="text-2xl font-black group-hover:text-white">Panel Juri</h2>
                    <p class="text-gray-400 text-sm group-hover:text-indigo-100 mt-1">Masuk menggunakan PIN Passcode Juri</p>
                </div>
            </a>

            <!-- Menu Live Score -->
            <a href="/live" target="_blank" class="group bg-slate-800/80 hover:bg-pink-600 border border-slate-700 hover:border-pink-500 p-8 rounded-3xl shadow-xl transition-all duration-300 transform hover:-translate-y-1 text-left flex items-center gap-6">
                <div class="text-5xl bg-pink-500/20 p-4 rounded-2xl group-hover:bg-white/20 transition">📺</div>
                <div>
                    <h2 class="text-2xl font-black group-hover:text-white">Live Score Layar</h2>
                    <p class="text-gray-400 text-sm group-hover:text-pink-100 mt-1">Tampilan skor sementara untuk panggung</p>
                </div>
            </a>

            <!-- Menu Leaderboard -->
            <a href="/leaderboard" target="_blank" class="group bg-slate-800/80 hover:bg-amber-600 border border-slate-700 hover:border-amber-500 p-8 rounded-3xl shadow-xl transition-all duration-300 transform hover:-translate-y-1 text-left flex items-center gap-6">
                <div class="text-5xl bg-amber-500/20 p-4 rounded-2xl group-hover:bg-white/20 transition">🏆</div>
                <div>
                    <h2 class="text-2xl font-black group-hover:text-white">Leaderboard Podium</h2>
                    <p class="text-gray-400 text-sm group-hover:text-amber-100 mt-1">Pengumuman pemenang & juara favorit</p>
                </div>
            </a>

            <!-- Menu Admin -->
            <a href="/admin/login" class="group bg-slate-800/80 hover:bg-emerald-600 border border-slate-700 hover:border-emerald-500 p-8 rounded-3xl shadow-xl transition-all duration-300 transform hover:-translate-y-1 text-left flex items-center gap-6">
                <div class="text-5xl bg-emerald-500/20 p-4 rounded-2xl group-hover:bg-white/20 transition">⚙️</div>
                <div>
                    <h2 class="text-2xl font-black group-hover:text-white">Admin Panel</h2>
                    <p class="text-gray-400 text-sm group-hover:text-emerald-100 mt-1">Kontrol lomba, peserta, juri, & rekap</p>
                </div>
            </a>

            <a href="/vote" class="group bg-slate-800/80 hover:bg-cyan-600 border border-slate-700 hover:border-cyan-500 p-8 rounded-3xl shadow-xl transition-all duration-300 transform hover:-translate-y-1 text-left flex items-center gap-6">
                <div class="text-5xl bg-cyan-500/20 p-4 rounded-2xl group-hover:bg-white/20 transition">📱</div>
                <div>
                    <h2 class="text-2xl font-black group-hover:text-white">Voting Penonton</h2>
                    <p class="text-gray-400 text-sm group-hover:text-cyan-100 mt-1">Dukung peserta favoritmu di sini!</p>
                </div>
            </a>
        </div>

        <div class="mt-16 text-gray-500 text-xs">
            &copy; 2026 Made by Muhammad Hilman. All rights reserved.
        </div>
    </div>

</body>
</html>