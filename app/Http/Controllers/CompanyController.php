<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CompanyController extends Controller
{
    public function index()
    {
        // withCount: sekalian hitung jumlah user di tiap perusahaan
        $companies = Company::withCount('users')->orderBy('name')->get();

        return view('companies.index', compact('companies'));
    }

    public function create()
    {
        return view('companies.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255|unique:companies,name',
            'is_contractor' => 'nullable|boolean',
        ], [
            'name.required' => 'Nama perusahaan wajib diisi.',
            'name.unique'   => 'Nama perusahaan sudah terdaftar.',
        ]);

        Company::create([
            'name'          => trim($request->name),
            'is_contractor' => $request->boolean('is_contractor'),
        ]);

        return redirect()->route('companies.index')->with('success', 'Perusahaan berhasil ditambahkan!');
    }

    public function edit(Company $company)
    {
        return view('companies.edit', compact('company'));
    }

    public function update(Request $request, Company $company)
    {
        $request->validate([
            'name'          => ['required', 'string', 'max:255', Rule::unique('companies', 'name')->ignore($company->id)],
            'is_contractor' => 'nullable|boolean',
        ], [
            'name.required' => 'Nama perusahaan wajib diisi.',
            'name.unique'   => 'Nama perusahaan sudah terdaftar.',
        ]);

        $company->update([
            'name'          => trim($request->name),
            'is_contractor' => $request->boolean('is_contractor'),
        ]);

        return redirect()->route('companies.index')->with('success', 'Data perusahaan berhasil diperbarui!');
    }

    public function destroy(Company $company)
    {
        // Jangan hapus perusahaan yang masih dipakai user, supaya data pelapor tidak hilang
        $jumlahUser = $company->users()->count();

        if ($jumlahUser > 0) {
            return redirect()->route('companies.index')->withErrors([
                "Gagal! {$company->name} masih dipakai oleh {$jumlahUser} user. Pindahkan user tersebut ke perusahaan lain terlebih dahulu.",
            ]);
        }

        $company->delete();

        return redirect()->route('companies.index')->with('success', 'Perusahaan berhasil dihapus!');
    }
}