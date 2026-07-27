<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center">
            <a href="{{ route('waste-lb3.index') }}" class="mr-4 text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Request Penyerahan Limbah B3 ke TPS</h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100">
                
                <form action="{{ route('waste-lb3.store') }}" method="POST" class="p-6 md:p-8">
                    @csrf
                    
                    <div class="mb-6 pb-4 border-b border-gray-100 flex items-start">
                        <div class="bg-red-50 p-2 rounded-lg mr-3">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Identifikasi Limbah B3</h3>
                            <p class="text-sm text-gray-500 mt-1">Data yang disubmit akan berstatus "Menunggu Diterima". Petugas TPS wajib memberikan tanda tangan digital saat serah terima.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Jenis Limbah B3 <span class="text-red-500">*</span></label>
                            <input type="text" name="waste_type" value="{{ old('waste_type') }}" required class="shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md w-full" placeholder="cth: Oli Bekas, Majun Terkontaminasi, Filter Bekas">
                            @error('waste_type') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Sumber / Lokasi Asal <span class="text-red-500">*</span></label>
                            <input type="text" name="source_location" value="{{ old('source_location') }}" required class="shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md w-full" placeholder="cth: Workshop Alat Berat, Genset Blok B">
                            @error('source_location') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Jumlah / Volume <span class="text-red-500">*</span></label>
                            <input type="number" step="0.01" min="0" name="quantity" value="{{ old('quantity') }}" required class="shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md w-full text-lg font-bold" placeholder="0">
                            @error('quantity') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Satuan <span class="text-red-500">*</span></label>
                            <select name="unit" required class="shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md w-full">
                                <option value="Liter">Liter (L)</option>
                                <option value="Kilogram">Kilogram (Kg)</option>
                                <option value="Pcs">Pcs / Buah</option>
                                <option value="Drum">Drum</option>
                            </select>
                            @error('unit') <span class="text-xs text-red-500 mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="flex items-center justify-end pt-4 border-t border-gray-100">
                        <a href="{{ route('waste-lb3.index') }}" class="px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 transition mr-3">Batal</a>
                        <button type="submit" class="px-6 py-2 bg-indigo-600 border border-transparent rounded-md font-bold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">
                            Kirim Request
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>