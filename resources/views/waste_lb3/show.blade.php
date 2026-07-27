<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center">
            <a href="{{ route('waste-lb3.index') }}" class="mr-4 text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Bukti Serah Terima Manifest LB3</h2>
            
            @if($wasteLb3->status == 'Pending')
                <span class="ml-3 px-3 py-1 bg-yellow-100 text-yellow-800 text-xs font-bold rounded-full animate-pulse">Menunggu Diterima</span>
            @else
                <span class="ml-3 px-3 py-1 bg-green-100 text-green-800 text-xs font-bold rounded-full">Diterima TPS</span>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-200">
                
                <!-- KOP Surat / Header Dokumen -->
                <div class="border-b border-gray-200 p-6 bg-gray-50 flex justify-between items-center">
                    <div>
                        <h3 class="text-xl font-black text-gray-900 tracking-tight">MANIFEST INTERNAL LB3</h3>
                        <p class="text-sm text-gray-500">Nomor Registrasi: #LB3-{{ $wasteLb3->id }}-{{ $wasteLb3->created_at->format('ymd') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs font-bold text-gray-500 uppercase tracking-widest">Tanggal Request</p>
                        <p class="text-md font-bold text-gray-900">{{ $wasteLb3->created_at->format('d F Y') }}</p>
                    </div>
                </div>

                <div class="p-6 md:p-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                        
                        <!-- Rincian Limbah -->
                        <div class="space-y-6">
                            <div>
                                <p class="text-xs text-gray-500 font-bold uppercase mb-1">Jenis Limbah B3</p>
                                <p class="text-2xl font-black text-gray-900">{{ $wasteLb3->waste_type }}</p>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-red-50 border border-red-100 p-4 rounded-lg">
                                    <p class="text-xs text-red-800 font-bold uppercase mb-1">Total Volume</p>
                                    <p class="text-xl font-black text-red-600">{{ $wasteLb3->quantity }} <span class="text-sm font-semibold">{{ $wasteLb3->unit }}</span></p>
                                </div>
                                <div class="bg-gray-50 border border-gray-100 p-4 rounded-lg">
                                    <p class="text-xs text-gray-500 font-bold uppercase mb-1">Asal / Sumber</p>
                                    <p class="text-md font-bold text-gray-900">{{ $wasteLb3->source_location }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Kolom Tanda Tangan -->
                        <div class="border-l-0 md:border-l border-gray-200 pt-6 md:pt-0 md:pl-10">
                            <h4 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4 border-b border-gray-200 pb-2">Otorisasi Serah Terima</h4>
                            
                            <div class="flex justify-between items-end gap-4">
                                <!-- Pihak Penyerah -->
                                <div class="w-1/2 text-center">
                                    <p class="text-xs text-gray-500 mb-8">Pihak Penyerah (Lapangan)</p>
                                    <div class="h-16 flex items-center justify-center">
                                        <span class="text-xs italic text-gray-400 bg-gray-50 px-2 py-1 rounded">Disubmit by System</span>
                                    </div>
                                    <p class="text-sm font-bold text-gray-900 mt-2 underline">{{ $wasteLb3->user->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $wasteLb3->created_at->format('d/m/Y H:i') }}</p>
                                </div>

                                <!-- Pihak TPS -->
                                <div class="w-1/2 text-center">
                                    <p class="text-xs text-gray-500 mb-2">Pihak Penerima (TPS LB3)</p>
                                    <div class="h-24 flex items-center justify-center border-b border-gray-300">
                                        @if($wasteLb3->status == 'Received' && $wasteLb3->signature_path)
                                            <!-- Menampilkan Gambar Tanda Tangan -->
                                            <img src="{{ asset('storage/' . $wasteLb3->signature_path) }}" alt="Tanda Tangan" class="max-h-full object-contain mix-blend-multiply">
                                        @else
                                            <span class="text-xs font-bold text-yellow-600 bg-yellow-50 px-2 py-1 rounded border border-yellow-200 animate-pulse">Belum Ditandatangani</span>
                                        @endif
                                    </div>
                                    <p class="text-sm font-bold text-gray-900 mt-2">{{ $wasteLb3->receiver_name ?? '(Belum Ada)' }}</p>
                                    <p class="text-xs text-gray-500">{{ $wasteLb3->received_at ? \Carbon\Carbon::parse($wasteLb3->received_at)->format('d/m/Y H:i') : '-' }}</p>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>