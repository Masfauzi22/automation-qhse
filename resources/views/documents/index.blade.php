<!-- x-data="{ search: '' }" di bawah ini adalah inisiasi mesin pencari Alpine.js -->
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center">
            <svg class="w-6 h-6 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
            </svg>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Document Control') }}
            </h2>
        </div>
    </x-slot>

    <!-- Bungkus seluruh konten dengan Alpine.js state untuk pencarian -->
    <div class="py-12" x-data="{ search: '' }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Notifikasi Sukses -->
            @if (session('success'))
                <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" x-transition.duration.500ms class="flex items-center bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded-r-lg shadow-sm">
                    <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <p class="text-sm text-green-700 font-medium">{{ session('success') }}</p>
                </div>
            @endif

            <!-- Notifikasi Error -->
            @if ($errors->any())
                <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" x-transition.duration.500ms class="flex items-start bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-r-lg shadow-sm">
                    <svg class="w-5 h-5 text-red-500 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <div class="text-sm text-red-700 font-medium">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Kontainer Utama -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100">
                <div class="p-6 md:p-8">
                    
                    <!-- Header Tabel, Kolom Pencarian & Tombol Tambah -->
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                        <div class="w-full md:w-1/2">
                            <h3 class="text-xl font-bold text-gray-900">Daftar Dokumen Internal</h3>
                            <p class="text-sm text-gray-500 mt-1">Kelola dan pantau seluruh SOP, Instruksi Kerja, dan Form perusahaan.</p>
                        </div>
                        
                        <!-- Area Search & Button -->
                        <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto items-center">
                            
                            <!-- Search Bar Real-time (x-model="search") -->
                            <div class="relative w-full sm:w-64">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                </div>
                                <input type="text" x-model="search" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 p-2.5 transition-colors" placeholder="Cari dokumen...">
                            </div>

                            @hasanyrole('superadmin|qhse')
                            <!-- Tombol Upload -->
                            <a href="{{ route('documents.create') }}" class="w-full sm:w-auto inline-flex justify-center items-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 shrink-0">
                                <svg class="w-5 h-5 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                Upload Dokumen
                            </a>
                            @endhasanyrole
                        </div>
                    </div>

                    <!-- Area Tabel dengan Batas Ketinggian (Scroll Internal) -->
                    <!-- max-h-[65vh] mengatur tinggi maksimal tabel menjadi 65% tinggi layar -->
                    <div class="overflow-y-auto max-h-[65vh] rounded-lg border border-gray-200 custom-scrollbar">
                        <table class="min-w-full divide-y divide-gray-200">
                            <!-- sticky top-0 agar header tabel tidak ikut tergulung -->
                            <thead class="bg-gray-50 sticky top-0 z-10 shadow-sm">
                                <tr>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Info Dokumen</th>
                                    <th scope="col" class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Kategori</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Diupload Oleh</th>
                                    <th scope="col" class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($documents as $doc)
                                <!-- Logika Search Alpine.js: Tampilkan baris HANYA JIKA pencarian cocok dengan Judul, Nomor, atau Tipe Dokumen -->
                                <tr x-show="search === '' || '{{ strtolower($doc->title . ' ' . ($doc->document_number ?? '') . ' ' . $doc->type) }}'.includes(search.toLowerCase())" 
                                    x-transition.opacity.duration.300ms
                                    class="hover:bg-slate-50 transition-colors">
                                    
                                    <!-- Kolom Info Dokumen (Gabungan Judul & Nomor) -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 flex items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-bold text-gray-900">{{ $doc->title }}</div>
                                                <div class="text-xs text-gray-500 mt-0.5">{{ $doc->document_number ?? 'Tanpa Nomor' }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Kolom Kategori (Badge) -->
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 border border-blue-200">
                                            {{ $doc->type }}
                                        </span>
                                    </td>

                                    <!-- Kolom Uploader -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $doc->uploader->name }}</div>
                                        <div class="text-xs text-gray-500 mt-0.5">{{ $doc->created_at->format('d M Y') }}</div>
                                    </td>

                                    <!-- Kolom Aksi -->
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                        <div class="flex justify-center items-center space-x-3">
                                            
                                            <!-- Tombol Lihat/Download -->
                                            <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank" class="text-gray-400 hover:text-indigo-600 transition-colors" title="Lihat Dokumen">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                            </a>

                                            @hasanyrole('superadmin|qhse')
                                            <!-- Tombol Edit -->
                                            <a href="{{ route('documents.edit', $doc->id) }}" class="text-gray-400 hover:text-amber-500 transition-colors" title="Edit Data">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            </a>
                                            
                                            <!-- Tombol Hapus -->
                                            <form action="{{ route('documents.destroy', $doc->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus dokumen ini beserta file-nya?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-gray-400 hover:text-red-600 transition-colors" title="Hapus Dokumen">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </form>
                                            @endhasanyrole

                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <!-- Empty State -->
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada dokumen</h3>
                                        <p class="mt-1 text-sm text-gray-500">Mulai dengan mengunggah dokumen baru.</p>
                                    </td>
                                </tr>
                                @endforelse
                                
                                <!-- State Tambahan: Jika Pencarian Tidak Ditemukan -->
                                <tr x-show="search !== '' && !Array.from($el.parentElement.children).some(tr => tr.style.display !== 'none' && tr !== $el)" style="display: none;">
                                    <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500">
                                        Pencarian "<span class="font-bold text-gray-700" x-text="search"></span>" tidak menemukan hasil apapun.
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