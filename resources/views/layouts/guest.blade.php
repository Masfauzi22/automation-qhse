<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'AQHSE') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-gray-900 antialiased">
    <div class="relative min-h-screen flex flex-col items-center justify-center pt-6 sm:pt-0 overflow-hidden">

        <!-- Background Foto Tambang -->
        <div class="absolute inset-0 bg-cover bg-center"
             style="background-image: url('{{ asset('images/bg-tambang.jpg') }}');"></div>

        <!-- Overlay gelap supaya konten tetap terbaca -->
        <div class="absolute inset-0 bg-gradient-to-b from-black/70 via-black/50 to-black/70"></div>

        <!-- Konten -->
        <div class="relative z-10 flex flex-col items-center w-full px-4">

            <div class="mb-6 bg-white/90 rounded-xl px-6 py-2 shadow-md">
                <a href="/">
                    <img src="{{ asset('images/logo-adaro.png') }}" alt="Logo Adaro" class="h-12 w-auto object-contain">
                </a>
            </div>

            <div class="w-full sm:max-w-md px-6 py-8 bg-white/95 backdrop-blur-sm shadow-2xl overflow-hidden sm:rounded-2xl">
                {{ $slot }}
            </div>

            <p class="relative z-10 mt-6 text-xs text-white/70 text-center">
                &copy; {{ date('Y') }} PT Adaro Andalan Indonesia. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>