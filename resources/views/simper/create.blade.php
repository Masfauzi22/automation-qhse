<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <!-- Tombol Kembali -->
                <a href="{{ route('simper.index') }}" class="mr-4 text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    @hasanyrole('superadmin|qhse')
                        {{ __('Tambah Data SIMPER Baru') }}
                    @else
                        {{ __('Formulir Pengajuan SIMPER') }}
                    @endhasanyrole
                </h2>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100">
                <div class="p-6 md:p-8">
                    
                    <div class="mb-6 border-b border-gray-100 pb-4">
                        @hasanyrole('superadmin|qhse')
                            <h3 class="text-lg font-bold text-gray-900">Input Data SIMPER Administratif</h3>
                            <p class="text-sm text-gray-500 mt-1">Masukkan data lengkap pegawai, nomor SIMPER, dan otorisasi alat berat.</p>
                        @else
                            <h3 class="text-lg font-bold text-gray-900">Identitas Pemohon</h3>
                            <p class="text-sm text-gray-500 mt-1">Isi data diri dan jabatan Anda dengan benar. Nomor SIMPER akan diproses oleh tim QHSE.</p>
                        @endhasanyrole
                    </div>

                    <form action="{{ route('simper.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <!-- Area Upload Foto -->
                        <div x-data="{ photoName: null, photoPreview: null }" class="mb-8">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Pas Foto Pegawai <span class="text-gray-400 font-normal">(Opsional)</span></label>
                            <div class="flex items-center space-x-6">
                                <div class="shrink-0">
                                    <div x-show="!photoPreview" class="h-24 w-20 object-cover rounded-lg bg-gray-100 border border-gray-200 flex items-center justify-center text-gray-400 shadow-sm">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    </div>
                                    <img x-show="photoPreview" :src="photoPreview" class="h-24 w-20 object-cover rounded-lg border border-indigo-200 shadow-sm" style="display: none;">
                                </div>
                                <div>
                                    <input type="file" name="photo_path" class="hidden" x-ref="photo" accept="image/*"
                                        @change="
                                            photoName = $refs.photo.files[0].name;
                                            const reader = new FileReader();
                                            reader.onload = (e) => { photoPreview = e.target.result; };
                                            reader.readAsDataURL($refs.photo.files[0]);
                                        ">
                                    <button type="button" @click="$refs.photo.click()" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        Pilih Foto
                                    </button>
                                    <div class="mt-2 text-xs text-gray-500">Format JPG, PNG maksimal 2MB. Dimensi ID Card (3x4).</div>
                                </div>
                            </div>
                            @error('photo_path') <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- BAGIAN 1: IDENTITAS DASAR (BISA DILIHAT SEMUA ROLE) -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                                <input type="text" name="name" value="{{ old('name') }}" required class="shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md w-full" placeholder="cth: Jhon Doe">
                                @error('name') <span class="text-sm text-red-600 mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">NRP / NIK <span class="text-gray-400 font-normal">(Opsional)</span></label>
                                <input type="text" name="nrp" value="{{ old('nrp') }}" class="shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md w-full" placeholder="cth: 15029999">
                                @error('nrp') <span class="text-sm text-red-600 mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Perusahaan / Kontraktor <span class="text-red-500">*</span></label>
                                <input type="text" name="company" value="{{ old('company') }}" required class="shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md w-full" placeholder="cth: PT. Angkasapura Support">
                                @error('company') <span class="text-sm text-red-600 mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Departemen <span class="text-red-500">*</span></label>
                                <input type="text" name="department" value="{{ old('department') }}" required class="shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md w-full" placeholder="cth: Operation">
                                @error('department') <span class="text-sm text-red-600 mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Golongan Darah <span class="text-red-500">*</span></label>
                                <select name="blood_type" required class="shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md w-full bg-white">
                                    <option value="" disabled selected>Pilih Gol. Darah</option>
                                    <option value="A" {{ old('blood_type') == 'A' ? 'selected' : '' }}>A</option>
                                    <option value="B" {{ old('blood_type') == 'B' ? 'selected' : '' }}>B</option>
                                    <option value="AB" {{ old('blood_type') == 'AB' ? 'selected' : '' }}>AB</option>
                                    <option value="O" {{ old('blood_type') == 'O' ? 'selected' : '' }}>O</option>
                                </select>
                                @error('blood_type') <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Jabatan <span class="text-red-500">*</span></label>
                                <input type="text" name="position" value="{{ old('position') }}" required class="shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md w-full" placeholder="cth: Operator Excavator">
                                @error('position') <span class="text-sm text-red-600 mt-1">{{ $message }}</span> @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">SIM Polisi Nasional<span class="text-red-500">*</span></label>
                                <input type="text" name="police_license" value="{{ old('police_license') }}" required class="shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md w-full" placeholder="cth: Tipe B2 Umum">
                                @error('police_license') <span class="text-sm text-red-600 mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>


                        <!-- BAGIAN 2: ADMINISTRASI QHSE (HANYA MUNCUL JIKA YANG LOGIN ADALAH QHSE/SUPERADMIN) -->
                        @hasanyrole('superadmin|qhse')
                        
                        <div class="mt-8 pt-8 border-t border-dashed border-gray-300">
                            <h4 class="text-sm font-bold text-indigo-600 uppercase tracking-wider mb-6 flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                Bagian Khusus QHSE (Otorisasi & Administrasi)
                            </h4>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Area yang Diizinkan <span class="text-red-500">*</span></label>
                                    <input type="text" name="authorized_area" value="{{ old('authorized_area') }}" class="shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md w-full" placeholder="cth: All Area">
                                    @error('authorized_area') <span class="text-sm text-red-600 mt-1">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Nomor SIMPER <span class="text-red-500">*</span></label>
                                    <input type="text" name="simper_number" value="{{ old('simper_number') }}" class="shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md w-full bg-yellow-50" placeholder="cth: IBT/SIMPER/OPR/1025/583">
                                    @error('simper_number') <span class="text-sm text-red-600 mt-1">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Tanggal Terbit <span class="text-red-500">*</span></label>
                                    <input type="date" name="issue_date" value="{{ old('issue_date') }}" class="shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md w-full">
                                    @error('issue_date') <span class="text-sm text-red-600 mt-1">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Masa Berlaku <span class="text-red-500">*</span></label>
                                    <input type="date" name="valid_until" value="{{ old('valid_until') }}" class="shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md w-full">
                                    @error('valid_until') <span class="text-sm text-red-600 mt-1">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- Checkbox Otorisasi Unit / Alat Berat -->
                            <div class="mt-8 pt-6">
                                <label class="block text-sm font-bold text-gray-900 mb-1">Authorize Licence (Unit yang diizinkan) <span class="text-red-500">*</span></label>
                                <p class="text-xs text-gray-500 mb-4">Centang unit atau alat berat yang diotorisasi untuk dioperasikan (sesuai kartu).</p>
                                
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                                    @php
                                        $units = [
                                            'Motor Cycle', 'Mobile Crane', 'Light Vehicle', 'Hoist Crane',
                                            'Light Truck', 'Barge Crane', 'Heavy Truck', 'Motor Grader',
                                            'Dump Truck', 'Excavator 320C', 'Backhoe Loader', 'Bob Cat',
                                            'W. Loader 924K', 'Control Room', 'Speed Boat', 'Shiploader',
                                            'Dozer D5G', 'Jetty Train', 'Dozer D10R', 'Truck Palfinger',
                                            'Forklift FD35T', 'Kubota Tractor', 'Dozer 8', 'W. Loader 928G',
                                            'Manitou MT 625', 'Isolator', 'Excavator 320', 'Fire Truck Hn 500'
                                        ];
                                    @endphp

                                    @foreach($units as $unit)
                                    <label class="inline-flex items-center p-2 border border-gray-200 rounded-md cursor-pointer hover:bg-gray-50 transition-colors">
                                        <input type="checkbox" name="equipments[]" value="{{ $unit }}" class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500" {{ is_array(old('equipments')) && in_array($unit, old('equipments')) ? 'checked' : '' }}>
                                        <span class="ml-2 text-sm text-gray-700">{{ $unit }}</span>
                                    </label>
                                    @endforeach
                                </div>
                                @error('equipments') <span class="text-sm text-red-600 mt-2 block">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                        @endhasanyrole

                        <!-- Area Tombol (Teks dinamis) -->
                        <div class="mt-10 flex items-center justify-end space-x-3 pt-5 border-t border-gray-100">
                            <a href="{{ route('simper.index') }}" class="px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 transition ease-in-out duration-150">
                                Batal
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                @hasanyrole('superadmin|qhse')
                                    Simpan Data SIMPER
                                @else
                                    Kirim Pengajuan SIMPER
                                @endhasanyrole
                            </button>
                        </div>
                        
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>