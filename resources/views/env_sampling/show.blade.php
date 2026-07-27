<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('env-sampling.index') }}" 
                   class="p-2 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-xl transition-all duration-200"
                   title="Kembali">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <h2 class="font-bold text-xl text-slate-900 tracking-tight">
                        {{ __('Detail Laporan Sampling: ') }} <span class="text-emerald-600 font-mono">{{ $envSampling->sample_mark }}</span>
                    </h2>
                    <p class="text-xs text-slate-500 font-medium">Laporan lengkap pengujian laboratorium & kualifikasi parameter</p>
                </div>
            </div>
            
            @if($envSampling->attachment_file_path)
                <a href="{{ asset('storage/' . $envSampling->attachment_file_path) }}" target="_blank" 
                   class="inline-flex items-center gap-2 py-2.5 px-4 rounded-xl font-bold text-sm text-white bg-slate-900 hover:bg-slate-800 active:bg-slate-950 shadow-sm transition-all duration-200">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Lihat Dokumen Lab</span>
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-8 bg-slate-50/50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden p-6 sm:p-8 space-y-8">
                
                <!-- KOP INFORMASI SAMPLING -->
                <div class="bg-slate-50/80 rounded-2xl border border-slate-200/80 p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <!-- Kategori Subject -->
                        <div class="flex items-start gap-3">
                            <div class="p-2.5 bg-emerald-500/10 border border-emerald-500/20 rounded-xl text-emerald-600 shrink-0 mt-0.5">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Kategori Subject</p>
                                <p class="text-base font-bold text-slate-900 mt-0.5">{{ $envSampling->subject->name }}</p>
                            </div>
                        </div>

                        <!-- Tanggal & Waktu Sampling -->
                        <div class="flex items-start gap-3">
                            <div class="p-2.5 bg-emerald-500/10 border border-emerald-500/20 rounded-xl text-emerald-600 shrink-0 mt-0.5">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Tanggal & Waktu Sampling</p>
                                <p class="text-base font-mono font-bold text-slate-900 mt-0.5">
                                    {{ \Carbon\Carbon::parse($envSampling->sampling_date)->format('d F Y') }} 
                                    {{ $envSampling->sampling_time ? ' | ' . $envSampling->sampling_time : '' }}
                                </p>
                            </div>
                        </div>

                        <!-- Titik Lokasi -->
                        <div class="flex items-start gap-3">
                            <div class="p-2.5 bg-rose-500/10 border border-rose-500/20 rounded-xl text-rose-600 shrink-0 mt-0.5">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Titik Lokasi (Sample Mark)</p>
                                <p class="text-base font-semibold text-slate-800 mt-0.5">{{ $envSampling->sample_mark }}</p>
                            </div>
                        </div>

                        <!-- Koordinat -->
                        <div class="flex items-start gap-3">
                            <div class="p-2.5 bg-sky-500/10 border border-sky-500/20 rounded-xl text-sky-600 shrink-0 mt-0.5">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Koordinat</p>
                                <p class="text-base font-mono font-semibold text-slate-800 mt-0.5">{{ $envSampling->coordinate ?? '-' }}</p>
                            </div>
                        </div>

                        <!-- Deskripsi / Catatan -->
                        <div class="md:col-span-2 pt-2 border-t border-slate-200/60 flex items-start gap-3">
                            <div class="p-2 bg-slate-200/60 rounded-lg text-slate-600 shrink-0 mt-0.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Deskripsi / Catatan Lapangan</p>
                                <p class="text-sm font-medium text-slate-700 mt-0.5">{{ $envSampling->description ?? 'Tidak ada catatan.' }}</p>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- TABEL HASIL PARAMETER -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Hasil Uji Parameter</h3>
                            <p class="text-xs text-slate-500">Nilai terukur dari pengujian sampel laboratorium</p>
                        </div>
                        <span class="bg-slate-100 text-slate-700 text-xs font-bold px-3 py-1 rounded-full border border-slate-200/80">
                            {{ count($envSampling->results) }} Parameter
                        </span>
                    </div>
                    
                    <div class="overflow-x-auto rounded-2xl border border-slate-200/80 shadow-2xs">
                        <table class="min-w-full divide-y divide-slate-100 text-left">
                            <thead class="bg-slate-50 border-b border-slate-200/80">
                                <tr>
                                    <th class="px-5 py-3.5 text-xs font-bold text-slate-600 uppercase tracking-wider">Parameter</th>
                                    <th class="px-4 py-3.5 text-center text-xs font-bold text-slate-600 uppercase tracking-wider">Hasil (Result)</th>
                                    <th class="px-4 py-3.5 text-center text-xs font-bold text-slate-600 uppercase tracking-wider">Satuan (Unit)</th>
                                    <th class="px-5 py-3.5 text-xs font-bold text-slate-600 uppercase tracking-wider">Metode Uji</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @foreach($envSampling->results as $detail)
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <!-- Parameter Name -->
                                    <td class="px-5 py-4 whitespace-nowrap text-sm font-bold text-slate-900">
                                        {{ $detail->parameter->name }}
                                    </td>

                                    <!-- Result Value -->
                                    <td class="px-4 py-4 whitespace-nowrap text-center">
                                        <span class="inline-block px-3 py-1 text-xs font-mono font-black text-emerald-700 bg-emerald-50 border border-emerald-200/80 rounded-lg">
                                            {{ $detail->result_value }}
                                        </span>
                                    </td>

                                    <!-- Unit -->
                                    <td class="px-4 py-4 whitespace-nowrap text-center text-sm font-mono font-medium text-slate-700">
                                        {{ $detail->parameter->unit ?? '-' }}
                                    </td>

                                    <!-- Method -->
                                    <td class="px-5 py-4 whitespace-nowrap text-sm text-slate-600">
                                        {{ $detail->parameter->method ?? '-' }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>