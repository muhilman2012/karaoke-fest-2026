<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Administrator</title>
    <link rel="icon" type="image/png" href="{{ asset('logo/setneg.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('logo/setneg.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-900 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white p-8 rounded-3xl shadow-2xl w-full max-w-md">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-black text-gray-800">Admin Panel</h1>
            <p class="text-gray-500 text-sm mt-1">Masukkan password untuk mengakses kontrol</p>
        </div>

        @if($errors->any())
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl text-sm font-bold text-center">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="/admin/login" method="POST" class="space-y-6">
            @csrf
            <div>
                <label class="block text-gray-700 font-bold mb-2">Password Admin</label>
                <input type="password" name="password" class="w-full border-2 border-gray-300 rounded-xl p-4 text-lg focus:border-indigo-600 focus:ring-indigo-600 font-mono" placeholder="••••••••" required autofocus>
            </div>

            <div class="flex gap-3">
                <a href="/" class="w-1/3 bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-4 rounded-xl text-center transition">Kembali</a>
                <button type="submit" class="w-2/3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 rounded-xl shadow-lg transition">Masuk</button>
            </div>
        </form>
    </div>
</body>
</html>