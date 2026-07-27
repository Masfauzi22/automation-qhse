<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    // Menampilkan daftar departemen
    public function index()
    {
        $departments = Department::latest()->get();
        return view('departments.index', compact('departments'));
    }

    // Menampilkan form tambah
    public function create()
    {
        return view('departments.create');
    }

    // Menyimpan data ke database
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:departments,name',
        ], [
            'name.unique' => 'Nama departemen ini sudah terdaftar.'
        ]);

        Department::create([
            'name' => strtoupper($request->name), // Otomatis dikapitalisasi
        ]);

        return redirect()->route('departments.index')->with('success', 'Departemen berhasil ditambahkan!');
    }

    // Menampilkan form edit
    public function edit(Department $department)
    {
        return view('departments.edit', compact('department'));
    }

    // Memproses perubahan data
    public function update(Request $request, Department $department)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:departments,name,' . $department->id,
        ], [
            'name.unique' => 'Nama departemen ini sudah terdaftar.'
        ]);

        $department->update([
            'name' => strtoupper($request->name),
        ]);

        return redirect()->route('departments.index')->with('success', 'Departemen berhasil diperbarui!');
    }

    // Menghapus data
    public function destroy(Department $department)
    {
        // Cek apakah ada user yang sedang menggunakan departemen ini
        if ($department->users()->count() > 0) {
            return redirect()->route('departments.index')->withErrors(['Gagal! Departemen ini sedang digunakan oleh user dan tidak bisa dihapus.']);
        }

        $department->delete();
        return redirect()->route('departments.index')->with('success', 'Departemen berhasil dihapus!');
    }
}