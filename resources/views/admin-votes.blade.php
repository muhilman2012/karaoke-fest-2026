<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Voting - Admin Panel</title>
    <link rel="icon" type="image/png" href="{{ asset('logo/setneg.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('logo/setneg.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 antialiased selection:bg-indigo-500 selection:text-white">
    
    <!-- Navigasi Admin Tambahan (Opsional, agar bisa kembali ke Dashboard) -->
    <div class="bg-indigo-900 text-white p-4 shadow-md">
        <div class="max-w-5xl mx-auto flex justify-between items-center">
            <div class="font-bold">🎤 Setwapres Karaoke Fest - Admin</div>
            <a href="/admin/dashboard" class="bg-indigo-700 hover:bg-indigo-600 px-4 py-2 rounded-lg text-sm font-bold transition">
                Kembali ke Dashboard Utama
            </a>
        </div>
    </div>

    <!-- Memanggil komponen rekap voting -->
    <livewire:admin-votes />

</body>
</html>