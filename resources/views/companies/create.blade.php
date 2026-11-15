<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tambah Perusahaan Baru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    @if ($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                            <strong>Gagal Menyimpan Data!</strong>
                            <ul class="mt-2 list-disc list-inside text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('companies.store') }}" method="POST">
                        @csrf

                        <!-- Nama Perusahaan -->
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Nama Perusahaan</label>
                            <input type="text" name="name" value="{{ old('name') }}" required maxlength="255" placeholder="Contoh: PT Angkasa Pura Supports" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            <p class="text-xs text-gray-500 mt-1">Tulis nama resmi lengkap, termasuk "PT", supaya tidak ada data dobel.</p>
                        </div>

                        <!-- Kontraktor -->
                        <div class="mb-4">
                            <input type="hidden" name="is_contractor" value="0">
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="is_contractor" value="1" {{ old('is_contractor') ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                <span class="text-sm font-bold text-gray-700">Perusahaan kontraktor / subkon</span>
                            </label>
                            <p class="text-xs text-gray-500 mt-1 ml-6">Biarkan tidak dicentang untuk perusahaan internal.</p>
                        </div>

                        <!-- Tombol -->
                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('companies.index') }}" class="text-gray-500 hover:text-gray-700 mr-4">Batal</a>
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Simpan Data
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>