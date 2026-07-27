<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-emerald-500/10 border border-emerald-500/20 rounded-xl text-emerald-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="font-bold text-xl text-slate-900 tracking-tight">
                        {{ __('Master Data: Parameter Uji Lab') }}
                    </h2>
                    <p class="text-xs text-slate-500 font-medium">Pengelolaan baku mutu dan metode pengujian HSE Mining</p>
                </div>
            </div>
            <a href="{{ route('sampling-subjects.index') }}" 
               class="inline-flex items-center gap-2 text-sm text-slate-600 hover:text-emerald-600 bg-white hover:bg-slate-50 border border-slate-200 px-4 py-2 rounded-xl font-semibold shadow-xs transition-all duration-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span>Kategori Subject</span>
            </a>
        </div>
    </x-slot>

    <div class="py-8 bg-slate-50/50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            
            <!-- Alert Notifikasi -->
            @if (session('success'))
                <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show" x-transition 
                     class="flex items-center gap-3 bg-emerald-50 border border-emerald-200/80 p-4 rounded-xl shadow-xs text-emerald-800">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm font-semibold">{{ session('success') }}</p>
                </div>
            @endif

            @if (session('error'))
                <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" x-transition 
                     class="flex items-center gap-3 bg-rose-50 border border-rose-200/80 p-4 rounded-xl shadow-xs text-rose-800">
                    <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm font-semibold">{{ session('error') }}</p>
                </div>
            @endif

            @if ($errors->any())
                <div class="flex items-start gap-3 bg-rose-50 border border-rose-200/80 p-4 rounded-xl shadow-xs text-rose-800">
                    <svg class="w-5 h-5 text-rose-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <ul class="text-sm font-medium space-y-1 list-disc pl-4">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                
                <!-- BAGIAN KIRI: Form Tambah Parameter -->
                <div class="lg:col-span-4 lg:sticky lg:top-8">
                    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                        <div class="p-5 bg-gradient-to-r from-slate-900 to-slate-800 text-white">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                <h3 class="font-bold text-base tracking-wide">Tambah Parameter Baru</h3>
                            </div>
                            <p class="text-xs text-slate-300 mt-1">Daftarkan acuan uji lab & mapping kategorinya</p>
                        </div>

                        <form action="{{ route('sampling-parameters.store') }}" method="POST" class="p-6 space-y-5">
                            @csrf
                            
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                                    Nama Parameter <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" name="name" 
                                       class="w-full rounded-xl border-slate-200 bg-slate-50/50 text-slate-900 text-sm focus:bg-white focus:border-emerald-500 focus:ring-emerald-500 transition-all placeholder:text-slate-400" 
                                       placeholder="Cth: pH (in situ), TSS, Fe" required>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Satuan (Unit)</label>
                                    <input type="text" name="unit" 
                                           class="w-full rounded-xl border-slate-200 bg-slate-50/50 text-slate-900 text-sm focus:bg-white focus:border-emerald-500 focus:ring-emerald-500 transition-all placeholder:text-slate-400" 
                                           placeholder="mg/L, °C, -">
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Metode Uji</label>
                                    <input type="text" name="method" 
                                           class="w-full rounded-xl border-slate-200 bg-slate-50/50 text-slate-900 text-sm focus:bg-white focus:border-emerald-500 focus:ring-emerald-500 transition-all placeholder:text-slate-400" 
                                           placeholder="SNI / APHA">
                                </div>
                            </div>

                            <div class="bg-slate-50/80 p-4 rounded-xl border border-slate-200/80">
                                <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider mb-1">
                                    Mapping Kategori <span class="text-rose-500">*</span>
                                </label>
                                <p class="text-xs text-slate-500 mb-3">Centang kategori pengujian yang menggunakan parameter ini:</p>
                                
                                <div class="max-h-48 overflow-y-auto space-y-1.5 pr-1 text-slate-700">
                                    @forelse($subjects as $subject)
                                        <label class="flex items-center gap-3 p-2 rounded-lg hover:bg-white hover:shadow-xs border border-transparent hover:border-slate-200 cursor-pointer transition-all">
                                            <input type="checkbox" name="subjects[]" value="{{ $subject->id }}" 
                                                   class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                            <span class="text-sm font-medium text-slate-700">{{ $subject->name }}</span>
                                        </label>
                                    @empty
                                        <div class="p-3 text-center bg-rose-50 border border-rose-100 rounded-lg">
                                            <p class="text-xs text-rose-600 font-medium">Belum ada Kategori Subject tersedia.</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>

                            <button type="submit" 
                                    class="w-full inline-flex justify-center items-center gap-2 py-3 px-4 rounded-xl font-bold text-sm text-white bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 shadow-sm shadow-emerald-600/20 transition-all duration-200">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>Simpan Parameter</span>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- BAGIAN KANAN: Tabel Data Parameter -->
                <div class="lg:col-span-8">
                    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                        <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                            <div>
                                <h3 class="font-bold text-slate-900 text-base">Daftar Parameter Lab</h3>
                                <p class="text-xs text-slate-500">Total parameter terdaftar dalam sistem pengujian</p>
                            </div>
                            <span class="bg-slate-200/70 text-slate-700 text-xs font-bold px-3 py-1 rounded-full">
                                {{ count($parameters) }} Data
                            </span>
                        </div>
                        
                        <div class="overflow-x-auto overflow-y-auto max-h-[70vh]">
                            <table class="min-w-full divide-y divide-slate-100 relative text-left">
                                <thead class="bg-slate-50 sticky top-0 z-10 border-b border-slate-200/80">
                                    <tr>
                                        <th class="px-5 py-3.5 text-xs font-bold text-slate-600 uppercase tracking-wider">Parameter</th>
                                        <th class="px-4 py-3.5 text-center text-xs font-bold text-slate-600 uppercase tracking-wider">Satuan</th>
                                        <th class="px-5 py-3.5 text-xs font-bold text-slate-600 uppercase tracking-wider">Metode Uji</th>
                                        <th class="px-5 py-3.5 text-xs font-bold text-slate-600 uppercase tracking-wider">Kategori Mapping</th>
                                        <th class="px-4 py-3.5 text-center text-xs font-bold text-slate-600 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 bg-white">
                                    @forelse ($parameters as $parameter)
                                        <tr class="hover:bg-slate-50/80 transition-colors">
                                            <td class="px-5 py-4 text-sm font-bold text-slate-900 whitespace-nowrap">
                                                {{ $parameter->name }}
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-center">
                                                <span class="inline-block px-2.5 py-1 text-xs font-mono font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200/60 rounded-md">
                                                    {{ $parameter->unit ?? '-' }}
                                                </span>
                                            </td>
                                            <td class="px-5 py-4 text-sm text-slate-600 whitespace-nowrap">
                                                {{ $parameter->method ?? '-' }}
                                            </td>
                                            <td class="px-5 py-4 text-sm text-slate-700">
                                                <div class="flex flex-wrap gap-1.5">
                                                    @forelse($parameter->subjects as $paramSubject)
                                                        <span class="px-2.5 py-0.5 inline-flex items-center text-xs font-medium rounded-lg bg-slate-100 text-slate-700 border border-slate-200/80">
                                                            {{ $paramSubject->name }}
                                                        </span>
                                                    @empty
                                                        <span class="text-xs text-rose-500 font-medium italic">Belum di-mapping</span>
                                                    @endforelse
                                                </div>
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-center">
                                                <form action="{{ route('sampling-parameters.destroy', $parameter->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus parameter ini?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="inline-flex items-center gap-1.5 text-rose-600 hover:text-rose-700 bg-rose-50 hover:bg-rose-100/80 border border-rose-200/60 px-3 py-1.5 rounded-lg transition-all font-semibold text-xs">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                        </svg>
                                                        <span>Hapus</span>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-6 py-12 text-center text-slate-400 text-sm">
                                                <div class="flex flex-col items-center justify-center gap-2">
                                                    <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                                                    </svg>
                                                    <p class="font-medium">Belum ada data parameter laboratorium.</p>
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
        </div>
    </div>
</x-app-layout>