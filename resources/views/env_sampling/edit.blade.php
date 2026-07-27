<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center">
            <a href="{{ route('env-sampling.index') }}" class="mr-4 text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Update Data & Upload LHU</h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100">
                
                <!-- PENTING: enctype="multipart/form-data" wajib untuk upload file -->
                <form action="{{ route('env-sampling.update', $envSampling->id) }}" method="POST" enctype="multipart/form-data" class="p-6 md:p-8">
                    @csrf
                    @method('PUT')
                    
                    <!-- Informasi Jadwal Dasar -->
                    <div class="bg-gray-50 p-4 rounded-lg mb-6 border border-gray-200 grid grid-cols-3 gap-4">
                        <div>
                            <p class="text-xs text-gray-500 uppercase font-semibold">Periode</p>
                            <p class="font-bold text-gray-900">{{ \Carbon\Carbon::parse($envSampling->sampling_period)->translatedFormat('F Y') }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase font-semibold">Lokasi / Titik</p>
                            <p class="font-bold text-gray-900">{{ $envSampling->location }}</p>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 uppercase font-semibold mb-1">Status Progres <span class="text-red-500">*</span></label>
                            <select name="status" required class="shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md w-full text-sm font-bold text-indigo-700 bg-indigo-50">
                                <option value="Scheduled" {{ $envSampling->status == 'Scheduled' ? 'selected' : '' }}>Dijadwalkan</option>
                                <option value="Sampled" {{ $envSampling->status == 'Sampled' ? 'selected' : '' }}>Sampel Diambil (Menunggu Lab)</option>
                                <option value="LHU_Received" {{ $envSampling->status == 'LHU_Received' ? 'selected' : '' }}>LHU Diterima (Selesai)</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                        <!-- Kolom Kiri: Checklist Lapangan -->
                        <div>
                            <h3 class="text-md font-bold text-gray-900 mb-4 pb-2 border-b border-gray-200">Checklist Pelaksanaan</h3>
                            
                            <div class="space-y-3">
                                <label class="flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition">
                                    <input type="checkbox" name="checklist_data[botol_siap]" value="Ya" {{ isset($envSampling->checklist_data['botol_siap']) ? 'checked' : '' }} class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                    <span class="ml-3 text-sm font-medium text-gray-700">Botol sampel & pengawet disiapkan</span>
                                </label>
                                
                                <label class="flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition">
                                    <input type="checkbox" name="checklist_data[label_terpasang]" value="Ya" {{ isset($envSampling->checklist_data['label_terpasang']) ? 'checked' : '' }} class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                    <span class="ml-3 text-sm font-medium text-gray-700">Label botol & segel terpasang</span>
                                </label>

                                <label class="flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition">
                                    <input type="checkbox" name="checklist_data[suhu_terjaga]" value="Ya" {{ isset($envSampling->checklist_data['suhu_terjaga']) ? 'checked' : '' }} class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                    <span class="ml-3 text-sm font-medium text-gray-700">Disimpan di Coolbox (Suhu terjaga)</span>
                                </label>

                                <label class="flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition">
                                    <input type="checkbox" name="checklist_data[ba_ditandatangani]" value="Ya" {{ isset($envSampling->checklist_data['ba_ditandatangani']) ? 'checked' : '' }} class="w-5 h-5 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                    <span class="ml-3 text-sm font-medium text-gray-700">Berita Acara (BA) ditandatangani</span>
                                </label>
                            </div>
                        </div>

                        <!-- Kolom Kanan: Input Angka & Upload PDF -->
                        <div>
                            <h3 class="text-md font-bold text-gray-900 mb-4 pb-2 border-b border-gray-200">Arsip & Hasil LHU</h3>
                            
                            <div class="mb-5">
                                <label class="block text-sm font-bold text-gray-700 mb-1">Upload File LHU (PDF Maks 5MB)</label>
                                @if($envSampling->lhu_file_path)
                                    <div class="mb-2 text-sm text-green-600 flex items-center font-medium">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        File PDF sudah terunggah. Upload file baru untuk menimpa.
                                    </div>
                                @endif
                                <input type="file" name="lhu_file" accept=".pdf" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 border border-gray-300 rounded-md">
                                @error('lhu_file') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div class="space-y-4 bg-indigo-50 p-4 rounded-lg border border-indigo-100">
                                <p class="text-xs text-indigo-600 font-bold uppercase">Input Angka Hasil Lab Utama</p>
                                
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Kadar pH</label>
                                    <input type="number" step="0.01" name="lhu_results[ph_level]" value="{{ $envSampling->lhu_results['ph_level'] ?? '' }}" class="shadow-sm border-gray-300 focus:border-indigo-500 rounded-md w-full py-1.5 text-sm" placeholder="cth: 7.2">
                                </div>
                                
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">TSS (Total Suspended Solid) - mg/L</label>
                                    <input type="number" step="0.01" name="lhu_results[tss]" value="{{ $envSampling->lhu_results['tss'] ?? '' }}" class="shadow-sm border-gray-300 focus:border-indigo-500 rounded-md w-full py-1.5 text-sm" placeholder="cth: 45.5">
                                </div>
                                
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Besi (Fe) - mg/L</label>
                                    <input type="number" step="0.01" name="lhu_results[fe]" value="{{ $envSampling->lhu_results['fe'] ?? '' }}" class="shadow-sm border-gray-300 focus:border-indigo-500 rounded-md w-full py-1.5 text-sm" placeholder="cth: 2.1">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end pt-4 border-t border-gray-100">
                        <a href="{{ route('env-sampling.index') }}" class="px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 transition mr-3">Batal</a>
                        <button type="submit" class="px-6 py-2 bg-indigo-600 border border-transparent rounded-md font-bold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>