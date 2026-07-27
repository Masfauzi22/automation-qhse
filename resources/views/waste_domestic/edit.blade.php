<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center">
            <a href="{{ route('waste-domestic.index') }}" class="mr-4 text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Data Sampah</h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100">
                <form action="{{ route('waste-domestic.update', $wasteDomestic->id) }}" method="POST" class="p-6 md:p-8">
                    @csrf
                    @method('PUT')
                    
                    <h3 class="text-md font-bold text-gray-900 border-b border-gray-200 pb-2 mb-6">Informasi Angkutan & Lokasi</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Tanggal Pengangkutan <span class="text-red-500">*</span></label>
                            <input type="date" name="collection_date" value="{{ old('collection_date', $wasteDomestic->collection_date) }}" required class="shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md w-full">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Kategori Lokasi <span class="text-red-500">*</span></label>
                            <select name="location" required class="shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md w-full">
                                <option value="Coal Terminal" {{ $wasteDomestic->location == 'Coal Terminal' ? 'selected' : '' }}>Coal Terminal</option>
                                <option value="Fuel Terminal" {{ $wasteDomestic->location == 'Fuel Terminal' ? 'selected' : '' }}>Fuel Terminal</option>
                            </select>
                        </div>
                    </div>

                    <h3 class="text-md font-bold text-gray-900 border-b border-gray-200 pb-2 mb-6">Rincian Volume Sampah</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">
                        <div class="bg-green-50 p-4 rounded-lg border border-green-200 text-center">
                            <label class="block text-xs font-bold text-green-800 uppercase mb-2">Organik <span class="text-red-500">*</span></label>
                            <input type="number" step="0.01" min="0" name="organic_volume" value="{{ old('organic_volume', $wasteDomestic->organic_volume) }}" required class="shadow-sm border-green-300 focus:border-green-500 rounded-md w-full text-center text-lg font-bold text-green-700">
                        </div>
                        <div class="bg-blue-50 p-4 rounded-lg border border-blue-200 text-center">
                            <label class="block text-xs font-bold text-blue-800 uppercase mb-2">Anorganik <span class="text-red-500">*</span></label>
                            <input type="number" step="0.01" min="0" name="anorganic_volume" value="{{ old('anorganic_volume', $wasteDomestic->anorganic_volume) }}" required class="shadow-sm border-blue-300 focus:border-blue-500 rounded-md w-full text-center text-lg font-bold text-blue-700">
                        </div>
                        <div class="bg-orange-50 p-4 rounded-lg border border-orange-200 text-center">
                            <label class="block text-xs font-bold text-orange-800 uppercase mb-2">Residu <span class="text-red-500">*</span></label>
                            <input type="number" step="0.01" min="0" name="residue_volume" value="{{ old('residue_volume', $wasteDomestic->residue_volume) }}" required class="shadow-sm border-orange-300 focus:border-orange-500 rounded-md w-full text-center text-lg font-bold text-orange-700">
                        </div>
                    </div>

                    <div class="mb-8">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Catatan Tambahan (Opsional)</label>
                        <textarea name="notes" rows="3" class="shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md w-full">{{ old('notes', $wasteDomestic->notes) }}</textarea>
                    </div>

                    <div class="flex items-center justify-end pt-4 border-t border-gray-100">
                        <a href="{{ route('waste-domestic.index') }}" class="px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 transition mr-3">Batal</a>
                        <button type="submit" class="px-6 py-2 bg-indigo-600 border border-transparent rounded-md font-bold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">
                            Perbarui Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>