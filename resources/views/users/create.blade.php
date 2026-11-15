<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tambah User Baru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <!-- TAMPILAN PESAN ERROR -->
                    @if ($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                            <strong>Gagal Menyimpan Data!</strong>
                            <ul class="mt-2 list-disc list-inside text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('users.store') }}" method="POST">
                        @csrf 

                        <!-- Input Nama -->
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Nama Lengkap</label>
                            <input type="text" name="name" value="{{ old('name') }}" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>

                        <!-- Input Email -->
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Email Akun</label>
                            <input type="email" name="email" value="{{ old('email') }}" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>

                        <!-- Input ID Karyawan (BARU) -->
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">ID Karyawan / NIK</label>
                            <input type="text" name="employee_number" value="{{ old('employee_number') }}" maxlength="50" placeholder="Contoh: APS-00123" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            <p class="text-xs text-gray-500 mt-1">Opsional. Harus unik, tidak boleh sama dengan user lain.</p>
                        </div>

                        <!-- Input Perusahaan (BARU) -->
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Perusahaan</label>
                            <select name="company_id" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                <option value="">-- Pilih Perusahaan --</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                        {{ $company->name }}{{ $company->is_contractor ? ' (Kontraktor)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Input Departemen (Dropdown) -->
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Departemen</label>
                            <select name="department_id" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                <option value="">-- Tidak ada spesifik / Universal --</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                        {{ $dept->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Input Jabatan (BARU: alur Permit to Work) -->
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Jabatan</label>
                            <select name="position" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                <option value="">-- Pilih Jabatan --</option>
                                @foreach($positions as $key => $position)
                                    <option value="{{ $key }}" {{ old('position') === $key ? 'selected' : '' }}>
                                        {{ $position['label'] }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Permit to Work: Foreman &amp; Supervisor bisa mengajukan; Supervisor &amp; Section Head memeriksa permit bawahannya di departemen yang sama.</p>
                        </div>

                        <!-- Input Password -->
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Password (Min. 8 Karakter)</label>
                            <input type="password" name="password" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>

                        <!-- Pilihan Role -->
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Pilih Hak Akses (Role)</label>
                            <select name="role" required class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                <option value="">-- Pilih Role --</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->name }}" {{ old('role') == $role->name ? 'selected' : '' }}>
                                        {{ strtoupper($role->name) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Tombol Submit -->
                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('users.index') }}" class="text-gray-500 hover:text-gray-700 mr-4">Batal</a>
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Simpan Data
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>