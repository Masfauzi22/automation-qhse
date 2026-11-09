<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Daftar · {{ config('app.name', 'AQHSE') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif; }
        [x-cloak] { display: none !important; }

        @keyframes slowzoom { from { transform: scale(1.04); } to { transform: scale(1.14); } }
        .photo-zoom { animation: slowzoom 28s ease-in-out infinite alternate; }

        @keyframes rise { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: none; } }
        .rise { opacity: 0; animation: rise .7s cubic-bezier(.2,.7,.2,1) forwards; }
        .d1 { animation-delay: .08s } .d2 { animation-delay: .16s } .d3 { animation-delay: .24s }
        .d4 { animation-delay: .32s } .d5 { animation-delay: .40s } .d6 { animation-delay: .48s }

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
        <img src="{{ asset('images/bg-tambang.jpg') }}" alt=""
             class="photo-zoom absolute inset-0 h-full w-full object-cover">

        <div class="absolute inset-0 bg-gradient-to-t from-[#04241f]/95 via-[#063a33]/40 to-[#063a33]/10"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-[#04241f]/60 to-transparent"></div>

        <div class="relative flex h-full flex-col justify-between p-6 sm:p-10 xl:p-14">
            <div class="rise d1">
                <span class="inline-flex items-center rounded-2xl bg-white px-4 py-2.5 shadow-lg shadow-black/20">
                    <img src="{{ asset('images/logo-adaro.png') }}" alt="Adaro" class="h-7 w-auto sm:h-8">
                </span>
            </div>

            <div class="hidden max-w-xl text-white lg:block">
                <span class="rise d2 inline-flex items-center gap-2 rounded-full border border-white/25 bg-white/10 px-3.5 py-1.5 text-xs font-semibold uppercase tracking-[.18em] text-emerald-100 backdrop-blur">
                    <span class="h-1.5 w-1.5 rounded-full bg-amber-400"></span>
                    Sistem Manajemen AQHSE
                </span>

                <h2 class="rise d3 mt-6 text-4xl font-extrabold leading-[1.1] tracking-tight xl:text-5xl">
                    Keselamatan dimulai<br>
                    dari <span class="text-amber-400">kita semua.</span>
                </h2>

                <p class="rise d4 mt-5 max-w-md text-[15px] leading-relaxed text-white/75">
                    Buat akun untuk ikut melaporkan temuan, memantau tindak lanjut,
                    dan menjaga standar AQHSE di area kerja Anda.
                </p>

                <ul class="rise d5 mt-8 space-y-3">
                    @foreach ([
                        'Laporkan temuan & insiden dari mana saja',
                        'Pantau status tindak lanjut secara langsung',
                        'Semua data tersimpan rapi dalam satu sistem',
                    ] as $poin)
                        <li class="flex items-center gap-3 text-[15px] text-white/90">
                            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-400/20 text-emerald-300 ring-1 ring-emerald-300/40">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7"/></svg>
                            </span>
                            {{ $poin }}
                        </li>
                    @endforeach
                </ul>
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
        <div class="hidden h-1 bg-gradient-to-r from-emerald-600 via-emerald-500 to-amber-400 lg:block"></div>

        <div class="flex flex-1 items-center justify-center px-6 py-10 sm:px-10 lg:px-14">
            <div class="w-full max-w-[400px]">

                <div class="rise d1">
                    <p class="text-sm font-semibold text-emerald-700">Akun baru</p>
                    <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-slate-900">Daftar ke AQHSE</h1>
                    <p class="mt-2 text-[15px] text-slate-500">Isi data di bawah untuk membuat akun Anda.</p>
                </div>

                @php
                    $inputBase = 'block h-12 w-full rounded-xl border border-slate-200 bg-slate-50 pl-12 text-[15px] text-slate-900 placeholder:text-slate-400 transition focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/15';
                    $inputError = '!border-rose-400 !bg-rose-50/40 focus:!ring-rose-500/15';
                    $iconWrap = 'pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400 transition group-focus-within:text-emerald-600';
                @endphp

                <form method="POST" action="{{ route('register') }}" class="mt-8 space-y-5"
                      x-data="{ show: false, show2: false, loading: false, pw: '', pw2: '' }"
                      @submit="loading = true">
                    @csrf

                    {{-- Nama --}}
                    <div class="rise d2">
                        <label for="name" class="mb-2 block text-sm font-semibold text-slate-700">Nama lengkap</label>
                        <div class="group relative">
                            <span class="{{ $iconWrap }}">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><circle cx="12" cy="8" r="4"/><path stroke-linecap="round" d="M4.5 20a7.5 7.5 0 0 1 15 0"/></svg>
                            </span>
                            <input id="name" type="text" name="name" value="{{ old('name') }}"
                                   required autofocus autocomplete="name" placeholder="Nama sesuai ID karyawan"
                                   class="{{ $inputBase }} pr-4 @error('name') {{ $inputError }} @enderror">
                        </div>
                        @error('name') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    {{-- Email --}}
                    <div class="rise d3">
                        <label for="email" class="mb-2 block text-sm font-semibold text-slate-700">Email</label>
                        <div class="group relative">
                            <span class="{{ $iconWrap }}">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5A1.5 1.5 0 0 1 4.5 6h15A1.5 1.5 0 0 1 21 7.5v9a1.5 1.5 0 0 1-1.5 1.5h-15A1.5 1.5 0 0 1 3 16.5v-9Zm.5-.5L12 13l8.5-6"/></svg>
                            </span>
                            <input id="email" type="email" name="email" value="{{ old('email') }}"
                                   required autocomplete="username" placeholder="nama@adaro.com"
                                   class="{{ $inputBase }} pr-4 @error('email') {{ $inputError }} @enderror">
                        </div>
                        @error('email') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    {{-- Password --}}
                    <div class="rise d4">
                        <label for="password" class="mb-2 block text-sm font-semibold text-slate-700">Password</label>
                        <div class="group relative">
                            <span class="{{ $iconWrap }}">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><rect x="4.5" y="10.5" width="15" height="10" rx="2"/><path stroke-linecap="round" d="M8 10.5V7.5a4 4 0 0 1 8 0v3"/></svg>
                            </span>
                            <input id="password" name="password" type="password" :type="show ? 'text' : 'password'"
                                   x-model="pw" required autocomplete="new-password" placeholder="Minimal 8 karakter"
                                   class="{{ $inputBase }} pr-12 @error('password') {{ $inputError }} @enderror">
                            <button type="button" @click="show = !show"
                                    class="absolute inset-y-0 right-0 flex items-center px-4 text-slate-400 transition hover:text-emerald-700"
                                    :aria-label="show ? 'Sembunyikan password' : 'Tampilkan password'">
                                <svg x-show="!show" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg x-show="show" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.6 10.6a2 2 0 0 0 2.8 2.8M9.9 5.2A9.8 9.8 0 0 1 12 5c6.4 0 10 7 10 7a17 17 0 0 1-3.2 4.1M6.6 6.6C3.9 8.4 2 12 2 12s3.6 7 10 7a9.6 9.6 0 0 0 5.4-1.6"/></svg>
                            </button>
                        </div>

                        {{-- Indikator kekuatan password --}}
                        <div x-show="pw.length" x-cloak class="mt-2.5"
                             x-data="{ get score() { let s = 0; if (pw.length >= 8) s++; if (/[A-Z]/.test(pw) && /[a-z]/.test(pw)) s++; if (/\d/.test(pw)) s++; if (/[^A-Za-z0-9]/.test(pw)) s++; return s; } }">
                            <div class="flex gap-1.5">
                                <template x-for="i in 4">
                                    <span class="h-1 flex-1 rounded-full transition-colors"
                                          :class="i <= score ? (score <= 1 ? 'bg-rose-400' : score <= 2 ? 'bg-amber-400' : 'bg-emerald-500') : 'bg-slate-200'"></span>
                                </template>
                            </div>
                            <p class="mt-1.5 text-xs text-slate-500"
                               x-text="['Terlalu lemah', 'Lemah', 'Cukup', 'Kuat', 'Sangat kuat'][score]"></p>
                        </div>

                        @error('password') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    {{-- Konfirmasi password --}}
                    <div class="rise d5">
                        <label for="password_confirmation" class="mb-2 block text-sm font-semibold text-slate-700">Ulangi password</label>
                        <div class="group relative">
                            <span class="{{ $iconWrap }}">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3 5 6v5c0 4.5 3 8.3 7 10 4-1.7 7-5.5 7-10V6l-7-3Z"/><path stroke-linecap="round" stroke-linejoin="round" d="m9 12 2 2 4-4"/></svg>
                            </span>
                            <input id="password_confirmation" name="password_confirmation" type="password" :type="show2 ? 'text' : 'password'"
                                   x-model="pw2" required autocomplete="new-password" placeholder="Ketik ulang password"
                                   class="{{ $inputBase }} pr-12 @error('password_confirmation') {{ $inputError }} @enderror">
                            <button type="button" @click="show2 = !show2"
                                    class="absolute inset-y-0 right-0 flex items-center px-4 text-slate-400 transition hover:text-emerald-700"
                                    :aria-label="show2 ? 'Sembunyikan password' : 'Tampilkan password'">
                                <svg x-show="!show2" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg x-show="show2" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.6 10.6a2 2 0 0 0 2.8 2.8M9.9 5.2A9.8 9.8 0 0 1 12 5c6.4 0 10 7 10 7a17 17 0 0 1-3.2 4.1M6.6 6.6C3.9 8.4 2 12 2 12s3.6 7 10 7a9.6 9.6 0 0 0 5.4-1.6"/></svg>
                            </button>
                        </div>
                        <p x-show="pw2.length" x-cloak class="mt-2 flex items-center gap-1.5 text-xs"
                           :class="pw2 === pw ? 'text-emerald-600' : 'text-rose-600'">
                            <span x-text="pw2 === pw ? '✓ Password cocok' : 'Password belum sama'"></span>
                        </p>
                        @error('password_confirmation') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    {{-- Tombol daftar --}}
                    <div class="rise d6 pt-2">
                        <button type="submit" :disabled="loading"
                                class="group relative flex h-12 w-full items-center justify-center gap-2 overflow-hidden rounded-xl bg-emerald-700 text-[15px] font-bold text-white shadow-lg shadow-emerald-900/20 transition hover:bg-emerald-800 focus:outline-none focus-visible:ring-4 focus-visible:ring-emerald-500/30 disabled:cursor-wait disabled:opacity-80">
                            <span class="absolute inset-y-0 right-0 w-1.5 bg-amber-400"></span>
                            <svg x-show="loading" x-cloak class="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" opacity=".25"/>
                                <path d="M22 12a10 10 0 0 0-10-10" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                            </svg>
                            <span x-text="loading ? 'Memproses…' : 'Buat akun'">Buat akun</span>
                            <svg x-show="!loading" class="h-4 w-4 transition group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke-width="2.4" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6"/></svg>
                        </button>
                    </div>
                </form>

                <p class="rise d6 mt-8 text-center text-sm text-slate-500">
                    Sudah punya akun?
                    <a href="{{ route('login') }}" class="font-semibold text-emerald-700 hover:text-emerald-900 hover:underline">Masuk di sini</a>
                </p>
            </div>
        </div>

        <p class="pb-6 text-center text-xs text-slate-400 lg:hidden">
            &copy; {{ date('Y') }} PT Indonesia Bulk Terminal
        </p>
    </section>
</div>
</body>
</html>