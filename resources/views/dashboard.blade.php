<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard AQHSE') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Section 1: Ucapan Selamat Datang -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100 p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h3 class="text-2xl font-bold text-gray-900">Selamat datang, <span class="text-indigo-600">{{ Auth::user()->name ?? 'User' }}</span>! 👋</h3>
                    <p class="text-sm text-gray-500 mt-1">Berikut adalah ringkasan aktivitas Health, Safety & Environment (HSE) hari ini.</p>
                </div>
                <div class="text-left md:text-right bg-indigo-50 px-4 py-2 rounded-lg border border-indigo-100">
                    <p class="text-xs font-bold text-indigo-800 uppercase tracking-wider">Tanggal Hari Ini</p>
                    <p class="text-sm font-medium text-indigo-900">{{ now()->translatedFormat('l, d F Y') }}</p>
                </div>
            </div>

            <!-- Section 2: Grid Kartu Statistik (Data Dummy Sementara) -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                
                <!-- Kartu: Hazard Report -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center gap-4 hover:shadow-md transition">
                    <div class="p-3 bg-red-50 text-red-600 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Open Hazard</p>
                        <h4 class="text-2xl font-bold text-gray-900">12</h4>
                    </div>
                </div>

                <!-- Kartu: Permit to Work -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center gap-4 hover:shadow-md transition">
                    <div class="p-3 bg-amber-50 text-amber-600 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Active PTW</p>
                        <h4 class="text-2xl font-bold text-gray-900">8</h4>
                    </div>
                </div>

                <!-- Kartu: Environmental Sampling -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center gap-4 hover:shadow-md transition">
                    <div class="p-3 bg-green-50 text-green-600 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Sampling Lab</p>
                        <h4 class="text-2xl font-bold text-gray-900">24</h4>
                    </div>
                </div>

                <!-- Kartu: Dokumen Pending -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center gap-4 hover:shadow-md transition">
                    <div class="p-3 bg-blue-50 text-blue-600 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Doc. Pending</p>
                        <h4 class="text-2xl font-bold text-gray-900">5</h4>
                    </div>
                </div>

            </div>

            <!-- Section 3: Quick Actions & Status -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Quick Access / Jalan Pintas -->
                <div class="lg:col-span-1 bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-bold text-gray-900 border-b border-gray-200 pb-2 mb-4">Jalan Pintas</h3>
                    <div class="space-y-3">
                        <a href="{{ route('env-sampling.index') }}" class="flex items-center justify-between p-3 bg-gray-50 hover:bg-indigo-50 border border-gray-100 rounded-lg transition group">
                            <span class="text-sm font-medium text-gray-700 group-hover:text-indigo-700">Input Data Sampling</span>
                            <span class="text-gray-400 group-hover:text-indigo-500">&rarr;</span>
                        </a>
                        <a href="{{ route('documents.index') }}" class="flex items-center justify-between p-3 bg-gray-50 hover:bg-indigo-50 border border-gray-100 rounded-lg transition group">
                            <span class="text-sm font-medium text-gray-700 group-hover:text-indigo-700">Upload Dokumen Baru</span>
                            <span class="text-gray-400 group-hover:text-indigo-500">&rarr;</span>
                        </a>
                        <a href="#" class="flex items-center justify-between p-3 bg-gray-50 hover:bg-indigo-50 border border-gray-100 rounded-lg transition group">
                            <span class="text-sm font-medium text-gray-700 group-hover:text-indigo-700">Lapor Hazard (Bahaya)</span>
                            <span class="text-gray-400 group-hover:text-indigo-500">&rarr;</span>
                        </a>
                    </div>
                </div>

                <!-- Info Panel (Bisa untuk grafik/log aktivitas) -->
                <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col justify-center items-center text-center">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <h4 class="text-gray-800 font-bold text-lg mb-1">Area Grafik / Aktivitas</h4>
                    <p class="text-gray-500 text-sm max-w-md">Nantinya area ini bisa kita isi dengan grafik tren limbah, tren parameter lab, atau daftar riwayat persetujuan dokumen terbaru.</p>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>