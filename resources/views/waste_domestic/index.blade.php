<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-emerald-500/10 border border-emerald-500/20 rounded-xl text-emerald-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </div>
                <div>
                    <h2 class="font-bold text-xl text-slate-900 tracking-tight">
                        {{ __('Rekapitulasi & BAST Sampah Domestik') }}
                    </h2>
                    <p class="text-xs text-slate-500 font-medium">Monitoring pengelolaan limbah domestik & alur validasi Berita Acara Serah Terima (BAST)</p>
                </div>
            </div>

            <!-- Tombol ini hanya muncul untuk Role QHSE -->
            @role('qhse')
            <a href="{{ route('waste-domestic.create') }}" 
               class="inline-flex items-center gap-2 py-2.5 px-4 rounded-xl font-bold text-sm text-white bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 shadow-sm shadow-emerald-600/20 transition-all duration-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Catat Data Harian</span>
            </a>
            @endrole
        </div>
    </x-slot>

    <div class="py-8 bg-slate-50/50 min-h-screen" x-data="{ search: '' }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            
            <!-- Alert Notifikasi Sukses -->
            @if (session('success'))
                <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show" x-transition.duration.500ms 
                     class="flex items-center gap-3 bg-emerald-50 border border-emerald-200/80 p-4 rounded-xl shadow-xs text-emerald-800">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm font-semibold">{{ session('success') }}</p>
                </div>
            @endif

            <!-- Card Utama & Tabel Data -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                
                <!-- Card Header -->
                <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <div>
                        <h3 class="font-bold text-slate-900 text-base">Laporan Bulanan per Lokasi</h3>
                        <p class="text-xs text-slate-500">Tim GA wajib mengunggah BAST pada setiap akhir bulan untuk divalidasi oleh QHSE.</p>
                    </div>
                    <span class="bg-slate-200/70 text-slate-700 text-xs font-bold px-3 py-1 rounded-full">
                        {{ count($monthlyRecaps) }} Rekap
                    </span>
                </div>

                <!-- Tabel Data -->
                <div class="overflow-x-auto overflow-y-auto max-h-[72vh]">
                    <table class="min-w-full divide-y divide-slate-100 relative text-left">
                        <thead class="bg-slate-50 sticky top-0 z-10 border-b border-slate-200/80">
                            <tr>
                                <th class="px-5 py-3.5 text-xs font-bold text-slate-600 uppercase tracking-wider">Bulan Laporan</th>
                                <th class="px-5 py-3.5 text-xs font-bold text-slate-600 uppercase tracking-wider">Lokasi Area</th>
                                <th class="px-4 py-3.5 text-center text-xs font-bold text-slate-600 uppercase tracking-wider">Total Vol (Ton)</th>
                                <th class="px-4 py-3.5 text-center text-xs font-bold text-slate-600 uppercase tracking-wider">Status Dokumen BAST</th>
                                <th class="px-4 py-3.5 text-center text-xs font-bold text-slate-600 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($monthlyRecaps as $data)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <!-- Bulan Laporan -->
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <span class="text-sm font-mono font-bold text-slate-800">
                                            {{ \Carbon\Carbon::parse($data->month_year . '-01')->translatedFormat('F Y') }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Lokasi Area -->
                                <td class="px-5 py-4 whitespace-nowrap text-sm font-semibold text-slate-900">
                                    <div class="flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        <span>{{ $data->location }}</span>
                                    </div>
                                </td>

                                <!-- Total Volume (Ton) -->
                                <td class="px-4 py-4 whitespace-nowrap text-center">
                                    <span class="inline-block px-3 py-1 text-xs font-mono font-black text-emerald-700 bg-emerald-50 border border-emerald-200/80 rounded-lg">
                                        {{ number_format(($data->total_organic + $data->total_anorganic + $data->total_residue) / 1000, 2, ',', '.') }} Ton
                                    </span>
                                </td>

                                <!-- Status Dokumen BAST -->
                                <td class="px-4 py-4 whitespace-nowrap text-center">
                                    @if($data->bast_status == 'Menunggu BAST')
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200/80 animate-pulse">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            <span>Menunggu Upload GA</span>
                                        </span>
                                    @elseif($data->bast_status == 'Menunggu Validasi')
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-200/80">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            <span>Menunggu Validasi QHSE</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200/80">
                                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            <span>Tervalidasi</span>
                                        </span>
                                    @endif
                                </td>

                                <!-- Aksi -->
                                <td class="px-4 py-4 whitespace-nowrap text-center">
                                    <a href="{{ url('/waste-domestic/recap?month='.$data->month_year.'&location='.$data->location) }}" 
                                       class="inline-flex items-center gap-1.5 text-slate-700 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 border border-slate-200/80 px-3 py-1.5 rounded-lg transition-all font-semibold text-xs">
                                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <span>Lihat & Proses BAST</span>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-slate-400 text-sm">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        <p class="font-medium">Belum ada data pengangkutan sampah yang dicatat.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>