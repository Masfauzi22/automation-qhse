<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Kategori & Parameter: ') }} <span class="text-indigo-600">{{ $samplingSubject->name }}</span>
            </h2>
            <a href="{{ route('sampling-subjects.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900 font-medium underline">
                &larr; Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100 p-8">
                
                <form action="{{ route('sampling-subjects.update', $samplingSubject->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <!-- Edit Nama Kategori -->
                    <div class="mb-6">
                        <label for="name" class="block text-sm font-bold text-gray-700 mb-2">Nama Kategori <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name', $samplingSubject->name) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 uppercase font-semibold" required>
                    </div>

                    <!-- Edit Mapping Parameter -->
                    <div class="mb-8 bg-gray-50 p-6 rounded-lg border border-gray-200">
                        <label class="block text-sm font-bold text-gray-800 mb-2">Pilih Parameter untuk Kategori Ini</label>
                        <p class="text-sm text-gray-500 mb-4 border-b border-gray-200 pb-4">Centang parameter yang akan dimunculkan saat user memilih kategori <b>{{ $samplingSubject->name }}</b> di form input.</p>
                        
                        <!-- Grid Checkbox 2 Kolom -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-h-96 overflow-y-auto pr-2">
                            @php
                                // Mengambil ID parameter yang sudah dicentang sebelumnya
                                $mappedParameterIds = $samplingSubject->parameters->pluck('id')->toArray();
                            @endphp

                            @forelse($parameters as $parameter)
                                <label class="inline-flex items-center w-full bg-white border border-gray-200 hover:bg-indigo-50 p-3 rounded-lg cursor-pointer transition shadow-sm">
                                    <input type="checkbox" name="parameters[]" value="{{ $parameter->id }}" 
                                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 w-5 h-5"
                                           {{ in_array($parameter->id, $mappedParameterIds) ? 'checked' : '' }}>
                                    <div class="ml-3">
                                        <span class="block text-sm font-bold text-gray-900">{{ $parameter->name }}</span>
                                        <span class="block text-xs text-gray-500">{{ $parameter->unit ?? '-' }} | {{ $parameter->method ?? '-' }}</span>
                                    </div>
                                </label>
                            @empty
                                <p class="text-sm text-red-500 col-span-2">Belum ada data parameter di sistem. Silakan buat parameter terlebih dahulu.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="flex justify-end gap-3">
                        <a href="{{ route('sampling-subjects.index') }}" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition">Batal</a>
                        <button type="submit" class="px-6 py-2 border border-transparent rounded-md shadow-sm text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 transition">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>