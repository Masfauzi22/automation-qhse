<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center">
            <!-- Ikon Users di Header -->
            <svg class="w-6 h-6 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
            </svg>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Manajemen Data User') }}
            </h2>
        </div>
    </x-slot>

    <!-- Bungkus konten utama dengan Alpine.js untuk pencarian -->
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
                            <h3 class="text-xl font-bold text-gray-900">Daftar Karyawan / Pengguna</h3>
                            <p class="text-sm text-gray-500 mt-1">Kelola data seluruh pengguna dan hak akses sistem.</p>
                        </div>
                        
                        <!-- Area Search & Button -->
                        <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto items-center">
                            
                            <!-- Search Bar Real-time (x-model="search") -->
                            <div class="relative w-full sm:w-64">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                </div>
                                <input type="text" x-model="search" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 p-2.5 transition-colors" placeholder="Cari nama, email, atau role...">
                            </div>

                            <!-- Tombol Tambah User -->
                            <a href="{{ route('users.create') }}" class="w-full sm:w-auto inline-flex justify-center items-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 shrink-0">
                                <svg class="w-5 h-5 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                Tambah User
                            </a>
                        </div>
                    </div>

                    <!-- Area Tabel dengan Batas Ketinggian (Scroll Internal) -->
                    <!-- max-h-[65vh] mengatur tinggi maksimal tabel menjadi 65% tinggi layar -->
                    <div class="overflow-y-auto max-h-[65vh] rounded-lg border border-gray-200 custom-scrollbar">
                        <table class="min-w-full divide-y divide-gray-200">
                            <!-- sticky top-0 agar header tabel tidak ikut tergulung -->
                            <thead class="bg-gray-50 sticky top-0 z-10 shadow-sm">
                                <tr>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Profil Karyawan</th>
                                    <th scope="col" class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Role Akses</th>
                                    <th scope="col" class="px-6 py-4 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($users as $user)
                                <!-- Logika Search Alpine.js: Mencocokkan Nama, Email, atau Role -->
                                <tr x-show="search === '' || '{{ strtolower($user->name . ' ' . $user->email . ' ' . $user->roles->pluck('name')->join(' ')) }}'.includes(search.toLowerCase())" 
                                    x-transition.opacity.duration.300ms
                                    class="hover:bg-slate-50 transition-colors">
                                    
                                    <!-- Kolom Profil (Avatar, Nama, Email) -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <!-- Avatar Inisial -->
                                            <div class="flex-shrink-0 h-10 w-10 flex items-center justify-center rounded-full bg-indigo-100 text-indigo-700 font-bold border border-indigo-200">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-bold text-gray-900">{{ $user->name }}</div>
                                                <div class="text-xs text-gray-500 mt-0.5">{{ $user->email }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Kolom Role Akses -->
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @foreach($user->roles as $role)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 border border-emerald-200 mr-1 shadow-sm">
                                                {{ strtoupper($role->name) }}
                                            </span>
                                        @endforeach
                                    </td>

                                    <!-- Kolom Aksi -->
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                        <div class="flex justify-center items-center space-x-3">
                                            
                                            <!-- Tombol Edit -->
                                            <a href="{{ route('users.edit', $user->id) }}" class="text-gray-400 hover:text-amber-500 transition-colors" title="Edit Data User">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            </a>
                                            
                                            <!-- Tombol Hapus -->
                                            <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus user ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-gray-400 hover:text-red-600 transition-colors" title="Hapus User">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </form>

                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <!-- Empty State (Tabel Kosong dari Awal) -->
                                <tr>
                                    <td colspan="3" class="px-6 py-12 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                        <h3 class="mt-2 text-sm font-medium text-gray-900">Belum ada user</h3>
                                        <p class="mt-1 text-sm text-gray-500">Mulai dengan menambahkan user baru.</p>
                                    </td>
                                </tr>
                                @endforelse

                                <!-- State Tambahan: Jika Pencarian Tidak Ditemukan -->
                                <tr x-show="search !== '' && !Array.from($el.parentElement.children).some(tr => tr.style.display !== 'none' && tr !== $el)" style="display: none;">
                                    <td colspan="3" class="px-6 py-8 text-center text-sm text-gray-500">
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