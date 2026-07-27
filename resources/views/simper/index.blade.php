<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center">
            <svg class="w-6 h-6 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path>
            </svg>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Data SIMPER (Surat Izin Mengemudi Perusahaan)') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12" x-data="{ search: '' }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if (session('success'))
                <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show" x-transition.duration.500ms class="flex items-center bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded-r-lg shadow-sm">
                    <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <p class="text-sm text-green-700 font-medium">{{ session('success') }}</p>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100">
                <div class="p-6 md:p-8">
                    
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                        <div class="w-full md:w-1/2">
                            <h3 class="text-xl font-bold text-gray-900">Database SIMPER</h3>
                            <p class="text-sm text-gray-500 mt-1">Kelola data pemegang SIMPER, kontraktor, dan masa berlakunya.</p>
                        </div>
                        
                        <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto items-center">
                            <div class="relative w-full sm:w-72">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                </div>
                                <input type="text" x-model="search" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 p-2.5 transition-colors" placeholder="Cari nama, NRP, atau perusahaan...">
                            </div>

                            <a href="{{ route('simper.create') }}" class="w-full sm:w-auto inline-flex justify-center items-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 shrink-0">
                                <svg class="w-5 h-5 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                @hasanyrole('superadmin|qhse')
                                    Tambah SIMPER
                                @else
                                    Ajukan SIMPER Baru
                                @endhasanyrole
                            </a>
                        </div>
                    </div>

                    <div class="overflow-y-auto max-h-[65vh] rounded-lg border border-gray-200 custom-scrollbar">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50 sticky top-0 z-10 shadow-sm">
                                <tr>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Profil Pegawai</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Info SIMPER</th>
                                    <th scope="col" class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Status & Berlaku</th>
                                    <th scope="col" class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($simpers as $simper)
                                <tr x-show="search === '' || '{{ strtolower($simper->name . ' ' . $simper->nrp . ' ' . $simper->company . ' ' . ($simper->simper_number ?? '')) }}'.includes(search.toLowerCase())" 
                                    x-transition.opacity.duration.300ms
                                    class="hover:bg-slate-50 transition-colors {{ $simper->status == 'Pending' ? 'bg-orange-50/30' : '' }}">
                                    
                                    <!-- Kolom Profil -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            @if($simper->photo_path)
                                                <img class="h-12 w-10 rounded-lg object-cover border border-gray-200 shadow-sm" src="{{ asset('storage/' . $simper->photo_path) }}" alt="">
                                            @else
                                                <div class="flex-shrink-0 h-12 w-10 flex items-center justify-center rounded-lg bg-indigo-100 text-indigo-700 font-bold border border-indigo-200 shadow-sm">
                                                    {{ strtoupper(substr($simper->name, 0, 1)) }}
                                                </div>
                                            @endif
                                            
                                            <div class="ml-4">
                                                <div class="text-sm font-bold text-gray-900">{{ $simper->name }}</div>
                                                <div class="text-xs text-gray-500 mt-0.5">NRP: {{ $simper->nrp ?? '-' }}</div>
                                                <div class="text-xs font-medium text-indigo-600 mt-1">{{ $simper->company }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Kolom Info SIMPER -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($simper->simper_number)
                                            <div class="text-sm font-medium text-gray-900">{{ $simper->simper_number }}</div>
                                        @else
                                            <div class="text-xs font-medium text-orange-600 bg-orange-100 px-2 py-0.5 rounded inline-block">Belum Diterbitkan</div>
                                        @endif
                                        <div class="text-xs text-gray-500 mt-1">{{ $simper->department }} - {{ $simper->position }}</div>
                                    </td>

                                    <!-- Kolom Status & Berlaku (Termasuk Status PIC) -->
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <div class="flex flex-col items-center gap-1.5">
                                            @if($simper->status == 'Active')
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200 shadow-sm">Aktif</span>
                                            @elseif($simper->status == 'Pending')
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800 border border-orange-200 shadow-sm animate-pulse">Menunggu Proses</span>
                                            @elseif($simper->status == 'Expired')
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 border border-red-200 shadow-sm">Expired</span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 border border-gray-200 shadow-sm">Dicabut</span>
                                            @endif

                                            <div class="text-[11px] font-medium">
                                                @if($simper->pic_status == 'Approved')
                                                    <span class="text-green-600 bg-green-50 px-2 py-0.5 rounded border border-green-200">✓ PIC: Disetujui</span>
                                                @elseif($simper->pic_status == 'Rejected')
                                                    <span class="text-red-600 bg-red-50 px-2 py-0.5 rounded border border-red-200">✕ PIC: Ditolak</span>
                                                @else
                                                    <span class="text-amber-600 bg-amber-50 px-2 py-0.5 rounded border border-amber-200">⏳ PIC: Menunggu ACC</span>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        @if($simper->valid_until)
                                            <div class="text-xs text-gray-500 mt-2 font-medium">s/d {{ \Carbon\Carbon::parse($simper->valid_until)->format('d M Y') }}</div>
                                        @else
                                            <div class="text-xs text-gray-400 mt-2 italic">Masa berlaku belum diset</div>
                                        @endif
                                    </td>

                                    <!-- Kolom Aksi -->
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                        <div class="flex justify-center items-center space-x-3">
                                            <a href="{{ route('simper.show', $simper->id) }}" class="text-gray-400 hover:text-indigo-600 transition-colors" title="Lihat Detail">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                            </a>

                                            @hasanyrole('superadmin|qhse')
                                                <a href="{{ route('simper.edit', $simper->id) }}" class="{{ $simper->status == 'Pending' ? 'text-orange-500 hover:text-orange-700' : 'text-gray-400 hover:text-amber-500' }} transition-colors" title="Edit Data">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                                </a>
                                                
                                                <form action="{{ route('simper.destroy', $simper->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus data ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-gray-400 hover:text-red-600 transition-colors" title="Hapus Data">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    </button>
                                                </form>
                                            @endhasanyrole
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-gray-500 text-sm">Belum ada data SIMPER.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>