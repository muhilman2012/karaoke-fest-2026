<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Roda Putar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/png" href="{{ asset('logo/setneg.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('logo/setneg.png') }}">
    @livewireStyles
</head>
<body class="bg-gray-50">
    
    <!-- Memanggil komponen Livewire yang ada di folder components/ -->
    <livewire:spinwheel-admin />

    @livewireScripts
</body>
</html>