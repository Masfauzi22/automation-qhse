<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center">
            <a href="{{ route('env-sampling.index') }}" class="mr-4 text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Input Hasil Uji Lab (Sampling)') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100 p-8">
                
                <form action="{{ route('env-sampling.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <!-- BAGIAN 1: INFORMASI UMUM (HEADER) -->
                    <div class="mb-8">
                        <h3 class="text-lg font-bold text-gray-900 border-b border-gray-200 pb-2 mb-4">1. Informasi Pengambilan Sampel</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            <!-- Pilih Subject (Kategori) -->
                            <div class="md:col-span-2">
                                <label for="sampling_subject_id" class="block text-sm font-bold text-gray-700">Kategori / Subject <span class="text-red-500">*</span></label>
                                <select name="sampling_subject_id" id="sampling_subject_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm bg-indigo-50 border-2 border-indigo-200 py-3" required>
                                    <option value="">-- Pilih Kategori Subject Uji Lab --</option>
                                    @foreach($subjects as $subject)
                                        <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                    @endforeach
                                </select>
                                <p class="text-xs text-gray-500 mt-1">Parameter uji akan muncul otomatis di bawah setelah Kategori dipilih.</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Titik / Sample Mark <span class="text-red-500">*</span></label>
                                <input type="text" name="sample_mark" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Cth: AIR BAKU (DAM)" required>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Koordinat (Opsional)</label>
                                <input type="text" name="coordinate" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Cth: S: 04° 01' 41.6'' E: 116° 03' 07.9''">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Tanggal Sampling <span class="text-red-500">*</span></label>
                                <input type="date" name="sampling_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Waktu Sampling (Opsional)</label>
                                <input type="time" name="sampling_time" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Catatan / Deskripsi (Opsional)</label>
                                <textarea name="description" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-bold text-gray-700">Upload Dokumen Hasil Lab (PDF/Image) <span class="text-red-500">*</span></label>
                                <input type="file" name="attachment_file" accept=".pdf,image/*" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 border border-gray-300 rounded p-1" required>
                            </div>

                        </div>
                    </div>

                    <!-- BAGIAN 2: PARAMETER UJI LAB (MUNCUL DINAMIS) -->
                    <div id="parameters-wrapper" class="mb-8 hidden">
                        <h3 class="text-lg font-bold text-gray-900 border-b border-gray-200 pb-2 mb-4">2. Input Hasil Uji Parameter</h3>
                        
                        <!-- Kontainer ini akan diisi otomatis oleh Javascript -->
                        <div id="parameters-container" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Input HTML masuk ke sini via AJAX -->
                        </div>
                    </div>

                    <div class="flex items-center justify-end border-t border-gray-200 pt-6">
                        <button type="submit" id="btn-submit" class="inline-flex items-center px-6 py-3 bg-indigo-600 border border-transparent rounded-lg font-bold text-sm text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-md">
                            Simpan Data Sampling
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <!-- SCRIPT AJAX UNTUK MEMANGGIL PARAMETER -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const subjectSelect = document.getElementById('sampling_subject_id');
            const parametersWrapper = document.getElementById('parameters-wrapper');
            const parametersContainer = document.getElementById('parameters-container');
            const btnSubmit = document.getElementById('btn-submit');

            subjectSelect.addEventListener('change', function () {
                const subjectId = this.value;
                parametersContainer.innerHTML = ''; // Kosongkan form sebelumnya

                if (!subjectId) {
                    parametersWrapper.classList.add('hidden'); // Sembunyikan jika tidak ada yg dipilih
                    return;
                }

                // Tampilkan loading
                parametersWrapper.classList.remove('hidden');
                parametersContainer.innerHTML = '<p class="text-gray-500 text-sm col-span-3">Memuat parameter dari database...</p>';
                btnSubmit.disabled = true;

                // Memanggil route env-sampling.get-parameters
                fetch(`/env-sampling/get-parameters/${subjectId}`)
                    .then(response => response.json())
                    .then(data => {
                        parametersContainer.innerHTML = ''; // Hapus loading text
                        btnSubmit.disabled = false;

                        if(data.length === 0) {
                            parametersContainer.innerHTML = '<p class="text-red-500 text-sm col-span-3">Belum ada parameter lab untuk Kategori ini. Silakan hubungi Superadmin.</p>';
                            return;
                        }

                        // Looping data parameter dan cetak form input HTML
                        data.forEach(param => {
                            const unitText = param.unit ? `(${param.unit})` : '';
                            const methodText = param.method ? `<span class="text-xs text-gray-500 block">Metode: ${param.method}</span>` : '';
                            
                            // Perhatikan pada bagian name="results[${param.id}]" 
                            // Ini akan membuat input berbentuk Array, sehingga di Controller sangat mudah disave!
                            const html = `
                                <div class="border border-gray-200 rounded-lg p-4 bg-gray-50 shadow-sm">
                                    <label class="block text-sm font-bold text-gray-800 mb-1">
                                        ${param.name} <span class="text-indigo-600">${unitText}</span>
                                    </label>
                                    ${methodText}
                                    <input type="text" name="results[${param.id}]" class="mt-3 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm font-medium" placeholder="Input hasil..." required>
                                </div>
                            `;
                            
                            parametersContainer.insertAdjacentHTML('beforeend', html);
                        });
                    })
                    .catch(error => {
                        parametersContainer.innerHTML = '<p class="text-red-500 text-sm col-span-3">Terjadi kesalahan koneksi. Silakan refresh halaman.</p>';
                        console.error('Error AJAX:', error);
                        btnSubmit.disabled = false;
                    });
            });
        });
    </script>
</x-app-layout>