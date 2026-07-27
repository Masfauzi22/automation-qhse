<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-emerald-500/10 border border-emerald-500/20 rounded-xl text-emerald-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="font-bold text-xl text-slate-900 tracking-tight">
                        {{ __('Document Control') }}
                    </h2>
                    <p class="text-xs text-slate-500 font-medium">Pusat pengelolaan dan kontrol dokumen internal perusahaan</p>
                </div>
            </div>

            @hasanyrole('superadmin|qhse')
            <a href="{{ route('documents.create') }}" 
               class="inline-flex items-center gap-2 py-2.5 px-4 rounded-xl font-bold text-sm text-white bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 shadow-sm shadow-emerald-600/20 transition-all duration-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Upload Dokumen</span>
            </a>
            @endhasanyrole
        </div>
    </x-slot>

    <!-- Bungkus seluruh konten dengan Alpine.js state untuk pencarian -->
    <div class="py-8 bg-slate-50/50 min-h-screen" x-data="{ search: '' }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            
            <!-- Notifikasi Sukses -->
            @if (session('success'))
                <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" x-transition.duration.500ms 
                     class="flex items-center gap-3 bg-emerald-50 border border-emerald-200/80 p-4 rounded-xl shadow-xs text-emerald-800">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm font-semibold">{{ session('success') }}</p>
                </div>
            @endif

            <!-- Notifikasi Error -->
            @if ($errors->any())
                <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show" x-transition.duration.500ms 
                     class="flex items-start gap-3 bg-rose-50 border border-rose-200/80 p-4 rounded-xl shadow-xs text-rose-800">
                    <svg class="w-5 h-5 text-rose-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="text-sm font-medium space-y-1">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Kontainer Utama -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="p-6 md:p-8 space-y-6">
                    
                    <!-- Header Tabel, Kolom Pencarian & Tombol Tambah -->
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Daftar Dokumen Internal</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Kelola dan pantau seluruh SOP, Instruksi Kerja, dan Form perusahaan.</p>
                        </div>
                        
                        <!-- Area Search -->
                        <div class="w-full md:w-72">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                </div>
                                <input type="text" x-model="search" 
                                       class="bg-slate-50/50 border border-slate-200 text-slate-900 text-sm rounded-xl focus:ring-emerald-500 focus:border-emerald-500 block w-full pl-10 pr-4 py-2.5 transition-all placeholder:text-slate-400" 
                                       placeholder="Cari dokumen...">
                            </div>
                        </div>
                    </div>

                    <!-- Area Tabel dengan Batas Ketinggian (Scroll Internal) -->
                    <div class="overflow-x-auto overflow-y-auto max-h-[65vh] rounded-xl border border-slate-200/80">
                        <table class="min-w-full divide-y divide-slate-100 relative text-left">
                            <thead class="bg-slate-50 sticky top-0 z-10 border-b border-slate-200/80">
                                <tr>
                                    <th scope="col" class="px-6 py-3.5 text-xs font-bold text-slate-600 uppercase tracking-wider">Info Dokumen</th>
                                    <th scope="col" class="px-6 py-3.5 text-center text-xs font-bold text-slate-600 uppercase tracking-wider">Kategori</th>
                                    <th scope="col" class="px-6 py-3.5 text-xs font-bold text-slate-600 uppercase tracking-wider">Diupload Oleh</th>
                                    <th scope="col" class="px-6 py-3.5 text-center text-xs font-bold text-slate-600 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @forelse ($documents as $doc)
                                <tr x-show="search === '' || '{{ strtolower($doc->title . ' ' . ($doc->document_number ?? '') . ' ' . $doc->type) }}'.includes(search.toLowerCase())" 
                                    x-transition.opacity.duration.300ms
                                    class="hover:bg-slate-50/80 transition-colors">
                                    
                                    <!-- Kolom Info Dokumen (Gabungan Judul & Nomor dengan Ikon Dinamis Berdasarkan Tipe) -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            @php
                                                $docType = strtolower($doc->type);
                                            @endphp

                                            @if(str_contains($docType, 'sop'))
                                                <!-- Ikon untuk SOP (Emerald) -->
                                                <div class="w-10 h-10 flex items-center justify-center rounded-xl bg-emerald-50 border border-emerald-200/60 text-emerald-600 shrink-0">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                                </div>
                                            @elseif(str_contains($docType, 'polis') || str_contains($docType, 'policy'))
                                                <!-- Ikon untuk Polis / Policy (Indigo/Dark) -->
                                                <div class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-900 text-emerald-400 shrink-0">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
                                                </div>
                                            @elseif(str_contains($docType, 'instruksi') || str_contains($docType, 'ik'))
                                                <!-- Ikon untuk Instruksi Kerja (Sky Blue) -->
                                                <div class="w-10 h-10 flex items-center justify-center rounded-xl bg-sky-50 border border-sky-200/60 text-sky-600 shrink-0">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                                </div>
                                            @elseif(str_contains($docType, 'form'))
                                                <!-- Ikon untuk Form (Amber) -->
                                                <div class="w-10 h-10 flex items-center justify-center rounded-xl bg-amber-50 border border-amber-200/60 text-amber-600 shrink-0">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                </div>
                                            @else
                                                <!-- Default File Ikon -->
                                                <div class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-100 border border-slate-200/80 text-slate-600 shrink-0">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                </div>
                                            @endif

                                            <div>
                                                <div class="text-sm font-bold text-slate-900">{{ $doc->title }}</div>
                                                <div class="text-xs font-mono text-slate-500 mt-0.5">{{ $doc->document_number ?? 'Tanpa Nomor' }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Kolom Kategori (Badge Dinamis) -->
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @php
                                            $badgeClass = 'bg-slate-100 text-slate-700 border-slate-200';
                                            if(str_contains($docType, 'sop')) {
                                                $badgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-200/80';
                                            } elseif(str_contains($docType, 'polis') || str_contains($docType, 'policy')) {
                                                $badgeClass = 'bg-slate-900 text-emerald-400 border-slate-800';
                                            } elseif(str_contains($docType, 'instruksi') || str_contains($docType, 'ik')) {
                                                $badgeClass = 'bg-sky-50 text-sky-700 border-sky-200/80';
                                            } elseif(str_contains($docType, 'form')) {
                                                $badgeClass = 'bg-amber-50 text-amber-700 border-amber-200/80';
                                            }
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold border {{ $badgeClass }}">
                                            {{ $doc->type }}
                                        </span>
                                    </td>

                                    <!-- Kolom Uploader -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-slate-900">{{ $doc->uploader->name }}</div>
                                        <div class="text-xs font-mono text-slate-500 mt-0.5">{{ $doc->created_at->format('d M Y') }}</div>
                                    </td>

                                    <!-- Kolom Aksi -->
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <div class="flex justify-center items-center gap-1.5">
                                            
                                            <!-- Tombol Lihat/Download -->
                                            <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" 
                                               class="p-2 text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 rounded-lg transition-all" 
                                               title="Lihat Dokumen">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            </a>

                                            @hasanyrole('superadmin|qhse')
                                            <!-- Tombol Edit -->
                                            <a href="{{ route('documents.edit', $doc->id) }}" 
                                               class="p-2 text-amber-600 hover:text-amber-800 bg-amber-50 hover:bg-amber-100 rounded-lg transition-all" 
                                               title="Edit Data">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </a>
                                            
                                            <!-- Tombol Hapus -->
                                            <form action="{{ route('documents.destroy', $doc->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus dokumen ini beserta file-nya?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="p-2 text-rose-600 hover:text-rose-800 bg-rose-50 hover:bg-rose-100 rounded-lg transition-all" 
                                                        title="Hapus Dokumen">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </form>
                                            @endhasanyrole

                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <!-- Empty State -->
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-slate-400 text-sm">
                                        <div class="flex flex-col items-center justify-center gap-2">
                                            <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            <h3 class="font-medium text-slate-700">Belum ada dokumen</h3>
                                            <p class="text-xs text-slate-500">Mulai dengan mengunggah dokumen baru.</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                                
                                <!-- State Tambahan: Jika Pencarian Tidak Ditemukan -->
                                <tr x-show="search !== '' && !Array.from($el.parentElement.children).some(tr => tr.style.display !== 'none' && tr !== $el)" style="display: none;">
                                    <td colspan="4" class="px-6 py-8 text-center text-sm text-slate-500">
                                        Pencarian "<span class="font-bold text-slate-700" x-text="search"></span>" tidak menemukan hasil apapun.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>