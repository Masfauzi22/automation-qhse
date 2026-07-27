<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('waste-domestic.index') }}" 
                   class="p-2 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-xl transition-all duration-200"
                   title="Kembali ke Rekapitulasi">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <h2 class="font-bold text-xl text-slate-900 tracking-tight">
                        Detail BAST & Rekap: {{ $location }}
                    </h2>
                    <p class="text-xs text-slate-500 font-medium">Monitoring rincian pengangkutan & pengesahan dokumen BAST</p>
                </div>
            </div>

            <!-- Badge Status -->
            <div>
                @if($bast->status == 'Menunggu BAST')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200/80 animate-pulse">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                        <span>Menunggu Upload GA</span>
                    </span>
                @elseif($bast->status == 'Menunggu Validasi')
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
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-slate-50/50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            
            @if (session('success'))
                <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show" x-transition.duration.500ms 
                     class="flex items-center gap-3 bg-emerald-50 border border-emerald-200/80 p-4 rounded-xl shadow-xs text-emerald-800">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm font-semibold">{{ session('success') }}</p>
                </div>
            @endif

            <!-- PANEL AKSI BAST (Berubah sesuai Role & Status) -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="p-6 bg-slate-50/50 flex flex-col md:flex-row justify-between items-stretch md:items-center gap-6">
                    
                    <div class="space-y-2">
                        <div class="flex items-center gap-2.5">
                            <span class="p-2 bg-slate-200/60 rounded-xl text-slate-700">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </span>
                            <div>
                                <h3 class="text-base font-bold text-slate-900 uppercase tracking-wide">Dokumen BAST Bulanan</h3>
                                <p class="text-xs text-slate-500">Periode: <span class="font-bold text-slate-800">{{ \Carbon\Carbon::parse($monthYear . '-01')->translatedFormat('F Y') }}</span></p>
                            </div>
                        </div>
                        
                        @if($bast->bast_file_path)
                            <div class="pt-2">
                                <a href="{{ asset('storage/' . $bast->bast_file_path) }}" target="_blank" 
                                   class="inline-flex items-center gap-2 px-3.5 py-2 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200/80 rounded-xl font-bold text-xs text-emerald-800 transition-all shadow-2xs">
                                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <span>Lihat File BAST PDF</span>
                                </a>
                            </div>
                        @endif
                    </div>

                    <div class="w-full md:w-1/2 bg-white p-5 rounded-xl border border-slate-200/80 shadow-xs">
                        
                        <!-- JIKA STATUS: MENUNGGU BAST -->
                        @if($bast->status == 'Menunggu BAST')
                            @role('ga|superadmin')
                                <form action="{{ route('waste-domestic.upload-bast') }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                                    @csrf
                                    <input type="hidden" name="bast_id" value="{{ $bast->id }}">
                                    
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Upload Dokumen BAST (PDF)</label>
                                    <input type="file" name="bast_file" accept=".pdf" required 
                                           class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 border border-slate-200 rounded-xl p-1 bg-slate-50/50">
                                    @error('bast_file') <p class="text-xs text-rose-500 font-medium mb-2">{{ $message }}</p> @enderror
                                    
                                    <button type="submit" class="w-full inline-flex justify-center items-center gap-2 py-2.5 px-4 rounded-xl font-bold text-xs uppercase tracking-wider text-white bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 shadow-xs transition-all">
                                        Upload & Ajukan
                                    </button>
                                </form>
                            @else
                                <div class="p-3 text-center bg-slate-50 border border-slate-200/60 rounded-lg">
                                    <p class="text-xs font-medium text-slate-500">Menunggu Tim GA mengunggah dokumen BAST.</p>
                                </div>
                            @endrole

                        <!-- JIKA STATUS: MENUNGGU VALIDASI -->
                        @elseif($bast->status == 'Menunggu Validasi')
                            @role('qhse|superadmin')
                                <form action="{{ route('waste-domestic.validate-bast') }}" method="POST" class="space-y-3">
                                    @csrf
                                    <input type="hidden" name="bast_id" value="{{ $bast->id }}">
                                    <p class="text-xs text-slate-600 font-medium">Mohon periksa dokumen BAST di samping. Jika sudah sesuai, silakan validasi.</p>
                                    <button type="submit" class="w-full inline-flex justify-center items-center gap-2 py-2.5 px-4 rounded-xl font-bold text-xs uppercase tracking-wider text-white bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 shadow-xs transition-all" onclick="return confirm('Yakin ingin memvalidasi dokumen BAST ini?');">
                                        Validasi Dokumen Ini
                                    </button>
                                </form>
                            @else
                                <div class="p-3 text-center bg-amber-50 border border-amber-200/60 rounded-lg">
                                    <p class="text-xs font-medium text-amber-700">Dokumen sedang direview oleh Tim QHSE.</p>
                                </div>
                            @endrole

                        <!-- JIKA STATUS: TERVALIDASI -->
                        @else
                            <div class="text-center py-2 space-y-1">
                                <div class="w-10 h-10 bg-emerald-100 rounded-full flex items-center justify-center mx-auto text-emerald-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <p class="text-sm font-bold text-slate-900">Proses Selesai</p>
                                <p class="text-xs text-slate-500">BAST telah disahkan dan diarsipkan.</p>
                            </div>
                        @endif

                    </div>
                </div>
            </div>

            <!-- PANEL RINGKASAN VOLUME -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Organik</span>
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    </div>
                    <p class="text-3xl font-mono font-black text-emerald-700">{{ $totalOrganic }}</p>
                </div>
                
                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Anorganik</span>
                        <span class="w-2.5 h-2.5 rounded-full bg-sky-500"></span>
                    </div>
                    <p class="text-3xl font-mono font-black text-sky-700">{{ $totalAnorganic }}</p>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Residu</span>
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                    </div>
                    <p class="text-3xl font-mono font-black text-amber-700">{{ $totalResidue }}</p>
                </div>

                <div class="bg-gradient-to-br from-slate-900 to-slate-800 p-5 rounded-2xl border border-slate-800 shadow-xs text-white">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-300">Grand Total</span>
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                    </div>
                    <p class="text-3xl font-mono font-black text-white">{{ $totalOrganic + $totalAnorganic + $totalResidue }}</p>
                </div>
            </div>

            <!-- TABEL RINCIAN HARIAN -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-slate-900 text-base">Rincian Data Pengangkutan Harian</h3>
                        <p class="text-xs text-slate-500">Catatan volume limbah berdasarkan tanggal pengumpulan</p>
                    </div>
                    <span class="bg-slate-200/70 text-slate-700 text-xs font-bold px-3 py-1 rounded-full">
                        {{ count($dailyData) }} Record
                    </span>
                </div>

                <div class="overflow-x-auto overflow-y-auto max-h-[60vh]">
                    <table class="min-w-full divide-y divide-slate-100 relative text-left">
                        <thead class="bg-slate-50 sticky top-0 z-10 border-b border-slate-200/80">
                            <tr>
                                <th class="px-5 py-3.5 text-xs font-bold text-slate-600 uppercase tracking-wider">Tanggal</th>
                                <th class="px-4 py-3.5 text-center text-xs font-bold text-slate-600 uppercase tracking-wider">Organik</th>
                                <th class="px-4 py-3.5 text-center text-xs font-bold text-slate-600 uppercase tracking-wider">Anorganik</th>
                                <th class="px-4 py-3.5 text-center text-xs font-bold text-slate-600 uppercase tracking-wider">Residu</th>
                                <th class="px-5 py-3.5 text-xs font-bold text-slate-600 uppercase tracking-wider">Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($dailyData as $data)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <span class="text-sm font-mono font-bold text-slate-800">
                                            {{ \Carbon\Carbon::parse($data->collection_date)->format('d M Y') }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-center text-sm font-mono font-bold text-emerald-700">
                                    {{ $data->organic_volume }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-center text-sm font-mono font-bold text-sky-700">
                                    {{ $data->anorganic_volume }}
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-center text-sm font-mono font-bold text-amber-700">
                                    {{ $data->residue_volume }}
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-600">
                                    {{ $data->notes ?? '-' }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-slate-400 text-sm">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <p class="font-medium">Belum ada data harian.</p>
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