<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center">
            <!-- Ikon Keamanan/Shield di Header -->
            <svg class="w-6 h-6 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
            </svg>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Manajemen Data Role') }}
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

            <!-- Grid Layout: Kiri Form (1/3), Kanan Tabel (2/3) -->
            <div class="flex flex-col md:flex-row gap-6">
                
                <!-- Kolom Kiri: Form Tambah Role -->
                <div class="w-full md:w-1/3 shrink-0">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100 h-fit">
                        <div class="p-6">
                            <h3 class="text-lg font-bold text-gray-900 mb-1">Tambah Role Baru</h3>
                            <p class="text-xs text-gray-500 mb-5">Buat kategori hak akses baru untuk sistem.</p>

                            <form action="{{ route('roles.store') }}" method="POST">
                                @csrf
                                <div class="mb-5">
                                    <label class="block text-gray-700 text-sm font-bold mb-2">Nama Role <span class="text-red-500">*</span></label>
                                    <input type="text" name="name" required placeholder="cth: auditor" class="shadow-sm appearance-none border border-gray-300 rounded-lg w-full py-2.5 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                                </div>
                                <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <svg class="w-5 h-5 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                    Simpan Role
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Kolom Kanan: Tabel Daftar Role -->
                <div class="w-full md:w-2/3">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100 h-full flex flex-col">
                        <div class="p-6 md:p-8 flex-1 flex flex-col">
                            
                            <!-- Header Tabel & Kolom Pencarian -->
                            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                                <div>
                                    <h3 class="text-xl font-bold text-gray-900">Daftar Role Akses</h3>
                                    <p class="text-sm text-gray-500 mt-1">Daftar seluruh hak akses yang tersedia.</p>
                                </div>
                                
                                <!-- Search Bar Real-time (x-model="search") -->
                                <div class="relative w-full sm:w-56 shrink-0">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                    </div>
                                    <input type="text" x-model="search" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 p-2.5 transition-colors" placeholder="Cari nama role...">
                                </div>
                            </div>

                            <!-- Area Tabel dengan Batas Ketinggian (Scroll Internal) -->
                            <div class="overflow-y-auto max-h-[50vh] rounded-lg border border-gray-200 custom-scrollbar flex-1">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <!-- Sticky Header -->
                                    <thead class="bg-gray-50 sticky top-0 z-10 shadow-sm">
                                        <tr>
                                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-16">No</th>
                                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama Role</th>
                                            <th scope="col" class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider w-32">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach ($roles as $index => $role)
                                        <!-- Logika Search Alpine.js -->
                                        <tr x-show="search === '' || '{{ strtolower($role->name) }}'.includes(search.toLowerCase())" 
                                            x-transition.opacity.duration.300ms
                                            class="hover:bg-slate-50 transition-colors">
                                            
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $index + 1 }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 border border-emerald-200 shadow-sm">
                                                        {{ strtoupper($role->name) }}
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                                @if($role->name !== 'superadmin')
                                                    <!-- Tombol Hapus -->
                                                    <form action="{{ route('roles.destroy', $role->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus role ini?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-gray-400 hover:text-red-600 transition-colors" title="Hapus Role">
                                                            <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                        </button>
                                                    </form>
                                                @else
                                                    <!-- Status Locked untuk Superadmin -->
                                                    <span class="inline-flex items-center text-gray-400 text-xs italic" title="Role utama tidak dapat dihapus">
                                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                                        Locked
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach

                                        <!-- State Tambahan: Jika Pencarian Tidak Ditemukan -->
                                        <tr x-show="search !== '' && !Array.from($el.parentElement.children).some(tr => tr.style.display !== 'none' && tr !== $el)" style="display: none;">
                                            <td colspan="3" class="px-6 py-8 text-center text-sm text-gray-500">
                                                Role "<span class="font-bold text-gray-700" x-text="search"></span>" tidak ditemukan.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>