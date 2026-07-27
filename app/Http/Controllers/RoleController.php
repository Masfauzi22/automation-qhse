<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    // Menampilkan daftar Role
    public function index()
    {
        $roles = Role::all();
        return view('roles.index', compact('roles'));
    }

    // Menyimpan Role baru
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name'
        ]);

        // Simpan dengan format huruf kecil agar seragam di database
        Role::create(['name' => strtolower($request->name)]);

        return redirect()->route('roles.index')->with('success', 'Role baru berhasil ditambahkan!');
    }

    // Menghapus Role
    public function destroy(Role $role)
    {
        if ($role->name === 'superadmin') {
            return redirect()->route('roles.index')->withErrors(['Gagal! Role Superadmin tidak boleh dihapus.']);
        }

        $role->delete();
        return redirect()->route('roles.index')->with('success', 'Role berhasil dihapus!');
    }
}