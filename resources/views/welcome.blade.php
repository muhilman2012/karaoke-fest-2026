<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KORPRI Musical Challenge 2026</title>
    <link rel="icon" type="image/png" href="{{ asset('logo/setneg.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('logo/setneg.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 font-sans text-white">
    <div class="pointer-events-none fixed inset-0 overflow-hidden">
        <div class="absolute -left-48 -top-48 h-[34rem] w-[34rem] rounded-full bg-indigo-600/20 blur-3xl"></div>
        <div class="absolute -right-48 top-1/3 h-[32rem] w-[32rem] rounded-full bg-pink-600/15 blur-3xl"></div>
        <div class="absolute bottom-0 left-1/3 h-80 w-80 rounded-full bg-cyan-500/10 blur-3xl"></div>
    </div>

    <main class="relative mx-auto w-full max-w-7xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
        <header class="mx-auto mb-12 max-w-4xl text-center sm:mb-16">
            <div class="mx-auto mb-5 flex h-20 w-20 items-center justify-center rounded-3xl border border-white/10 bg-white p-3 shadow-2xl shadow-indigo-950/50">
                <img src="{{ asset('logo/setneg.png') }}" alt="Logo Setneg" class="h-full w-full object-contain">
            </div>
            <span class="inline-flex items-center gap-2 rounded-full border border-indigo-400/30 bg-indigo-500/10 px-4 py-2 text-xs font-black uppercase tracking-[0.2em] text-indigo-200">Portal Acara</span>
            <h1 class="mt-5 bg-gradient-to-r from-white via-indigo-100 to-pink-300 bg-clip-text text-4xl font-black leading-tight text-transparent sm:text-6xl lg:text-7xl">KORPRI Musical Challenge 2026</h1>
            <p class="mx-auto mt-4 max-w-2xl text-sm leading-relaxed text-slate-400 sm:text-lg">Pilih akses sesuai kebutuhan Anda. Menu dikelompokkan untuk admin, juri, monitoring live, dan layar panggung.</p>
        </header>

        <div class="space-y-12">
            <section aria-labelledby="akses-utama">
                <div class="mb-5">
                    <p class="text-xs font-black uppercase tracking-[0.18em] text-emerald-400">Akses Utama</p>
                    <h2 id="akses-utama" class="mt-1 text-2xl font-black">Admin & Voting</h2>
                </div>
                <div class="grid gap-5 lg:grid-cols-2">
                    <a href="/admin" class="group relative overflow-hidden rounded-3xl border border-emerald-400/20 bg-gradient-to-br from-emerald-500/15 to-slate-900 p-6 shadow-xl transition hover:-translate-y-1 hover:border-emerald-400/60 sm:p-8">
                        <div class="absolute -right-12 -top-12 h-40 w-40 rounded-full bg-emerald-400/10 blur-2xl group-hover:bg-emerald-400/20"></div>
                        <div class="relative flex items-center gap-5">
                            <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-emerald-400/15 text-4xl ring-1 ring-emerald-300/20">⚙️</div>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2"><h3 class="text-2xl font-black">Admin Panel</h3><span class="rounded-full bg-emerald-400/15 px-2.5 py-1 text-[10px] font-black uppercase tracking-wider text-emerald-300">Satu Pintu</span></div>
                                <p class="mt-2 text-sm leading-relaxed text-slate-400 group-hover:text-slate-200">Kelola peserta, juri karaoke, Best Supporter, voting, live control, challenge, spinwheel, dan rekap.</p>
                            </div>
                            <span class="hidden text-2xl text-emerald-300 transition group-hover:translate-x-1 sm:block">→</span>
                        </div>
                    </a>
                    <a href="/vote" class="group relative overflow-hidden rounded-3xl border border-cyan-400/20 bg-gradient-to-br from-cyan-500/15 to-slate-900 p-6 shadow-xl transition hover:-translate-y-1 hover:border-cyan-400/60 sm:p-8">
                        <div class="absolute -right-12 -top-12 h-40 w-40 rounded-full bg-cyan-400/10 blur-2xl group-hover:bg-cyan-400/20"></div>
                        <div class="relative flex items-center gap-5">
                            <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-cyan-400/15 text-4xl ring-1 ring-cyan-300/20">📱</div>
                            <div class="min-w-0 flex-1"><h3 class="text-2xl font-black">Voting Penonton</h3><p class="mt-2 text-sm leading-relaxed text-slate-400 group-hover:text-slate-200">Berikan satu suara untuk peserta favorit melalui handphone.</p></div>
                            <span class="hidden text-2xl text-cyan-300 transition group-hover:translate-x-1 sm:block">→</span>
                        </div>
                    </a>
                </div>
            </section>

            <section aria-labelledby="penilaian-juri">
                <div class="mb-5"><p class="text-xs font-black uppercase tracking-[0.18em] text-orange-400">Penilaian</p><h2 id="penilaian-juri" class="mt-1 text-2xl font-black">Panel Juri</h2><p class="mt-1 text-sm text-slate-500">Gunakan halaman sesuai tugas penilaian masing-masing juri.</p></div>
                <div class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
                    <a href="/juri" class="group rounded-3xl border border-indigo-400/20 bg-slate-900/80 p-6 transition hover:-translate-y-1 hover:border-indigo-400/60 hover:bg-indigo-500/10 sm:p-7">
                        <div class="flex items-start gap-4"><div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-indigo-500/15 text-3xl">🎤</div><div><p class="text-[10px] font-black uppercase tracking-[0.16em] text-indigo-300">Penilaian Peserta</p><h3 class="mt-1 text-xl font-black">Juri Karaoke</h3><p class="mt-2 text-sm leading-relaxed text-slate-400">Teknik kualitas vokal, pembagian peran, dan kreativitas penampilan.</p><span class="mt-4 inline-flex items-center gap-2 text-sm font-black text-indigo-300">Masuk dengan PIN <span class="transition group-hover:translate-x-1">→</span></span></div></div>
                    </a>
                    <a href="/juri-supporter" class="group rounded-3xl border border-orange-400/20 bg-slate-900/80 p-6 transition hover:-translate-y-1 hover:border-orange-400/60 hover:bg-orange-500/10 sm:p-7">
                        <div class="flex items-start gap-4"><div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-orange-500/15 text-3xl">📣</div><div><p class="text-[10px] font-black uppercase tracking-[0.16em] text-orange-300">Penilaian Supporter</p><h3 class="mt-1 text-xl font-black">Juri Best Supporter</h3><p class="mt-2 text-sm leading-relaxed text-slate-400">Nilai kehebohan, kekompakan, kreativitas, dan ke-all-out-an supporter.</p><span class="mt-4 inline-flex items-center gap-2 text-sm font-black text-orange-300">Masuk dengan PIN <span class="transition group-hover:translate-x-1">→</span></span></div></div>
                    </a>
                </div>
            </section>

            <section aria-labelledby="live-monitoring">
                <div class="mb-5"><div class="flex items-center gap-2"><span class="h-2.5 w-2.5 animate-pulse rounded-full bg-red-500"></span><p class="text-xs font-black uppercase tracking-[0.18em] text-red-400">Live</p></div><h2 id="live-monitoring" class="mt-1 text-2xl font-black">Monitoring Langsung</h2></div>
                <div class="grid gap-5 md:grid-cols-3">
                    <a href="/live" target="_blank" class="group flex items-center gap-5 rounded-3xl border border-pink-400/20 bg-slate-900/80 p-6 transition hover:border-pink-400/60 hover:bg-pink-500/10"><div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-pink-500/15 text-3xl">📊</div><div class="flex-1"><h3 class="text-xl font-black">Live Score</h3><p class="mt-1 text-sm text-slate-400">Pantau nilai peserta yang sedang tampil.</p></div><span class="text-pink-300 transition group-hover:translate-x-1">↗</span></a>
                    <a href="/display/leaderboard" target="_blank" class="group flex items-center gap-5 rounded-3xl border border-amber-400/20 bg-slate-900/80 p-6 transition hover:border-amber-400/60 hover:bg-amber-500/10"><div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-amber-500/15 text-3xl">🏆</div><div class="flex-1"><h3 class="text-xl font-black">Live Leaderboard</h3><p class="mt-1 text-sm text-slate-400">Klasemen dari nilai juri yang telah dikunci.</p></div><span class="text-amber-300 transition group-hover:translate-x-1">↗</span></a>
                    <a href="/live-votes" target="_blank" class="group flex items-center gap-5 rounded-3xl border border-cyan-400/20 bg-slate-900/80 p-6 transition hover:border-cyan-400/60 hover:bg-cyan-500/10"><div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-cyan-500/15 text-3xl">🗳️</div><div class="flex-1"><h3 class="text-xl font-black">Live Hasil Voting</h3><p class="mt-1 text-sm text-slate-400">Peringkat suara peserta favorit secara langsung.</p></div><span class="text-cyan-300 transition group-hover:translate-x-1">↗</span></a>
                </div>
            </section>

            <section aria-labelledby="display-panggung">
                <div class="mb-5"><p class="text-xs font-black uppercase tracking-[0.18em] text-purple-400">Display</p><h2 id="display-panggung" class="mt-1 text-2xl font-black">Layar Panggung & Proyektor</h2><p class="mt-1 text-sm text-slate-500">Semua tautan dibuka pada tab baru untuk layar presentasi.</p></div>
                @php
                    $displays = [
                        ['url' => '/display/challenge', 'icon' => '🎤', 'title' => 'Musical Challenge', 'description' => 'Spin genre dan lagu untuk layar panggung.'],
                        ['url' => '/display/challenge-results', 'icon' => '📋', 'title' => 'Rekap Challenge', 'description' => 'Daftar genre dan lagu hasil undian peserta.'],
                        ['url' => '/display/spinwheel', 'icon' => '🎰', 'title' => 'Spinwheel', 'description' => 'Roda keberuntungan layar penuh.'],
                        ['url' => '/display/tradisional', 'icon' => '🎯', 'title' => 'Lomba Tradisional', 'description' => 'Display pemenang lomba tradisional.'],
                        ['url' => '/display/winners', 'icon' => '🥇', 'title' => 'Daftar Pemenang', 'description' => 'Tampilkan daftar pemenang di layar utama.'],
                    ];
                @endphp
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($displays as $display)
                        <a href="{{ $display['url'] }}" target="_blank" class="group rounded-2xl border border-slate-700/80 bg-slate-900/70 p-5 transition hover:-translate-y-1 hover:border-slate-500 hover:bg-slate-800"><div class="mb-4 flex items-center justify-between"><span class="text-3xl">{{ $display['icon'] }}</span><span class="text-slate-500 transition group-hover:translate-x-1 group-hover:text-white">↗</span></div><h3 class="text-lg font-black">{{ $display['title'] }}</h3><p class="mt-2 text-sm leading-relaxed text-slate-400">{{ $display['description'] }}</p></a>
                    @endforeach
                </div>
            </section>
        </div>

        <footer class="mt-16 border-t border-white/5 pt-6 text-center text-xs text-slate-600">&copy; 2026 KORPRI Musical Challenge • Lomba Karaoke Setwapres</footer>
    </main>
</body>
</html>
