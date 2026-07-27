<?php

namespace App\Http\Controllers;

use App\Models\SamplingParameter;
use App\Models\SamplingSubject;
use Illuminate\Http\Request;

class SamplingParameterController extends Controller
{
    public function index()
    {
        // Ambil data parameter beserta relasi subject-nya
        $parameters = SamplingParameter::with('subjects')->orderBy('name', 'asc')->get();
        
        // Ambil data subject untuk dimunculkan sebagai Checkbox di form
        $subjects = SamplingSubject::orderBy('name', 'asc')->get();
        
        return view('master_sampling.parameters.index', compact('parameters', 'subjects'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'unit' => 'nullable|string|max:50',
            'method' => 'nullable|string|max:255',
            'subjects' => 'nullable|array', // Validasi inputan checkbox
            'subjects.*' => 'exists:sampling_subjects,id'
        ]);

        // 1. Simpan data parameternya
        $parameter = SamplingParameter::create([
            'name' => $request->name,
            'unit' => $request->unit,
            'method' => $request->method,
        ]);

        // 2. Hubungkan (Mapping) dengan Kategori yang dicentang
        if ($request->has('subjects')) {
            $parameter->subjects()->sync($request->subjects);
        }

        return redirect()->route('sampling-parameters.index')->with('success', 'Parameter & Mapping berhasil ditambahkan!');
    }

    public function destroy(SamplingParameter $samplingParameter)
    {
        // Cek jika parameter ini sudah pernah dipakai di inputan hasil lab
        if ($samplingParameter->envSamplingResults()->exists()) {
            return redirect()->route('sampling-parameters.index')->with('error', 'Parameter tidak bisa dihapus karena sudah dipakai dalam data hasil uji lab!');
        }

        // Hapus relasi mapping-nya dulu, baru hapus parameternya
        $samplingParameter->subjects()->detach();
        $samplingParameter->delete();

        return redirect()->route('sampling-parameters.index')->with('success', 'Parameter berhasil dihapus!');
    }
}