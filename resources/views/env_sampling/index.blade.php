<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-emerald-500/10 border border-emerald-500/20 rounded-xl text-emerald-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 002 2h1.5a2.5 2.5 0 002.5-2.5V8.283a2.5 2.5 0 00-.832-1.854l-1.026-.921a2.5 2.5 0 00-1.672-.638H12a2 2 0 01-2-2V5c0-.828-.562-1.557-1.365-1.758L8 3.935z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="font-bold text-xl text-slate-900 tracking-tight">
                        {{ __('Data Sampling Lingkungan') }}
                    </h2>
                    <p class="text-xs text-slate-500 font-medium">Riwayat dan dokumentasi pemantauan baku mutu lingkungan pertambangan</p>
                </div>
            </div>
            
            <a href="{{ route('env-sampling.create') }}" 
               class="inline-flex items-center gap-2 py-2.5 px-4 rounded-xl font-bold text-sm text-white bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 shadow-sm shadow-emerald-600/20 transition-all duration-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Input Hasil Baru</span>
            </a>
        </div>
    </x-slot>

    <div class="py-8 bg-slate-50/50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Alert Sukses -->
            @if (session('success'))
                <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show" x-transition 
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
                        <h3 class="font-bold text-slate-900 text-base">Riwayat Pengujian Lab</h3>
                        <p class="text-xs text-slate-500">Daftar sampel hasil pemantauan fisik & laboratorium</p>
                    </div>
                    <span class="bg-slate-200/70 text-slate-700 text-xs font-bold px-3 py-1 rounded-full">
                        {{ count($samplings) }} Record
                    </span>
                </div>

                <!-- Tabel Data -->
                <div class="overflow-x-auto overflow-y-auto max-h-[72vh]">
                    <table class="min-w-full divide-y divide-slate-100 relative text-left">
                        <thead class="bg-slate-50 sticky top-0 z-10 border-b border-slate-200/80">
                            <tr>
                                <th class="px-5 py-3.5 text-xs font-bold text-slate-600 uppercase tracking-wider">Tanggal</th>
                                <th class="px-5 py-3.5 text-xs font-bold text-slate-600 uppercase tracking-wider">Kategori Subject</th>
                                <th class="px-5 py-3.5 text-xs font-bold text-slate-600 uppercase tracking-wider">Titik Lokasi (Sample Mark)</th>
                                <th class="px-5 py-3.5 text-xs font-bold text-slate-600 uppercase tracking-wider">Penginput</th>
                                <th class="px-4 py-3.5 text-center text-xs font-bold text-slate-600 uppercase tracking-wider">Lampiran</th>
                                <th class="px-4 py-3.5 text-center text-xs font-bold text-slate-600 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($samplings as $data)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <!-- Tanggal -->
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <span class="text-sm font-mono font-bold text-slate-800">
                                            {{ \Carbon\Carbon::parse($data->sampling_date)->format('d M Y') }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Kategori Subject -->
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-lg bg-emerald-50 text-emerald-800 border border-emerald-200/80">
                                        {{ $data->subject->name }}
                                    </span>
                                </td>

                                <!-- Titik Lokasi / Sample Mark -->
                                <td class="px-5 py-4 whitespace-nowrap text-sm font-semibold text-slate-900">
                                    <div class="flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        <span>{{ $data->sample_mark }}</span>
                                    </div>
                                </td>

                                <!-- Penginput -->
                                <td class="px-5 py-4 whitespace-nowrap text-sm text-slate-600">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center text-[10px] font-bold text-slate-600 uppercase">
                                            {{ substr($data->user->name ?? 'U', 0, 1) }}
                                        </div>
                                        <span>{{ $data->user->name }}</span>
                                    </div>
                                </td>

                                <!-- Lampiran -->
                                <td class="px-4 py-4 whitespace-nowrap text-center">
                                    @if($data->attachment_file_path)
                                        <a href="{{ asset('storage/' . $data->attachment_file_path) }}" target="_blank" 
                                           class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200/60 px-2.5 py-1 rounded-lg transition-all">
                                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                            </svg>
                                            <span>Dokumen</span>
                                        </a>
                                    @else
                                        <span class="text-xs text-slate-400 font-medium italic">-</span>
                                    @endif
                                </td>

                                <!-- Aksi -->
                                <td class="px-4 py-4 whitespace-nowrap text-center">
                                    <a href="{{ route('env-sampling.show', $data->id) }}" 
                                       class="inline-flex items-center gap-1.5 text-slate-700 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 border border-slate-200/80 px-3 py-1.5 rounded-lg transition-all font-semibold text-xs">
                                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        <span>Detail</span>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-slate-400 text-sm">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <p class="font-medium">Belum ada riwayat input pengujian laboratorium.</p>
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