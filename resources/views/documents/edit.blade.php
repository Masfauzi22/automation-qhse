<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Dokumen') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    @if ($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4 text-sm">
                            <strong>Gagal Menyimpan!</strong>
                            <ul class="mt-2 list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Tambahkan @method('PUT') untuk proses Edit -->
                    <form action="{{ route('documents.update', $document->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf 
                        @method('PUT')

                        <!-- Input Nomor Dokumen -->
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Nomor Dokumen (Opsional)</label>
                            <input type="text" name="document_number" value="{{ old('document_number', $document->document_number) }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>

                        <!-- Input Judul -->
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Judul Dokumen <span class="text-red-500">*</span></label>
                            <input type="text" name="title" value="{{ old('title', $document->title) }}" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>

                        <!-- Pilihan Tipe Dokumen -->
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Kategori / Tipe Dokumen <span class="text-red-500">*</span></label>
                            <select name="type" required class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                                <option value="Policy" {{ $document->type == 'Policy' ? 'selected' : '' }}>Policy</option>
                                <option value="SOP" {{ $document->type == 'SOP' ? 'selected' : '' }}>SOP</option>
                                <option value="Work Instruction" {{ $document->type == 'Work Instruction' ? 'selected' : '' }}>Work Instruction</option>
                                <option value="Form" {{ $document->type == 'Form' ? 'selected' : '' }}>Form</option>
                                <option value="Letter" {{ $document->type == 'Letter' ? 'selected' : '' }}>Letter</option>
                                <option value="HIRA" {{ $document->type == 'HIRA' ? 'selected' : '' }}>HIRA</option>
                            </select>
                        </div>

                        <!-- Input File Upload (Opsional) -->
                        <div class="mb-6 p-4 border border-gray-200 bg-gray-50 rounded">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Ganti File Dokumen (Opsional)</label>
                            <p class="text-xs text-gray-600 mb-3">Abaikan form ini jika tidak ingin mengubah file PDF/Word yang sudah ada.</p>
                            <input type="file" name="file" accept=".pdf,.doc,.docx,.xls,.xlsx" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        </div>

                        <!-- Tombol Submit -->
                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('documents.index') }}" class="text-gray-500 hover:text-gray-700 mr-4">Batal</a>
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Update Dokumen
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>