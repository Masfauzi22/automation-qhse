<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <!-- Tombol Kembali -->
                <a href="{{ route('simper.index') }}" class="mr-4 text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Edit Data SIMPER') }}
                </h2>
            </div>
            <!-- Badge Status di Pojok Kanan Atas -->
            <div>
                @if($simper->status == 'Active')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 border border-green-200">Status: Aktif</span>
                @elseif($simper->status == 'Expired')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800 border border-red-200">Status: Expired</span>
                @else
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800 border border-gray-200">Status: Dicabut</span>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100">
                <div class="p-6 md:p-8">
                    
                    <div class="mb-6 border-b border-gray-100 pb-4">
                        <h3 class="text-lg font-bold text-gray-900">Formulir Pembaruan Data</h3>
                        <p class="text-sm text-gray-500 mt-1">Perbarui informasi personal, masa berlaku, status, atau otorisasi alat berat.</p>
                    </div>

                    <!-- Gunakan route update dan method PUT -->
                    <form action="{{ route('simper.update', $simper->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <!-- Area Preview Foto (Otomatis load foto lama jika ada) -->
                        <div x-data="{ photoName: null, photoPreview: '{{ $simper->photo_path ? asset('storage/' . $simper->photo_path) : '' }}' }" class="mb-8">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Pas Foto Pegawai <span class="text-gray-400 font-normal">(Biarkan kosong jika tidak ingin ganti foto)</span></label>
                            
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
                                    <button type="button" @click="$refs.photo.click()" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 transition ease-in-out duration-150">
                                        Ganti Foto
                                    </button>
                                </div>
                            </div>
                            @error('photo_path') <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Grid 2 Kolom untuk Input Teks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            <!-- Nama -->
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                                <input type="text" name="name" value="{{ old('name', $simper->name) }}" required class="shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md w-full">
                                @error('name') <span class="text-sm text-red-600 mt-1">{{ $message }}</span> @enderror
                            </div>

                            <!-- NRP -->
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">NRP / NIK</label>
                                <input type="text" name="nrp" value="{{ old('nrp', $simper->nrp) }}" class="shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md w-full">
                            </div>

                            <!-- Perusahaan -->
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Perusahaan / Kontraktor <span class="text-red-500">*</span></label>
                                <input type="text" name="company" value="{{ old('company', $simper->company) }}" required class="shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md w-full">
                            </div>

                            <!-- Departemen -->
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Departemen <span class="text-red-500">*</span></label>
                                <input type="text" name="department" value="{{ old('department', $simper->department) }}" required class="shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md w-full">
                            </div>

                            <!-- Golongan Darah -->
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Golongan Darah <span class="text-red-500">*</span></label>
                                <select name="blood_type" required class="shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md w-full bg-white">
                                    <option value="A" {{ old('blood_type', $simper->blood_type) == 'A' ? 'selected' : '' }}>A</option>
                                    <option value="B" {{ old('blood_type', $simper->blood_type) == 'B' ? 'selected' : '' }}>B</option>
                                    <option value="AB" {{ old('blood_type', $simper->blood_type) == 'AB' ? 'selected' : '' }}>AB</option>
                                    <option value="O" {{ old('blood_type', $simper->blood_type) == 'O' ? 'selected' : '' }}>O</option>
                                </select>
                            </div>

                            <!-- Jabatan -->
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Jabatan <span class="text-red-500">*</span></label>
                                <input type="text" name="position" value="{{ old('position', $simper->position) }}" required class="shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md w-full">
                            </div>
                            
                            <!-- SIM Polisi -->
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">SIM Polisi <span class="text-red-500">*</span></label>
                                <input type="text" name="police_license" value="{{ old('police_license', $simper->police_license) }}" required class="shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md w-full">
                            </div>

                            <!-- Area yang di ijinkan -->
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Area yang Diizinkan <span class="text-red-500">*</span></label>
                                <input type="text" name="authorized_area" value="{{ old('authorized_area', $simper->authorized_area) }}" required class="shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md w-full">
                            </div>

                            <!-- Nomor SIMPER -->
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Nomor SIMPER <span class="text-red-500">*</span></label>
                                <input type="text" name="simper_number" value="{{ old('simper_number', $simper->simper_number) }}" required class="shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md w-full bg-yellow-50">
                                @error('simper_number') <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            
                            <!-- Status (Khusus Edit) -->
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Status Kartu <span class="text-red-500">*</span></label>
                                <select name="status" required class="shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md w-full bg-white">
                                    <option value="Active" {{ old('status', $simper->status) == 'Active' ? 'selected' : '' }}>Aktif</option>
                                    <option value="Expired" {{ old('status', $simper->status) == 'Expired' ? 'selected' : '' }}>Expired (Habis Masa Berlaku)</option>
                                    <option value="Revoked" {{ old('status', $simper->status) == 'Revoked' ? 'selected' : '' }}>Dicabut (Pelanggaran/Resign)</option>
                                </select>
                            </div>

                            <!-- Tanggal Terbit -->
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Tanggal Terbit <span class="text-red-500">*</span></label>
                                <input type="date" name="issue_date" value="{{ old('issue_date', \Carbon\Carbon::parse($simper->issue_date)->format('Y-m-d')) }}" required class="shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md w-full">
                            </div>

                            <!-- Masa Berlaku -->
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Masa Berlaku <span class="text-red-500">*</span></label>
                                <input type="date" name="valid_until" value="{{ old('valid_until', \Carbon\Carbon::parse($simper->valid_until)->format('Y-m-d')) }}" required class="shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md w-full">
                            </div>
                        </div>

                        <!-- Checkbox Otorisasi Unit / Alat Berat -->
                        <div class="mt-10 border-t border-gray-100 pt-6">
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
                                    
                                    // PERBAIKAN: Datanya sudah berbentuk array, langsung kita panggil
                                    $savedEquipments = $simper->equipments ?? [];
                                @endphp

                                @foreach($units as $unit)
                                <label class="inline-flex items-center p-2 border border-gray-200 rounded-md cursor-pointer hover:bg-gray-50 transition-colors">
                                    <input type="checkbox" name="equipments[]" value="{{ $unit }}" class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500" 
                                        {{ in_array($unit, old('equipments', $savedEquipments)) ? 'checked' : '' }}>
                                    <span class="ml-2 text-sm text-gray-700">{{ $unit }}</span>
                                </label>
                                @endforeach
                            </div>
                            @error('equipments') <span class="text-sm text-red-600 mt-2 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Area Tombol -->
                        <div class="mt-10 flex items-center justify-end space-x-3 pt-5 border-t border-gray-100">
                            <a href="{{ route('simper.index') }}" class="px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 transition ease-in-out duration-150">
                                Batal
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 transition ease-in-out duration-150">
                                Perbarui Data
                            </button>
                        </div>
                        
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>