<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Sistem Penilaian Juri - Live Score</title>
    <link rel="icon" type="image/png" href="{{ asset('logo/setneg.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('logo/setneg.png') }}">
    
    <!-- Tailwind CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- CSS Khusus Tablet -->
    <style>
        input[type='number'] {
            -moz-appearance:textfield;
        }
        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
    </style>
</head>
<body class="bg-gray-100 antialiased selection:bg-indigo-500 selection:text-white">
    
    <!-- Memanggil komponen Livewire Single-File Anda di sini -->
    <livewire:judge-form />

</body>
</html>