<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-emerald-500/10 border border-emerald-500/20 rounded-xl text-emerald-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="font-bold text-xl text-slate-900 tracking-tight">
                        {{ __('Daily Environmental Monitoring') }}
                    </h2>
                    <p class="text-xs text-slate-500 font-medium">Pemantauan harian kualitas air lapangan & penggunaan bahan kimia pengolahan</p>
                </div>
            </div>

            <a href="{{ route('daily-monitoring.create') }}" 
               class="inline-flex items-center gap-2 py-2.5 px-4 rounded-xl font-bold text-sm text-white bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 shadow-sm shadow-emerald-600/20 transition-all duration-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Tambah Data</span>
            </a>
        </div>
    </x-slot>

    <div class="py-8 bg-slate-50/50 min-h-screen">
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

            <!-- Main Content Card -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                
                <!-- Card Header -->
                <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <div>
                        <h3 class="font-bold text-slate-900 text-base">Rekapitulasi Kualitas Air</h3>
                        <p class="text-xs text-slate-500">Hasil pengujian harian pH, debit air, dan konsumsi perlakuan kimia (NaOH)</p>
                    </div>
                    <span class="bg-slate-200/70 text-slate-700 text-xs font-bold px-3 py-1 rounded-full">
                        {{ count($monitorings) }} Record
                    </span>
                </div>

                <!-- Tabel Data -->
                <div class="overflow-x-auto overflow-y-auto max-h-[72vh]">
                    <table class="min-w-full divide-y divide-slate-100 relative text-left">
                        <thead class="bg-slate-50 sticky top-0 z-10 border-b border-slate-200/80">
                            <tr>
                                <th class="px-5 py-3.5 text-xs font-bold text-slate-600 uppercase tracking-wider">Tanggal</th>
                                <th class="px-5 py-3.5 text-xs font-bold text-slate-600 uppercase tracking-wider">Lokasi</th>
                                <th class="px-4 py-3.5 text-center text-xs font-bold text-slate-600 uppercase tracking-wider">pH Level</th>
                                <th class="px-4 py-3.5 text-center text-xs font-bold text-slate-600 uppercase tracking-wider">Debit (m³/jam)</th>
                                <th class="px-4 py-3.5 text-center text-xs font-bold text-slate-600 uppercase tracking-wider">Penggunaan NaOH</th>
                                <th class="px-4 py-3.5 text-center text-xs font-bold text-slate-600 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($monitorings as $data)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <!-- Tanggal -->
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <span class="text-sm font-mono font-bold text-slate-800">
                                            {{ \Carbon\Carbon::parse($data->date)->format('d M Y') }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Lokasi -->
                                <td class="px-5 py-4 whitespace-nowrap text-sm font-semibold text-slate-900">
                                    <div class="flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        <span>{{ $data->location }}</span>
                                    </div>
                                </td>

                                <!-- pH Level Badge -->
                                <td class="px-4 py-4 whitespace-nowrap text-center">
                                    @php
                                        $isPhNormal = ($data->ph_level >= 6 && $data->ph_level <= 9);
                                    @endphp
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-mono font-bold border {{ $isPhNormal ? 'bg-emerald-50 text-emerald-700 border-emerald-200/80' : 'bg-rose-50 text-rose-700 border-rose-200/80 animate-pulse' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $isPhNormal ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                        <span>{{ $data->ph_level }}</span>
                                    </span>
                                </td>

                                <!-- Debit Level -->
                                <td class="px-4 py-4 whitespace-nowrap text-center text-sm font-mono font-medium text-slate-700">
                                    {{ $data->debit_level ? $data->debit_level : '-' }}
                                </td>

                                <!-- NaOH Dosage -->
                                <td class="px-4 py-4 whitespace-nowrap text-center">
                                    @if($data->naoh_amount > 0)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-lg bg-amber-50 text-amber-800 border border-amber-200/80">
                                            <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                            </svg>
                                            <span>{{ $data->naoh_amount }} Karung</span>
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-400 font-medium italic">-</span>
                                    @endif
                                </td>

                                <!-- Action Buttons -->
                                <td class="px-4 py-4 whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <!-- Show -->
                                        <a href="{{ route('daily-monitoring.show', $data->id) }}" 
                                           class="p-2 text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 rounded-lg transition-all" 
                                           title="Lihat Detail">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>
                                        <!-- Edit -->
                                        <a href="{{ route('daily-monitoring.edit', $data->id) }}" 
                                           class="p-2 text-amber-600 hover:text-amber-800 bg-amber-50 hover:bg-amber-100 rounded-lg transition-all" 
                                           title="Edit Data">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </a>
                                        <!-- Delete -->
                                        <form action="{{ route('daily-monitoring.destroy', $data->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus data pemantauan ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="p-2 text-rose-600 hover:text-rose-800 bg-rose-50 hover:bg-rose-100 rounded-lg transition-all" 
                                                    title="Hapus Data">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-slate-400 text-sm">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00-5.86 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                                        </svg>
                                        <p class="font-medium">Belum ada data pemantauan harian.</p>
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