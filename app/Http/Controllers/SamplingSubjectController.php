<?php

namespace App\Http\Controllers;

use App\Models\SamplingParameter;
use App\Models\SamplingSubject;
use Illuminate\Http\Request;

class SamplingSubjectController extends Controller
{
    public function index()
    {
        // Ubah withCount('parameters') menjadi with('parameters') agar datanya ditarik semua
        $subjects = SamplingSubject::with('parameters')->orderBy('name', 'asc')->get();
        return view('master_sampling.subjects.index', compact('subjects'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:sampling_subjects,name'
        ], [
            'name.unique' => 'Kategori Subject ini sudah ada di database.'
        ]);

        SamplingSubject::create([
            'name' => strtoupper($request->name) // Format otomatis jadi huruf besar
        ]);

        return redirect()->route('sampling-subjects.index')->with('success', 'Kategori Subject berhasil ditambahkan!');
    }

    public function destroy(SamplingSubject $samplingSubject)
    {
        // Mencegah penghapusan jika sudah ada transaksi sampling yang menggunakan subject ini
        if ($samplingSubject->envSamplings()->exists()) {
            return redirect()->route('sampling-subjects.index')->with('error', 'Kategori tidak bisa dihapus karena sudah digunakan dalam riwayat pengujian lab!');
        }

        $samplingSubject->delete();
        return redirect()->route('sampling-subjects.index')->with('success', 'Kategori Subject berhasil dihapus!');
    }

    public function edit(SamplingSubject $samplingSubject)
    {
        // Ambil semua daftar parameter untuk ditampilkan sebagai pilihan checkbox
        $parameters = SamplingParameter::orderBy('name', 'asc')->get();
        
        // Load data parameter yang saat ini sudah terhubung dengan subject ini
        $samplingSubject->load('parameters');
        
        return view('master_sampling.subjects.edit', compact('samplingSubject', 'parameters'));
    }

    public function update(Request $request, SamplingSubject $samplingSubject)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:sampling_subjects,name,' . $samplingSubject->id,
            'parameters' => 'nullable|array', // Array dari checkbox
            'parameters.*' => 'exists:sampling_parameters,id'
        ]);

        // 1. Update nama kategori
        $samplingSubject->update([
            'name' => strtoupper($request->name)
        ]);

        // 2. Update mapping parameternya secara cerdas dan aman!
        if ($request->has('parameters')) {
            $samplingSubject->parameters()->sync($request->parameters);
        } else {
            // Jika user mencabut SEMUA centang, kosongkan relasinya
            $samplingSubject->parameters()->detach();
        }

        return redirect()->route('sampling-subjects.index')->with('success', 'Kategori dan Mapping Parameter berhasil diperbarui!');
    }


}