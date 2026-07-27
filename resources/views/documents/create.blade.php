<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Upload Dokumen Baru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    @if ($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4 text-sm">
                            <strong>Gagal Mengunggah!</strong>
                            <ul class="mt-2 list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- ATTENTION: enctype="multipart/form-data" WAJIB ada untuk form upload file -->
                    <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf 

                        <!-- Input Nomor Dokumen -->
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Nomor Dokumen (Opsional)</label>
                            <input type="text" name="document_number" value="{{ old('document_number') }}" placeholder="Contoh: IBT/SOP/001" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>

                        <!-- Input Judul -->
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Judul Dokumen <span class="text-red-500">*</span></label>
                            <input type="text" name="title" value="{{ old('title') }}" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>

                        <!-- Pilihan Tipe Dokumen -->
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Kategori / Tipe Dokumen <span class="text-red-500">*</span></label>
                            <select name="type" required class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                <option value="">-- Pilih Kategori --</option>
                                <option value="Policy" {{ old('type') == 'Policy' ? 'selected' : '' }}>Policy</option>
                                <option value="SOP" {{ old('type') == 'SOP' ? 'selected' : '' }}>SOP</option>
                                <option value="Work Instruction" {{ old('type') == 'Work Instruction' ? 'selected' : '' }}>Work Instruction</option>
                                <option value="Form" {{ old('type') == 'Form' ? 'selected' : '' }}>Form</option>
                                <option value="Letter" {{ old('type') == 'Letter' ? 'selected' : '' }}>Letter</option>
                                <option value="HIRA" {{ old('type') == 'HIRA' ? 'selected' : '' }}>HIRA</option>
                            </select>
                        </div>

                        <!-- Input File Upload -->
                        <div class="mb-6">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Pilih File (PDF, Word, Excel) <span class="text-red-500">*</span></label>
                            <input type="file" name="file" required accept=".pdf,.doc,.docx,.xls,.xlsx" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            <p class="text-xs text-gray-500 mt-1">Maksimal ukuran file: 5MB.</p>
                        </div>

                        <!-- Tombol Submit -->
                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('documents.index') }}" class="text-gray-500 hover:text-gray-700 mr-4">Batal</a>
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Upload Dokumen
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>