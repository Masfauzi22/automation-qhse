<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center">
            <a href="{{ route('daily-monitoring.index') }}" class="mr-4 text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Detail Pemantauan: {{ $dailyMonitoring->location }}</h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100">
                <div class="p-6 md:p-8">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Informasi Utama -->
                        <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
                            <h3 class="text-sm font-bold text-indigo-600 uppercase tracking-wider border-b border-gray-200 pb-2 mb-4">Parameter Lingkungan</h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <p class="text-xs text-gray-500 uppercase font-semibold">Tanggal Pantau</p>
                                    <p class="text-lg font-bold text-gray-900">{{ \Carbon\Carbon::parse($dailyMonitoring->date)->format('d F Y') }}</p>
                                </div>
                                
                                <div>
                                    <p class="text-xs text-gray-500 uppercase font-semibold">Kadar pH</p>
                                    <p class="text-3xl font-black {{ ($dailyMonitoring->ph_level >= 6 && $dailyMonitoring->ph_level <= 9) ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $dailyMonitoring->ph_level }}
                                        <span class="text-sm font-medium text-gray-500 block mt-1">Batas aman: 6.0 - 9.0</span>
                                    </p>
                                </div>

                                @if($dailyMonitoring->location !== 'Settling Pond 2')
                                <div>
                                    <p class="text-xs text-gray-500 uppercase font-semibold">Debit Air</p>
                                    <p class="text-xl font-bold text-gray-900">{{ $dailyMonitoring->debit_level }} <span class="text-sm text-gray-500">m³/jam</span></p>
                                </div>
                                @endif

                                <!-- Tambahan Informasi NaOH -->
                                <div>
                                    <p class="text-xs text-gray-500 uppercase font-semibold">Penggunaan Bahan Kimia</p>
                                    <p class="text-xl font-bold text-gray-900">
                                        {{ $dailyMonitoring->naoh_amount ?? 0 }} <span class="text-sm text-gray-500 font-medium">Karung NaOH</span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Informasi Log & Catatan -->
                        <div>
                            <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider border-b border-gray-200 pb-2 mb-4">Detail & Catatan</h3>
                            
                            <div class="mb-6">
                                <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Catatan Tambahan / Cuaca</p>
                                <div class="bg-yellow-50 border border-yellow-200 rounded p-3 text-sm text-gray-700 min-h-[80px]">
                                    {{ $dailyMonitoring->notes ?? 'Tidak ada catatan khusus.' }}
                                </div>
                            </div>

                            <div class="space-y-3 bg-white border border-gray-100 rounded-lg p-4 shadow-sm">
                                <div class="flex items-center text-sm">
                                    <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                    <span class="text-gray-500 w-24">Dicatat oleh</span>
                                    <span class="font-semibold text-gray-900">: {{ $dailyMonitoring->user->name }}</span>
                                </div>
                                <div class="flex items-center text-sm">
                                    <svg class="w-4 h-4 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <span class="text-gray-500 w-24">Dibuat pada</span>
                                    <span class="font-semibold text-gray-900">: {{ $dailyMonitoring->created_at->format('d M Y, H:i') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>