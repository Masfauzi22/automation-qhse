<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center">
            <a href="{{ route('daily-monitoring.index') }}" class="mr-4 text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tambah Pemantauan Harian</h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100">
                
                <!-- x-data mengatur kondisi form agar debit hilang saat SP2 dipilih -->
                <form action="{{ route('daily-monitoring.store') }}" method="POST" class="p-6 md:p-8" x-data="{ location: '{{ old('location', 'Settling Pond 2') }}' }">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Tanggal Pantau <span class="text-red-500">*</span></label>
                            <input type="date" name="date" value="{{ old('date', date('Y-m-d')) }}" required class="shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md w-full">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Lokasi Titik Pantau <span class="text-red-500">*</span></label>
                            <select name="location" x-model="location" required class="shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md w-full bg-white">
                                <option value="Settling Pond 2">Settling Pond 2 </option>
                                <option value="Settling Pond 3">Settling Pond 3 </option>
                                <option value="Limbah Domestik">Limbah Domestik </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Kadar pH <span class="text-red-500">*</span></label>
                            <input type="number" step="0.01" name="ph_level" value="{{ old('ph_level') }}" required min="0" max="14" class="shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md w-full" placeholder="cth: 7.5">
                        </div>

                        <!-- Kolom Debit hanya muncul jika lokasi BUKAN Settling Pond 2 -->
                        <div x-show="location !== 'Settling Pond 2'" x-transition>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Debit Air (m³/jam) <span class="text-red-500">*</span></label>
                            <input type="number" step="0.01" name="debit_level" value="{{ old('debit_level') }}" :required="location !== 'Settling Pond 2'" class="shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md w-full" placeholder="cth: 120.5">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Penggunaan NaOH (Karung) <span class="text-gray-400 font-normal text-xs">(Opsional)</span></label>
                        <input type="number" name="naoh_amount" value="{{ old('naoh_amount', $dailyMonitoring->naoh_amount ?? '') }}" min="0" class="shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md w-full" placeholder="cth: 2">
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Catatan Tambahan / Kondisi Cuaca</label>
                        <textarea name="notes" rows="3" class="shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md w-full" placeholder="cth: Hujan deras di pagi hari...">{{ old('notes') }}</textarea>
                    </div>

                    <div class="flex items-center justify-end mt-4 pt-4 border-t border-gray-100">
                        <button type="submit" class="px-6 py-2 bg-indigo-600 border border-transparent rounded-md font-bold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 transition">
                            Simpan Data
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>