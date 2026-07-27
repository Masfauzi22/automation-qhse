<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center">
            <a href="{{ route('waste-domestic.index') }}" class="mr-4 text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Detail Laporan Sampah: {{ $wasteDomestic->location }}</h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100 p-6 md:p-8">
                
                <div class="bg-gray-50 p-5 rounded-lg border border-gray-200 mb-8 grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <p class="text-xs text-gray-500 uppercase">Tanggal Pengangkutan</p>
                        <p class="font-bold text-gray-900">{{ \Carbon\Carbon::parse($wasteDomestic->collection_date)->format('d F Y') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase">Lokasi / Area</p>
                        <p class="font-bold text-gray-900">{{ $wasteDomestic->location }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase">Dicatat Oleh</p>
                        <p class="font-bold text-gray-900">{{ $wasteDomestic->user->name }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase">Total Volume Keseluruhan</p>
                        <p class="font-black text-indigo-700 text-lg">{{ $wasteDomestic->organic_volume + $wasteDomestic->anorganic_volume + $wasteDomestic->residue_volume }}</p>
                    </div>
                </div>

                <h3 class="text-md font-bold text-gray-900 mb-4 border-b border-gray-200 pb-2">Rincian Volume Terangkut</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">
                    <div class="bg-green-50 border border-green-200 rounded-lg p-6 text-center shadow-sm">
                        <p class="text-xs text-green-700 uppercase font-bold mb-2">Organik</p>
                        <p class="text-4xl font-black text-green-800">{{ $wasteDomestic->organic_volume }}</p>
                    </div>
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 text-center shadow-sm">
                        <p class="text-xs text-blue-700 uppercase font-bold mb-2">Anorganik</p>
                        <p class="text-4xl font-black text-blue-800">{{ $wasteDomestic->anorganic_volume }}</p>
                    </div>
                    <div class="bg-orange-50 border border-orange-200 rounded-lg p-6 text-center shadow-sm">
                        <p class="text-xs text-orange-700 uppercase font-bold mb-2">Residu</p>
                        <p class="text-4xl font-black text-orange-800">{{ $wasteDomestic->residue_volume }}</p>
                    </div>
                </div>

                <h3 class="text-md font-bold text-gray-900 mb-3 border-b border-gray-200 pb-2">Catatan Tambahan</h3>
                <div class="bg-yellow-50 border border-yellow-200 rounded p-4 text-sm text-gray-800 min-h-[100px]">
                    {{ $wasteDomestic->notes ?? 'Tidak ada catatan khusus pada periode ini.' }}
                </div>

            </div>
        </div>
    </div>
</x-app-layout>