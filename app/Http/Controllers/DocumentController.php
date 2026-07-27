<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class DocumentController extends Controller
{
    // Menampilkan daftar dokumen
    public function index()
    {
        // Ambil semua dokumen berserta data user yang upload, urutkan dari yang terbaru
        $documents = Document::with('uploader')->latest()->get();
        return view('documents.index', compact('documents'));
    }

    // Menampilkan form upload dokumen baru
    public function create()
    {
        return view('documents.create');
    }

    // Memproses data dan file yang di-upload
    public function store(Request $request)
    {
        // 1. Validasi inputan form
        $request->validate([
            'document_number' => 'nullable|string|max:255',
            'title' => 'required|string|max:255',
            'type' => 'required|in:Policy,SOP,Work Instruction,Form,Letter,HIRA',
            // File wajib diisi, maksimal 5MB (5120 Kilobytes), format khusus dokumen
            'file' => 'required|mimes:pdf,doc,docx,xls,xlsx|max:5120', 
        ]);

        // 2. Simpan file fisik ke folder 'storage/app/public/documents'
        $filePath = $request->file('file')->store('documents', 'public');

        // 3. Simpan nama dan lokasi file ke database
        Document::create([
            'document_number' => $request->document_number,
            'title' => $request->title,
            'type' => $request->type,
            'file_path' => $filePath,
            'uploaded_by' => Auth::id(), // ID user yang sedang login
        ]);

        // 4. Kembali ke halaman utama
        return redirect()->route('documents.index')->with('success', 'Dokumen berhasil diunggah!');
    }

    // (Fungsi Edit, Update, dan Destroy akan kita buat nanti setelah form Create berhasil)
    // Menampilkan form edit
    public function edit(Document $document)
    {
        return view('documents.edit', compact('document'));
    }

    // Memproses update data dan file
    public function update(Request $request, Document $document)
    {
        $request->validate([
            'document_number' => 'nullable|string|max:255',
            'title' => 'required|string|max:255',
            'type' => 'required|in:Policy,SOP,Work Instruction,Form,Letter,HIRA',
            'file' => 'nullable|mimes:pdf,doc,docx,xls,xlsx|max:5120', // File opsional saat edit
        ]);

        $dataToUpdate = [
            'document_number' => $request->document_number,
            'title' => $request->title,
            'type' => $request->type,
        ];

        // Jika user mengupload file baru untuk merevisi dokumen
        if ($request->hasFile('file')) {
            // 1. Hapus file PDF lama dari folder storage
            if (Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }
            // 2. Simpan file PDF yang baru
            $dataToUpdate['file_path'] = $request->file('file')->store('documents', 'public');
        }

        $document->update($dataToUpdate);

        return redirect()->route('documents.index')->with('success', 'Dokumen berhasil diperbarui!');
    }

    // Menghapus dokumen
    public function destroy(Document $document)
    {
        // 1. Hapus file fisik PDF-nya dari folder storage
        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }
        
        // 2. Hapus datanya dari database
        $document->delete();

        return redirect()->route('documents.index')->with('success', 'Dokumen beserta file-nya berhasil dihapus!');
    }
}