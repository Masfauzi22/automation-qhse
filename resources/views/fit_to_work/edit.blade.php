<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center">
            <a href="{{ route('fit-to-work.index') }}" class="mr-4 text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                @if(Auth::user()->hasRole('medis') && $fitToWork->status == 'Pending_Medis')
                    {{ __('Proses Pemeriksaan Medis') }}
                @else
                    {{ __('Validasi Akhir QHSE') }}
                @endif
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100">
                
                <form action="{{ route('fit-to-work.update', $fitToWork->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="p-6 md:p-8">
                        <!-- BAGIAN 1: IDENTITAS & KUESIONER (READ-ONLY UNTUK MEDIS & QHSE) -->
                        <div class="mb-8 pb-6 border-b border-gray-200">
                            <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                Data Pemohon (Diisi oleh Pekerja)
                            </h3>
                            
                            <div class="bg-gray-50 rounded-lg p-5 border border-gray-200">
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                                    <div>
                                        <p class="text-xs font-semibold text-gray-500 uppercase">Nama Pekerja</p>
                                        <p class="text-sm font-bold text-gray-900 mt-1">{{ $fitToWork->user->name }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-semibold text-gray-500 uppercase">Jenis Pekerjaan</p>
                                        <p class="text-sm font-bold text-gray-900 mt-1">{{ $fitToWork->job_type }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-semibold text-gray-500 uppercase">Umur</p>
                                        <p class="text-sm font-bold text-gray-900 mt-1">{{ $fitToWork->age }} Tahun</p>
                                    </div>
                                    <div>
                                        <p class="text-xs font-semibold text-gray-500 uppercase">Tidur Malam</p>
                                        <p class="text-sm font-bold text-gray-900 mt-1">{{ $fitToWork->sleep_hours }} Jam</p>
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <p class="text-xs font-bold text-gray-900 uppercase border-b border-gray-200 pb-2 mb-3">Hasil Kuesioner:</p>
                                    
                                    @php
                                        $q_answers = [
                                            '1. Kondisi saat ini FIT?' => $fitToWork->q1_is_fit,
                                            '2. Terbiasa di ruang terbatas?' => $fitToWork->q2_accustomed,
                                            '3. Ada gangguan penglihatan?' => $fitToWork->q3_vision_issue,
                                            '4. Menderita Asma?' => $fitToWork->q4_asthma,
                                            '5. Menderita Epilepsi/Ayan?' => $fitToWork->q5_epilepsy,
                                            '6. Telah mendapat pelatihan?' => $fitToWork->q6_trained,
                                        ];
                                    @endphp

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-2">
                                        @foreach($q_answers as $question => $answer)
                                        <div class="flex justify-between items-center bg-white p-2 rounded border border-gray-100">
                                            <span class="text-xs text-gray-600">{{ $question }}</span>
                                            @if($answer)
                                                <span class="px-2 py-0.5 bg-green-100 text-green-700 text-xs font-bold rounded">YA</span>
                                            @else
                                                <span class="px-2 py-0.5 bg-red-100 text-red-700 text-xs font-bold rounded">TIDAK</span>
                                            @endif
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- BAGIAN 2: PEMERIKSAAN FISIK (DIISI OLEH MEDIS, READ-ONLY UNTUK QHSE) -->
                        <div class="mb-8">
                            <h3 class="text-lg font-bold text-indigo-700 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                                Pemeriksaan Fisik (Klinik)
                            </h3>
                            
                            @if(Auth::user()->hasRole('medis') && $fitToWork->status == 'Pending_Medis')
                                <!-- Form Input Untuk Dokter -->
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 bg-indigo-50 p-6 rounded-lg border border-indigo-100">
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-1">Tekanan Darah (mmHg) <span class="text-red-500">*</span></label>
                                        <input type="text" name="blood_pressure" value="{{ old('blood_pressure') }}" required class="shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md w-full" placeholder="cth: 120/80">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-1">Suhu Tubuh (°C) <span class="text-red-500">*</span></label>
                                        <input type="text" name="body_temp" value="{{ old('body_temp') }}" required class="shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md w-full" placeholder="cth: 36.5">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-1">SpO2 (%) <span class="text-red-500">*</span></label>
                                        <input type="text" name="spo2" value="{{ old('spo2') }}" required class="shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md w-full" placeholder="cth: 98">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-1">Denyut Nadi (x/menit) <span class="text-red-500">*</span></label>
                                        <input type="text" name="heart_rate" value="{{ old('heart_rate') }}" required class="shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md w-full" placeholder="cth: 80">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-1">Pernafasan (x/menit) <span class="text-red-500">*</span></label>
                                        <input type="text" name="respiration_rate" value="{{ old('respiration_rate') }}" required class="shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md w-full" placeholder="cth: 18">
                                    </div>
                                </div>
                                
                                <div class="mt-6 border-t border-gray-200 pt-5">
                                    <label class="block text-sm font-bold text-gray-900 mb-3">Rekomendasi Medis: <span class="text-red-500">*</span></label>
                                    <div class="flex space-x-6">
                                        <label class="inline-flex items-center cursor-pointer bg-white border border-gray-200 p-3 rounded-lg hover:bg-green-50 w-full justify-center">
                                            <input type="radio" name="is_recommended" value="1" class="form-radio text-green-600 h-5 w-5" required>
                                            <span class="ml-2 text-sm font-bold text-green-700">DAPAT BEKERJA</span>
                                        </label>
                                        <label class="inline-flex items-center cursor-pointer bg-white border border-gray-200 p-3 rounded-lg hover:bg-red-50 w-full justify-center">
                                            <input type="radio" name="is_recommended" value="0" class="form-radio text-red-600 h-5 w-5" required>
                                            <span class="ml-2 text-sm font-bold text-red-700">TIDAK DAPAT BEKERJA</span>
                                        </label>
                                    </div>
                                </div>
                            @else
                                <!-- Tampilan Read-Only Data Dokter untuk dilihat oleh QHSE -->
                                <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 bg-gray-50 p-5 rounded-lg border border-gray-200">
                                    <div><p class="text-xs text-gray-500">Tensi</p><p class="font-bold">{{ $fitToWork->blood_pressure }} mmHg</p></div>
                                    <div><p class="text-xs text-gray-500">Suhu</p><p class="font-bold">{{ $fitToWork->body_temp }} °C</p></div>
                                    <div><p class="text-xs text-gray-500">SpO2</p><p class="font-bold">{{ $fitToWork->spo2 }} %</p></div>
                                    <div><p class="text-xs text-gray-500">Nadi</p><p class="font-bold">{{ $fitToWork->heart_rate }} x/m</p></div>
                                    <div><p class="text-xs text-gray-500">Nafas</p><p class="font-bold">{{ $fitToWork->respiration_rate }} x/m</p></div>
                                </div>
                                
                                <div class="mt-4 p-4 rounded-lg flex items-center {{ $fitToWork->is_recommended ? 'bg-green-100 border border-green-200' : 'bg-red-100 border border-red-200' }}">
                                    <div class="mr-3">
                                        @if($fitToWork->is_recommended)
                                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        @else
                                            <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="text-xs font-bold text-gray-600 uppercase">Rekomendasi Dokter:</p>
                                        <p class="text-lg font-bold {{ $fitToWork->is_recommended ? 'text-green-700' : 'text-red-700' }}">
                                            {{ $fitToWork->is_recommended ? 'DAPAT BEKERJA (FIT)' : 'TIDAK DAPAT BEKERJA (UNFIT)' }}
                                        </p>
                                        <p class="text-xs text-gray-500 mt-0.5">Diperiksa oleh: {{ $fitToWork->medical->name ?? 'Tim Medis' }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- BAGIAN 3: VALIDASI QHSE (HANYA MUNCUL JIKA STATUS PENDING_QHSE) -->
                        @if(Auth::user()->hasAnyRole(['superadmin', 'qhse']) && $fitToWork->status == 'Pending_QHSE')
                        <div class="mt-8 border-t border-dashed border-gray-300 pt-6">
                            <h3 class="text-lg font-bold text-blue-700 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                                Validasi Akhir QHSE
                            </h3>
                            
                            <div class="bg-blue-50 p-6 rounded-lg border border-blue-100">
                                <label class="block text-sm font-bold text-gray-900 mb-3">Keputusan Izin Kerja: <span class="text-red-500">*</span></label>
                                <div class="flex space-x-6">
                                    <label class="inline-flex items-center cursor-pointer bg-white border border-gray-200 p-3 rounded-lg hover:bg-green-50 w-full justify-center shadow-sm">
                                        <input type="radio" name="status" value="Approved" class="form-radio text-green-600 h-5 w-5" required>
                                        <span class="ml-2 text-sm font-bold text-green-700">IZINKAN BEKERJA (APPROVED)</span>
                                    </label>
                                    <label class="inline-flex items-center cursor-pointer bg-white border border-gray-200 p-3 rounded-lg hover:bg-red-50 w-full justify-center shadow-sm">
                                        <input type="radio" name="status" value="Rejected" class="form-radio text-red-600 h-5 w-5" required>
                                        <span class="ml-2 text-sm font-bold text-red-700">TOLAK IZIN KERJA (REJECTED)</span>
                                    </label>
                                </div>
                                <p class="text-xs text-blue-600 mt-3 text-center">Pastikan Anda telah memeriksa hasil rekomendasi medis sebelum mengeluarkan keputusan.</p>
                            </div>
                        </div>
                        @endif

                    </div>

                    <!-- AREA TOMBOL SUBMIT -->
                    <div class="px-8 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end space-x-3 rounded-b-xl">
                        <a href="{{ route('fit-to-work.index') }}" class="px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-100 transition">
                            Batal
                        </a>
                        
                        <button type="submit" class="inline-flex items-center px-6 py-2 bg-indigo-600 border border-transparent rounded-md font-bold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition shadow-md">
                            @if(Auth::user()->hasRole('medis'))
                                Simpan Medis & Lanjut ke QHSE
                            @else
                                Selesaikan Validasi
                            @endif
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</x-app-layout>