<?php

namespace App\Http\Controllers;

use App\Models\EnvSampling;
use App\Models\EnvSamplingResult;
use App\Models\SamplingSubject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnvSamplingController extends Controller
{
    public function index()
    {
        // Menampilkan riwayat laporan sampling yang sudah pernah diinput
        $samplings = EnvSampling::with(['subject', 'user'])->orderBy('sampling_date', 'desc')->get();
        return view('env_sampling.index', compact('samplings'));
    }

    public function create()
    {
        // Mengirim daftar "Subject" (Raw Water, Waste Water, dll) ke dropdown form
        $subjects = SamplingSubject::orderBy('name', 'asc')->get();
        return view('env_sampling.create', compact('subjects'));
    }

    // FUNGSI KHUSUS UNTUK AJAX (Dipanggil saat user memilih dropdown Subject)
    public function getParameters($id)
    {
        $subject = SamplingSubject::with('parameters')->findOrFail($id);
        
        // Mengembalikan daftar parameter (dalam bentuk JSON) sesuai subject yang dipilih
        return response()->json($subject->parameters);
    }

    public function store(Request $request)
    {
        // 1. Validasi Inputan User
        $request->validate([
            'sampling_subject_id' => 'required|exists:sampling_subjects,id',
            'sample_mark' => 'required|string|max:255',
            'coordinate' => 'nullable|string|max:255',
            'sampling_date' => 'required|date',
            'sampling_time' => 'nullable', // Boleh kosong
            'description' => 'nullable|string',
            'attachment_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // Maks 5MB
            'results' => 'required|array',
            'results.*' => 'required|string' // Setiap kolom parameter wajib diisi
        ]);

        // 2. Proses Upload Dokumen Bukti (Disimpan ke storage/app/public/sampling_attachments)
        $filePath = null;
        if ($request->hasFile('attachment_file')) {
            $filePath = $request->file('attachment_file')->store('sampling_attachments', 'public');
        }

        // 3. Simpan Data Induk (Informasi Kop Surat)
        $envSampling = EnvSampling::create([
            'sampling_subject_id' => $request->sampling_subject_id,
            'user_id' => Auth::id(), // Menyimpan ID user yang sedang login
            'sampling_date' => $request->sampling_date,
            'sampling_time' => $request->sampling_time,
            'sample_mark' => $request->sample_mark,
            'coordinate' => $request->coordinate,
            'description' => $request->description,
            'attachment_file_path' => $filePath,
        ]);

        // 4. Simpan Data Detail (Array Hasil Parameter dari Form AJAX)
        foreach ($request->results as $parameterId => $resultValue) {
            EnvSamplingResult::create([
                'env_sampling_id' => $envSampling->id,
                'sampling_parameter_id' => $parameterId,
                'result_value' => $resultValue,
            ]);
        }

        // 5. Redirect ke halaman Index dengan pesan sukses
        return redirect()->route('env-sampling.index')->with('success', 'Data hasil uji lab berhasil disimpan secara otomatis beserta seluruh parameternya!');
    }

    public function show(EnvSampling $envSampling)
    {
        // Tarik data sampling beserta relasi ke user, subject, dan hasil detail parameternya
        $envSampling->load(['subject', 'user', 'results.parameter']);
        
        return view('env_sampling.show', compact('envSampling'));
    }
}