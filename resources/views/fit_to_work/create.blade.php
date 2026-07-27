<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center">
            <a href="{{ route('fit-to-work.index') }}" class="mr-4 text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Formulir Kuesioner Fit to Work') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100">
                <div class="p-6 md:p-8">
                    
                    <div class="mb-6 border-b border-gray-100 pb-4">
                        <h3 class="text-lg font-bold text-gray-900">Data Karyawan & Kuesioner</h3>
                        <p class="text-sm text-gray-500 mt-1">Harap isi data berikut dengan sejujurnya sebelum melakukan pemeriksaan fisik di klinik.</p>
                    </div>

                    <form action="{{ route('fit-to-work.store') }}" method="POST">
                        @csrf
                        
                        <!-- Data Karyawan -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Umur <span class="text-red-500">*</span></label>
                                <input type="number" name="age" value="{{ old('age') }}" required min="17" class="shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md w-full" placeholder="cth: 25">
                                @error('age') <span class="text-sm text-red-600 mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Lama Tidur Malam (Jam) <span class="text-red-500">*</span></label>
                                <input type="number" name="sleep_hours" value="{{ old('sleep_hours') }}" required min="0" max="24" class="shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md w-full" placeholder="cth: 7">
                                @error('sleep_hours') <span class="text-sm text-red-600 mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">Jenis Pekerjaan <span class="text-red-500">*</span></label>
                                <input type="text" name="job_type" value="{{ old('job_type') }}" required class="shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md w-full" placeholder="cth: Bekerja di Ruang Terbatas">
                                @error('job_type') <span class="text-sm text-red-600 mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Kuesioner Kesehatan -->
                        <div class="mb-6 border-b border-gray-100 pb-4">
                            <h3 class="text-md font-bold text-indigo-600 uppercase tracking-wider mb-4">Pertanyaan Kuesioner</h3>
                            <div class="space-y-4">
                                
                                @php
                                    $questions = [
                                        'q1_is_fit' => '1. Apakah Saudara saat ini dalam kondisi FIT?',
                                        'q2_accustomed' => '2. Apakah Anda terbiasa bekerja di ruang terbatas?',
                                        'q3_vision_issue' => '3. Apakah Anda ada gangguan penglihatan?',
                                        'q4_asthma' => '4. Apakah Anda menderita penyakit Asma?',
                                        'q5_epilepsy' => '5. Apakah Anda menderita penyakit Epilepsi / Ayan?',
                                        'q6_trained' => '6. Apakah Anda sudah mendapatkan pelatihan tentang bekerja di ruang terbatas?'
                                    ];
                                @endphp

                                @foreach($questions as $name => $label)
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-200">
                                    <label class="text-sm font-semibold text-gray-700 mb-2 sm:mb-0 sm:w-2/3">{{ $label }} <span class="text-red-500">*</span></label>
                                    <div class="flex space-x-4 sm:w-1/3 sm:justify-end">
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input type="radio" name="{{ $name }}" value="1" class="form-radio text-indigo-600 h-4 w-4" required {{ old($name) == '1' ? 'checked' : '' }}>
                                            <span class="ml-2 text-sm text-gray-700 font-bold">Ya</span>
                                        </label>
                                        <label class="inline-flex items-center cursor-pointer">
                                            <input type="radio" name="{{ $name }}" value="0" class="form-radio text-red-600 h-4 w-4" required {{ old($name) == '0' ? 'checked' : '' }}>
                                            <span class="ml-2 text-sm text-gray-700 font-bold">Tidak</span>
                                        </label>
                                    </div>
                                    @error($name) <span class="text-sm text-red-600 mt-1 block w-full">{{ $message }}</span> @enderror
                                </div>
                                @endforeach

                            </div>
                        </div>

                        <!-- Area Tombol -->
                        <div class="mt-8 flex items-center justify-end space-x-3">
                            <a href="{{ route('fit-to-work.index') }}" class="px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 transition">
                                Batal
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
                                Kirim & Lanjut ke Medis
                            </button>
                        </div>
                        
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>