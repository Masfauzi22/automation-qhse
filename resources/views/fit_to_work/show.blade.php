<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center">
            <a href="{{ route('fit-to-work.index') }}" class="mr-4 text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center">
                {{ __('Detail Fit to Work') }} 
                
                <!-- Badge Status di Header -->
                @if($fitToWork->status == 'Pending_Medis')
                    <span class="ml-3 px-3 py-1 bg-yellow-100 text-yellow-800 text-xs font-bold rounded-full">Menunggu Medis</span>
                @elseif($fitToWork->status == 'Pending_QHSE')
                    <span class="ml-3 px-3 py-1 bg-blue-100 text-blue-800 text-xs font-bold rounded-full">Menunggu Validasi QHSE</span>
                @elseif($fitToWork->status == 'Approved')
                    <span class="ml-3 px-3 py-1 bg-green-100 text-green-800 text-xs font-bold rounded-full">FIT (Dapat Bekerja)</span>
                @else
                    <span class="ml-3 px-3 py-1 bg-red-100 text-red-800 text-xs font-bold rounded-full">UNFIT (Tidak Dapat Bekerja)</span>
                @endif
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100">
                <div class="p-6 md:p-8">
                    
                    <!-- BAGIAN 1: IDENTITAS & KUESIONER -->
                    <div class="mb-8 pb-6 border-b border-gray-200">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            Data Pemohon & Kuesioner
                        </h3>
                        
                        <div class="bg-gray-50 rounded-lg p-5 border border-gray-200">
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                                <div><p class="text-xs font-semibold text-gray-500 uppercase">Nama Pekerja</p><p class="text-sm font-bold text-gray-900 mt-1">{{ $fitToWork->user->name }}</p></div>
                                <div><p class="text-xs font-semibold text-gray-500 uppercase">Jenis Pekerjaan</p><p class="text-sm font-bold text-gray-900 mt-1">{{ $fitToWork->job_type }}</p></div>
                                <div><p class="text-xs font-semibold text-gray-500 uppercase">Umur</p><p class="text-sm font-bold text-gray-900 mt-1">{{ $fitToWork->age }} Tahun</p></div>
                                <div><p class="text-xs font-semibold text-gray-500 uppercase">Tidur Malam</p><p class="text-sm font-bold text-gray-900 mt-1">{{ $fitToWork->sleep_hours }} Jam</p></div>
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

                    <!-- BAGIAN 2: HASIL PEMERIKSAAN MEDIS (Muncul jika dokter sudah periksa) -->
                    @if($fitToWork->medical_id)
                    <div class="mb-8 pb-6 {{ $fitToWork->qhse_id ? 'border-b border-gray-200' : '' }}">
                        <h3 class="text-lg font-bold text-indigo-700 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                            Hasil Pemeriksaan Fisik (Klinik)
                        </h3>
                        
                        <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 bg-indigo-50 p-5 rounded-lg border border-indigo-100 mb-4">
                            <div><p class="text-xs text-indigo-500 uppercase">Tensi</p><p class="font-bold text-indigo-900">{{ $fitToWork->blood_pressure }} mmHg</p></div>
                            <div><p class="text-xs text-indigo-500 uppercase">Suhu</p><p class="font-bold text-indigo-900">{{ $fitToWork->body_temp }} °C</p></div>
                            <div><p class="text-xs text-indigo-500 uppercase">SpO2</p><p class="font-bold text-indigo-900">{{ $fitToWork->spo2 }} %</p></div>
                            <div><p class="text-xs text-indigo-500 uppercase">Nadi</p><p class="font-bold text-indigo-900">{{ $fitToWork->heart_rate }} x/m</p></div>
                            <div><p class="text-xs text-indigo-500 uppercase">Nafas</p><p class="font-bold text-indigo-900">{{ $fitToWork->respiration_rate }} x/m</p></div>
                        </div>

                        <div class="p-4 rounded-lg flex items-center {{ $fitToWork->is_recommended ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' }}">
                            <div class="mr-3">
                                @if($fitToWork->is_recommended)
                                    <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                @else
                                    <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                @endif
                            </div>
                            <div>
                                <p class="text-xs font-bold text-gray-500 uppercase">Rekomendasi Tim Medis:</p>
                                <p class="text-lg font-bold {{ $fitToWork->is_recommended ? 'text-green-700' : 'text-red-700' }}">
                                    {{ $fitToWork->is_recommended ? 'DAPAT BEKERJA (FIT)' : 'TIDAK DAPAT BEKERJA (UNFIT)' }}
                                </p>
                                <p class="text-xs text-gray-500 mt-1">Diperiksa oleh: <strong>{{ $fitToWork->medical->name }}</strong> pada {{ $fitToWork->updated_at->format('d M Y - H:i') }}</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- BAGIAN 3: KEPUTUSAN QHSE (Muncul jika QHSE sudah validasi) -->
                    @if($fitToWork->qhse_id)
                    <div class="mt-6">
                        <h3 class="text-lg font-bold text-blue-700 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                            Keputusan Akhir QHSE
                        </h3>
                        
                        <div class="bg-blue-50 p-6 rounded-lg border border-blue-100 flex items-center justify-between">
                            <div>
                                <p class="text-sm text-blue-800 font-medium">Status Izin Kerja:</p>
                                @if($fitToWork->status == 'Approved')
                                    <p class="text-2xl font-black text-green-600 uppercase mt-1">DIIZINKAN (APPROVED)</p>
                                @else
                                    <p class="text-2xl font-black text-red-600 uppercase mt-1">DITOLAK (REJECTED)</p>
                                @endif
                                <p class="text-xs text-blue-600 mt-2">Divalidasi oleh: <strong>{{ $fitToWork->qhse->name }}</strong></p>
                            </div>
                            
                            <!-- Stempel Visual -->
                            <div class="hidden sm:block opacity-20 transform -rotate-12">
                                @if($fitToWork->status == 'Approved')
                                    <span class="border-4 border-green-600 text-green-600 font-black text-4xl p-2 rounded">APPROVED</span>
                                @else
                                    <span class="border-4 border-red-600 text-red-600 font-black text-4xl p-2 rounded">REJECTED</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif
                    
                </div>
                
                <div class="px-8 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end rounded-b-xl">
                    <a href="{{ route('fit-to-work.index') }}" class="px-6 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-100 transition shadow-sm">
                        Kembali ke Daftar
                    </a>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>