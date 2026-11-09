<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'AQHSE') }} · PT Indonesia Bulk Terminal</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Plus Jakarta Sans', ui-sans-serif, system-ui, sans-serif; }

        @keyframes slowzoom { from { transform: scale(1.04); } to { transform: scale(1.14); } }
        .photo-zoom { animation: slowzoom 30s ease-in-out infinite alternate; }

        @keyframes rise { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: none; } }
        .rise { opacity: 0; animation: rise .8s cubic-bezier(.2,.7,.2,1) forwards; }
        .d1 { animation-delay: .1s } .d2 { animation-delay: .2s } .d3 { animation-delay: .3s }
        .d4 { animation-delay: .4s } .d5 { animation-delay: .55s }

        @keyframes nudge { 0%,100% { transform: translateY(0); } 50% { transform: translateY(6px); } }
        .nudge { animation: nudge 1.8s ease-in-out infinite; }

        @media (prefers-reduced-motion: reduce) {
            .photo-zoom, .rise, .nudge { animation: none; opacity: 1; }
        }
    </style>
</head>
<body class="bg-white text-slate-800 antialiased">

{{-- =========================================================
     HERO
     ========================================================= --}}
<section class="relative flex min-h-screen flex-col overflow-hidden text-white">
    <img src="{{ asset('images/bg-tambang.jpg') }}" alt=""
         class="photo-zoom absolute inset-0 h-full w-full object-cover">
    <div class="absolute inset-0 bg-gradient-to-t from-[#04241f] via-[#063a33]/55 to-[#063a33]/25"></div>
    <div class="absolute inset-0 bg-gradient-to-r from-[#04241f]/75 via-[#04241f]/20 to-transparent"></div>

    {{-- Navigasi --}}
    <header class="relative z-10">
        <nav class="mx-auto flex max-w-7xl items-center justify-between px-6 py-6 lg:px-10">
            <a href="{{ url('/') }}" class="rise d1 inline-flex items-center rounded-2xl bg-white px-4 py-2.5 shadow-lg shadow-black/20">
                <img src="{{ asset('images/logo-adaro.png') }}" alt="Adaro" class="h-7 w-auto">
            </a>

            @if (Route::has('login'))
                <div class="rise d1 flex items-center gap-2 sm:gap-3">
                    @auth
                        <a href="{{ url('/dashboard') }}"
                           class="inline-flex items-center gap-2 rounded-xl bg-white px-5 py-2.5 text-sm font-bold text-emerald-800 shadow-lg shadow-black/20 transition hover:bg-emerald-50">
                            Buka Dashboard
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.4" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6"/></svg>
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                           class="rounded-xl px-4 py-2.5 text-sm font-semibold text-white/90 transition hover:bg-white/10 hover:text-white">
                            Masuk
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}"
                               class="rounded-xl border border-white/30 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white backdrop-blur transition hover:bg-white/20">
                                Daftar
                            </a>
                        @endif
                    @endauth
                </div>
            @endif
        </nav>
    </header>

    {{-- Isi hero --}}
    <div class="relative z-10 mx-auto flex w-full max-w-7xl flex-1 items-end px-6 pb-16 pt-10 lg:items-center lg:px-10 lg:pb-24">
        <div class="grid w-full items-end gap-12 lg:grid-cols-[1.25fr_1fr] lg:items-center">

            <div>
                <span class="rise d2 inline-flex items-center gap-2 rounded-full border border-white/25 bg-white/10 px-3.5 py-1.5 text-xs font-semibold uppercase tracking-[.18em] text-emerald-100 backdrop-blur">
                    <span class="h-1.5 w-1.5 rounded-full bg-amber-400"></span>
                    Sistem Manajemen AQHSE
                </span>

                <h1 class="rise d3 mt-6 text-[2.6rem] font-extrabold leading-[1.05] tracking-tight sm:text-6xl xl:text-7xl">
                    Bekerja aman,<br>
                    pulang <span class="text-amber-400">selamat.</span>
                </h1>

                <p class="rise d4 mt-6 max-w-xl text-base leading-relaxed text-white/75 sm:text-lg">
                    Platform terpadu PT Indonesia Bulk Terminal untuk mencatat, memantau, dan
                    menindaklanjuti aspek kualitas, kesehatan, keselamatan kerja, dan lingkungan.
                </p>

                <div class="rise d5 mt-9 flex flex-wrap gap-3">
                    @auth
                        <a href="{{ url('/dashboard') }}"
                           class="group relative inline-flex items-center gap-2 overflow-hidden rounded-xl bg-emerald-600 px-7 py-3.5 text-[15px] font-bold shadow-xl shadow-black/25 transition hover:bg-emerald-700">
                            <span class="absolute inset-y-0 right-0 w-1.5 bg-amber-400"></span>
                            Lanjut ke Dashboard
                            <svg class="h-4 w-4 transition group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke-width="2.4" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6"/></svg>
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                           class="group relative inline-flex items-center gap-2 overflow-hidden rounded-xl bg-emerald-600 px-7 py-3.5 text-[15px] font-bold shadow-xl shadow-black/25 transition hover:bg-emerald-700">
                            <span class="absolute inset-y-0 right-0 w-1.5 bg-amber-400"></span>
                            Masuk ke AQHSE
                            <svg class="h-4 w-4 transition group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke-width="2.4" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14m-6-6 6 6-6 6"/></svg>
                        </a>
                    @endauth
                    <a href="#tentang"
                       class="inline-flex items-center gap-2 rounded-xl border border-white/25 bg-white/5 px-7 py-3.5 text-[15px] font-semibold text-white backdrop-blur transition hover:bg-white/15">
                        Pelajari lebih lanjut
                    </a>
                </div>
            </div>

            {{-- Kartu kaca: 4 pilar --}}
            <div class="rise d5 hidden lg:block">
                <div class="rounded-3xl border border-white/15 bg-white/10 p-6 shadow-2xl shadow-black/30 backdrop-blur-xl">
                    <p class="text-xs font-semibold uppercase tracking-[.18em] text-white/60">Empat pilar kami</p>
                    <ul class="mt-5 space-y-3">
                        @foreach ([
                            ['Q', 'Quality', 'Standar mutu di setiap proses kerja'],
                            ['H', 'Health', 'Kesehatan pekerja sebagai prioritas'],
                            ['S', 'Safety', 'Nihil kecelakaan, tanpa kompromi'],
                            ['E', 'Environment', 'Operasi yang menjaga alam sekitar'],
                        ] as [$huruf, $judul, $desc])
                            <li class="flex items-center gap-4 rounded-2xl bg-white/5 p-3 ring-1 ring-white/10 transition hover:bg-white/10">
                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-700 text-lg font-extrabold shadow-inner">
                                    {{ $huruf }}
                                </span>
                                <div>
                                    <p class="font-bold">{{ $judul }}</p>
                                    <p class="text-sm text-white/65">{{ $desc }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- Petunjuk scroll --}}
    <a href="#tentang" class="nudge absolute bottom-6 left-1/2 z-10 hidden -translate-x-1/2 text-white/60 transition hover:text-white lg:block" aria-label="Gulir ke bawah">
        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
    </a>
</section>

{{-- =========================================================
     FITUR
     ========================================================= --}}
<section id="tentang" class="relative bg-white py-20 lg:py-28">
    <div class="mx-auto max-w-7xl px-6 lg:px-10">
        <div class="max-w-2xl">
            <p class="text-sm font-bold uppercase tracking-[.18em] text-emerald-700">Apa yang bisa dilakukan</p>
            <h2 class="mt-3 text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">
                Semua urusan AQHSE dalam satu tempat
            </h2>
            <p class="mt-4 text-lg leading-relaxed text-slate-500">
                Dari temuan di lapangan sampai laporan untuk manajemen, semuanya tercatat rapi dan mudah dilacak.
            </p>
        </div>

        {{-- Sesuaikan daftar fitur ini dengan modul yang ada di aplikasimu --}}
        <div class="mt-14 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([
                ['Pelaporan temuan', 'Catat kondisi atau tindakan tidak aman lengkap dengan foto dan lokasi.', 'M12 9v4m0 4h.01M10.3 3.9 2.4 18a2 2 0 0 0 1.7 3h15.8a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z'],
                ['Inspeksi terjadwal', 'Jalankan checklist inspeksi area dan peralatan secara berkala.', 'M9 5h6M9 3h6a1 1 0 0 1 1 1v1H8V4a1 1 0 0 1 1-1Zm-3 2h12a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Zm3 8 2 2 4-4'],
                ['Tindak lanjut', 'Tetapkan PIC dan tenggat, lalu pantau sampai temuan ditutup.', 'M12 7v5l3 2m6-2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
                ['Dashboard & laporan', 'Lihat tren kinerja AQHSE dan unduh laporan kapan saja.', 'M4 20V10m6 10V4m6 16v-7m4 7H2'],
            ] as $i => [$judul, $desc, $icon])
                <div class="group relative overflow-hidden rounded-3xl border border-slate-200 bg-white p-7 transition duration-300 hover:-translate-y-1 hover:border-emerald-200 hover:shadow-xl hover:shadow-emerald-900/5">
                    <span class="absolute right-6 top-5 text-5xl font-extrabold text-slate-100 transition group-hover:text-emerald-50">0{{ $i + 1 }}</span>
                    <span class="relative flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100 transition group-hover:bg-emerald-600 group-hover:text-white">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                    </span>
                    <h3 class="relative mt-6 text-lg font-bold text-slate-900">{{ $judul }}</h3>
                    <p class="relative mt-2 text-[15px] leading-relaxed text-slate-500">{{ $desc }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- =========================================================
     ALUR KERJA
     ========================================================= --}}
<section class="bg-slate-50 py-20 lg:py-28">
    <div class="mx-auto grid max-w-7xl items-center gap-14 px-6 lg:grid-cols-2 lg:px-10">
        <div class="relative">
            <div class="overflow-hidden rounded-[28px] shadow-2xl shadow-emerald-950/15">
                <img src="{{ asset('images/bg-tambang.jpg') }}" alt="Area operasi" class="aspect-[4/3] w-full object-cover">
            </div>
            <div class="absolute -bottom-6 -right-2 rounded-2xl bg-white p-5 shadow-xl shadow-slate-900/10 ring-1 ring-slate-100 sm:-right-6">
                <div class="flex items-center gap-3">
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-100 text-amber-700">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3 5 6v5c0 4.5 3 8.3 7 10 4-1.7 7-5.5 7-10V6l-7-3Z"/><path stroke-linecap="round" stroke-linejoin="round" d="m9 12 2 2 4-4"/></svg>
                    </span>
                    <div>
                        <p class="text-sm font-bold text-slate-900">Zero Harm</p>
                        <p class="text-xs text-slate-500">Komitmen setiap hari</p>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <p class="text-sm font-bold uppercase tracking-[.18em] text-emerald-700">Cara kerjanya</p>
            <h2 class="mt-3 text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">
                Dari lapangan ke keputusan, tanpa jeda
            </h2>

            <ol class="mt-10 space-y-8">
                @foreach ([
                    ['Laporkan', 'Siapa pun di lapangan bisa langsung melaporkan temuan dari perangkatnya.'],
                    ['Tindak lanjuti', 'Supervisor menetapkan tindakan perbaikan dan penanggung jawabnya.'],
                    ['Evaluasi', 'Manajemen memantau tren dan memastikan perbaikan berjalan efektif.'],
                ] as $i => [$judul, $desc])
                    <li class="relative flex gap-5">
                        @if (! $loop->last)
                            <span class="absolute left-5 top-12 h-[calc(100%-1rem)] w-px bg-emerald-200"></span>
                        @endif
                        <span class="relative flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-700 text-sm font-bold text-white ring-4 ring-emerald-100">
                            {{ $i + 1 }}
                        </span>
                        <div class="pt-1.5">
                            <h3 class="text-lg font-bold text-slate-900">{{ $judul }}</h3>
                            <p class="mt-1 text-[15px] leading-relaxed text-slate-500">{{ $desc }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>
        </div>
    </div>
</section>

{{-- =========================================================
     AJAKAN (CTA)
     ========================================================= --}}
<section class="px-6 py-20 lg:px-10 lg:py-24">
    <div class="relative mx-auto max-w-7xl overflow-hidden rounded-[32px] bg-[#063a33] px-8 py-14 text-white sm:px-14 lg:py-16">
        <img src="{{ asset('images/bg-tambang.jpg') }}" alt="" class="absolute inset-0 h-full w-full object-cover opacity-20 mix-blend-luminosity">
        <div class="absolute inset-0 bg-gradient-to-r from-[#04241f] via-[#063a33]/90 to-[#063a33]/40"></div>
        <div class="absolute inset-x-0 bottom-0 h-1 bg-gradient-to-r from-emerald-500 via-emerald-400 to-amber-400"></div>

        <div class="relative flex flex-col items-start justify-between gap-8 lg:flex-row lg:items-center">
            <div class="max-w-xl">
                <h2 class="text-3xl font-extrabold tracking-tight sm:text-4xl">
                    Keselamatan dimulai dari <span class="text-amber-400">kita semua.</span>
                </h2>
                <p class="mt-3 text-white/70">Masuk sekarang dan ikut menjaga standar AQHSE di area kerja Anda.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                @auth
                    <a href="{{ url('/dashboard') }}" class="rounded-xl bg-white px-7 py-3.5 text-[15px] font-bold text-emerald-800 shadow-lg transition hover:bg-emerald-50">Buka Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="rounded-xl bg-white px-7 py-3.5 text-[15px] font-bold text-emerald-800 shadow-lg transition hover:bg-emerald-50">Masuk</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="rounded-xl border border-white/30 px-7 py-3.5 text-[15px] font-semibold text-white transition hover:bg-white/10">Buat akun</a>
                    @endif
                @endauth
            </div>
        </div>
    </div>
</section>

{{-- =========================================================
     FOOTER
     ========================================================= --}}
<footer class="border-t border-slate-100">
    <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 px-6 py-8 text-sm text-slate-500 sm:flex-row lg:px-10">
        <div class="flex items-center gap-3">
            <img src="{{ asset('images/logo-adaro.png') }}" alt="Adaro" class="h-6 w-auto">
            <span class="hidden h-4 w-px bg-slate-200 sm:block"></span>
            <span class="font-semibold text-slate-700">AQHSE</span>
        </div>
        <p>&copy; {{ date('Y') }} PT Indonesia Bulk Terminal. Hak cipta dilindungi.</p>
    </div>
</footer>

</body>
</html>