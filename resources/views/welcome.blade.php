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

    <div class="max-w-4xl w-full text-center mt-10 mb-10">
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
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-3xl mx-auto">
            
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
            <a href="/display/leaderboard" target="_blank" class="group bg-slate-800/80 hover:bg-amber-600 border border-slate-700 hover:border-amber-500 p-8 rounded-3xl shadow-xl transition-all duration-300 transform hover:-translate-y-1 text-left flex items-center gap-6">
                <div class="text-5xl bg-amber-500/20 p-4 rounded-2xl group-hover:bg-white/20 transition">🏆</div>
                <div>
                    <h2 class="text-2xl font-black group-hover:text-white">Live Leaderboard</h2>
                    <p class="text-gray-400 text-sm group-hover:text-amber-100 mt-1">Live Klasemen Peserta Defile</p>
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

            <!-- Menu Voting Penonton (Dibuat full width col-span-2 agar rapi) -->
            <a href="/vote" class="group md:col-span-2 bg-slate-800/80 hover:bg-cyan-600 border border-slate-700 hover:border-cyan-500 p-8 rounded-3xl shadow-xl transition-all duration-300 transform hover:-translate-y-1 text-left flex items-center gap-6 justify-center md:justify-start">
                <div class="text-5xl bg-cyan-500/20 p-4 rounded-2xl group-hover:bg-white/20 transition">📱</div>
                <div>
                    <h2 class="text-2xl font-black group-hover:text-white">Voting Penonton</h2>
                    <p class="text-gray-400 text-sm group-hover:text-cyan-100 mt-1">Dukung peserta favoritmu di sini menggunakan Smartphone Anda!</p>
                </div>
            </a>

            <!-- Kartu Menu Spinwheel Display -->
            <a href="/display/spinwheel" target="_blank" class="group relative block text-left bg-slate-900 border border-slate-700 rounded-3xl p-8 hover:bg-slate-800 transition transform hover:-translate-y-2 hover:shadow-2xl hover:shadow-pink-500/20 overflow-hidden">
                <!-- Efek Latar Glow -->
                <div class="absolute -right-10 -top-10 w-40 h-40 bg-pink-500/20 rounded-full blur-3xl group-hover:bg-pink-500/30 transition"></div>
                
                <div class="text-6xl mb-6 transform group-hover:rotate-12 transition duration-500">🎰</div>
                <h2 class="text-2xl font-black text-white mb-2">Display Spinwheel</h2>
                <p class="text-slate-400">Buka layar utama Roda Keberuntungan untuk ditampilkan di Proyektor / Layar LED. Tampilan Full-Screen.</p>
                
                <div class="mt-8 flex items-center text-pink-400 font-bold group-hover:text-pink-300">
                    Buka Layar <span class="ml-2">→</span>
                </div>
            </a>

            <!-- Kartu Menu Tradisional Display (BARU) -->
            <a href="/display/tradisional" target="_blank" class="group relative block text-left bg-slate-900 border border-slate-700 rounded-3xl p-8 hover:bg-slate-800 transition transform hover:-translate-y-2 hover:shadow-2xl hover:shadow-teal-500/20 overflow-hidden">
                <!-- Efek Latar Glow -->
                <div class="absolute -left-10 -top-10 w-40 h-40 bg-teal-500/20 rounded-full blur-3xl group-hover:bg-teal-500/30 transition"></div>
                
                <div class="text-6xl mb-6 transform group-hover:-rotate-12 transition duration-500">🎯</div>
                <h2 class="text-2xl font-black text-white mb-2">Display Tradisional</h2>
                <p class="text-slate-400">Buka layar utama daftar pemenang lomba tradisional untuk ditampilkan di Proyektor / Layar LED.</p>
                
                <div class="mt-8 flex items-center text-teal-400 font-bold group-hover:text-teal-300">
                    Buka Layar <span class="ml-2">→</span>
                </div>
            </a>
            
        </div>

        <div class="mt-16 text-gray-500 text-xs">
            &copy; 2026 Made by Muhammad Hilman. All rights reserved.
        </div>
    </div>

</body>
</html>