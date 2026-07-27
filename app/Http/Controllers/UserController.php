<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('roles')->get(); 
        return view('users.index', compact('users'));
    }

    // Fungsi untuk menampilkan form tambah user
    public function create()
    {
        $roles = Role::all();
        return view('users.create', compact('roles'));
    }

    // Fungsi untuk menyimpan data user baru ke database
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole($request->role);

        return redirect()->route('users.index')->with('success', 'Karyawan / User berhasil ditambahkan!');
    }

    // Menampilkan form edit
    public function edit(User $user)
    {
        $roles = Role::all();
        return view('users.edit', compact('user', 'roles'));
    }

    // Memproses perubahan data
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8', 
            'role' => 'required'
        ]);

        // Kumpulkan data yang pasti diubah
        $dataToUpdate = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        // Jika password diisi, tambahkan ke dalam array data
        if ($request->filled('password')) {
            $dataToUpdate['password'] = Hash::make($request->password);
        }

        // Eksekusi update
        $user->update($dataToUpdate);

        // Update role
        $user->syncRoles($request->role);

        return redirect()->route('users.index')->with('success', 'Data Karyawan berhasil diperbarui!');
    }

    // Menghapus data user
    public function destroy(User $user)
    {
        // Proteksi agar Superadmin utama tidak tidak sengaja terhapus
        if ($user->hasRole('superadmin')) {
            return redirect()->route('users.index')->withErrors(['Gagal! Akun Superadmin tidak boleh dihapus.']);
        }
        
        $user->delete();
        return redirect()->route('users.index')->with('success', 'Data Karyawan berhasil dihapus!');
    }
}