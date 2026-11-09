<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Masuk · {{ config('app.name', 'AQHSE') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif; }
        [x-cloak] { display: none !important; }

        /* Foto bergerak sangat pelan (efek Ken Burns) */
        @keyframes slowzoom { from { transform: scale(1.04); } to { transform: scale(1.14); } }
        .photo-zoom { animation: slowzoom 28s ease-in-out infinite alternate; }

        /* Elemen muncul bertahap */
        @keyframes rise { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: none; } }
        .rise { opacity: 0; animation: rise .7s cubic-bezier(.2,.7,.2,1) forwards; }
        .d1 { animation-delay: .08s } .d2 { animation-delay: .16s } .d3 { animation-delay: .24s }
        .d4 { animation-delay: .32s } .d5 { animation-delay: .40s }

        @media (prefers-reduced-motion: reduce) {
            .photo-zoom, .rise { animation: none; opacity: 1; }
        }
    </style>
</head>
<body class="min-h-screen bg-white text-slate-800 antialiased">
<div class="flex min-h-screen flex-col lg:flex-row">

    {{-- =========================================================
         PANEL FOTO
         ========================================================= --}}
    <section class="relative h-64 overflow-hidden sm:h-80 lg:h-auto lg:w-[56%] xl:w-[60%]">
        {{-- Ganti path foto sesuai lokasi fotomu di folder public/ --}}
        <img src="{{ asset('images/bg-tambang.jpg') }}" alt=""
             class="photo-zoom absolute inset-0 h-full w-full object-cover">

        {{-- Overlay: gelap di bawah & kiri supaya teks terbaca, foto tetap terlihat --}}
        <div class="absolute inset-0 bg-gradient-to-t from-[#04241f]/95 via-[#063a33]/40 to-[#063a33]/10"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-[#04241f]/60 to-transparent"></div>

        <div class="relative flex h-full flex-col justify-between p-6 sm:p-10 xl:p-14">

            {{-- Logo --}}
            <div class="rise d1">
                <span class="inline-flex items-center rounded-2xl bg-white px-4 py-2.5 shadow-lg shadow-black/20">
                    {{-- Ganti path logo sesuai milikmu --}}
                    <img src="{{ asset('images/logo-adaro.png') }}" alt="Adaro" class="h-7 w-auto sm:h-8">
                </span>
            </div>

            {{-- Headline (disembunyikan di HP supaya banner tetap ringkas) --}}
            <div class="hidden max-w-xl text-white lg:block">
                <span class="rise d2 inline-flex items-center gap-2 rounded-full border border-white/25 bg-white/10 px-3.5 py-1.5 text-xs font-semibold uppercase tracking-[.18em] text-emerald-100 backdrop-blur">
                    <span class="h-1.5 w-1.5 rounded-full bg-amber-400"></span>
                    Sistem Manajemen AQHSE
                </span>

                <h2 class="rise d3 mt-6 text-4xl font-extrabold leading-[1.1] tracking-tight xl:text-5xl">
                    Bekerja aman,<br>
                    pulang <span class="text-amber-400">selamat.</span>
                </h2>

                <p class="rise d4 mt-5 max-w-md text-[15px] leading-relaxed text-white/75">
                    Satu tempat untuk mencatat, memantau, dan menindaklanjuti aspek kualitas,
                    kesehatan, keselamatan kerja, dan lingkungan di seluruh area operasi.
                </p>

                <div class="rise d5 mt-8 flex flex-wrap gap-2.5">
                    @foreach (['Quality', 'Health', 'Safety', 'Environment'] as $pilar)
                        <span class="rounded-xl border border-white/15 bg-white/10 px-4 py-2 text-sm font-medium text-white/90 backdrop-blur-md">
                            {{ $pilar }}
                        </span>
                    @endforeach
                </div>
            </div>

            <p class="hidden text-xs text-white/55 lg:block">
                &copy; {{ date('Y') }} PT Indonesia Bulk Terminal. Hak cipta dilindungi.
            </p>
        </div>
    </section>

    {{-- =========================================================
         PANEL FORM
         ========================================================= --}}
    <section class="relative -mt-8 flex flex-1 flex-col rounded-t-[28px] bg-white lg:mt-0 lg:rounded-none">

        {{-- Garis aksen brand --}}
        <div class="hidden h-1 bg-gradient-to-r from-emerald-600 via-emerald-500 to-amber-400 lg:block"></div>

        <div class="flex flex-1 items-center justify-center px-6 py-10 sm:px-10 lg:px-14">
            <div class="w-full max-w-[400px]">

                <div class="rise d1">
                    <p class="text-sm font-semibold text-emerald-700">Selamat datang kembali 👋</p>
                    <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-slate-900">Masuk ke akun Anda</h1>
                    <p class="mt-2 text-[15px] text-slate-500">Gunakan email perusahaan yang terdaftar di AQHSE.</p>
                </div>

                {{-- Status sesi (mis. setelah reset password) --}}
                @if (session('status'))
                    <div class="rise d2 mt-6 flex gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                        <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7"/></svg>
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5"
                      x-data="{ show: false, loading: false }" @submit="loading = true">
                    @csrf

                    {{-- Email --}}
                    <div class="rise d2">
                        <label for="email" class="mb-2 block text-sm font-semibold text-slate-700">Email</label>
                        <div class="group relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400 transition group-focus-within:text-emerald-600">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5A1.5 1.5 0 0 1 4.5 6h15A1.5 1.5 0 0 1 21 7.5v9a1.5 1.5 0 0 1-1.5 1.5h-15A1.5 1.5 0 0 1 3 16.5v-9Zm.5-.5L12 13l8.5-6"/></svg>
                            </span>
                            <input id="email" type="email" name="email" value="{{ old('email') }}"
                                   required autofocus autocomplete="username" placeholder="nama@adaro.com"
                                   class="block h-12 w-full rounded-xl border border-slate-200 bg-slate-50 pl-12 pr-4 text-[15px] text-slate-900 placeholder:text-slate-400 transition focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/15 @error('email') !border-rose-400 !bg-rose-50/40 focus:!ring-rose-500/15 @enderror">
                        </div>
                        @error('email')
                            <p class="mt-2 flex items-center gap-1.5 text-sm text-rose-600">
                                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="M12 8v4.5m0 3h.01"/></svg>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div class="rise d3">
                        <div class="mb-2 flex items-center justify-between">
                            <label for="password" class="block text-sm font-semibold text-slate-700">Password</label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}"
                                   class="rounded text-sm font-semibold text-emerald-700 transition hover:text-emerald-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500">
                                    Lupa password?
                                </a>
                            @endif
                        </div>
                        <div class="group relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400 transition group-focus-within:text-emerald-600">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><rect x="4.5" y="10.5" width="15" height="10" rx="2"/><path stroke-linecap="round" d="M8 10.5V7.5a4 4 0 0 1 8 0v3"/></svg>
                            </span>
                            <input id="password" name="password" type="password" :type="show ? 'text' : 'password'"
                                   required autocomplete="current-password" placeholder="••••••••"
                                   class="block h-12 w-full rounded-xl border border-slate-200 bg-slate-50 pl-12 pr-12 text-[15px] text-slate-900 placeholder:text-slate-400 transition focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/15 @error('password') !border-rose-400 !bg-rose-50/40 focus:!ring-rose-500/15 @enderror">
                            <button type="button" @click="show = !show"
                                    class="absolute inset-y-0 right-0 flex items-center rounded-r-xl px-4 text-slate-400 transition hover:text-emerald-700 focus:outline-none focus-visible:text-emerald-700"
                                    :aria-label="show ? 'Sembunyikan password' : 'Tampilkan password'">
                                <svg x-show="!show" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg x-show="show" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.6 10.6a2 2 0 0 0 2.8 2.8M9.9 5.2A9.8 9.8 0 0 1 12 5c6.4 0 10 7 10 7a17 17 0 0 1-3.2 4.1M6.6 6.6C3.9 8.4 2 12 2 12s3.6 7 10 7a9.6 9.6 0 0 0 5.4-1.6"/></svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Ingat saya --}}
                    <label for="remember_me" class="rise d4 flex w-fit cursor-pointer select-none items-center gap-2.5">
                        <input id="remember_me" type="checkbox" name="remember"
                               class="h-[18px] w-[18px] rounded-md border-slate-300 text-emerald-600 transition focus:ring-emerald-500/30">
                        <span class="text-sm text-slate-600">Ingat saya di perangkat ini</span>
                    </label>

                    {{-- Tombol masuk --}}
                    <div class="rise d5 pt-2">
                        <button type="submit" :disabled="loading"
                                class="group relative flex h-12 w-full items-center justify-center gap-2 overflow-hidden rounded-xl bg-emerald-700 text-[15px] font-bold text-white shadow-lg shadow-emerald-900/20 transition hover:bg-emerald-800 focus:outline-none focus-visible:ring-4 focus-visible:ring-emerald-500/30 disabled:cursor-wait disabled:opacity-80">
                            {{-- Kilau aksen kuning di tepi kanan --}}
                            <span class="absolute inset-y-0 right-0 w-1.5 bg-amber-400"></span>

                            <svg x-show="loading" x-cloak class="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" opacity=".25"/>
                                <path d="M22 12a10 10 0 0 0-10-10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                            </svg>
                            <span x-text="loading ? 'Memproses…' : 'Masuk'">Masuk</span>
                            <svg x-show="!loading" class="h-4 w-4 transition group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke-width="2.4" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6"/></svg>
                        </button>
                    </div>
                </form>

                {{-- Bantuan --}}
                <div class="rise d5 mt-10 flex items-start gap-3 rounded-2xl border border-slate-100 bg-slate-50 p-4">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-700">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" stroke-linejoin="round" d="M9.5 9a2.5 2.5 0 1 1 3.6 2.2c-.7.4-1.1 1-1.1 1.8M12 16.5h.01"/></svg>
                    </span>
                    <p class="text-sm leading-relaxed text-slate-600">
                        Belum punya akun atau tidak bisa masuk?
                        {{-- Ganti tautan bantuan sesuai kebutuhan --}}
                        <a href="mailto:admin.aqhse@adaro.com" class="font-semibold text-emerald-700 hover:underline">Hubungi admin AQHSE</a>.
                    </p>
                </div>
            </div>
        </div>

        <p class="pb-6 text-center text-xs text-slate-400 lg:hidden">
            &copy; {{ date('Y') }} PT Indonesia Bulk Terminal
        </p>
    </section>
</div>
</body>
</html>