<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Department; // <-- TAMBAHAN BARU
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        // Panggil relasi roles dan department sekaligus
        $users = User::with(['roles', 'department'])->get(); 
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::all();
        $departments = Department::all(); // <-- Panggil data departemen untuk dropdown
        return view('users.create', compact('roles', 'departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|string|email|max:255|unique:users',
            'password'      => 'required|string|min:8',
            'role'          => 'required',
            'department_id' => 'nullable|exists:departments,id' // Validasi Foreign Key
        ]);

        $user = User::create([
            'name'          => $request->name,
            'email'         => $request->email,
            'password'      => Hash::make($request->password),
            'department_id' => $request->department_id,
        ]);

        $user->assignRole($request->role);

        return redirect()->route('users.index')->with('success', 'Karyawan / User berhasil ditambahkan!');
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        $departments = Department::all(); 
        return view('users.edit', compact('user', 'roles', 'departments'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password'      => 'nullable|string|min:8', 
            'role'          => 'required',
            'department_id' => 'nullable|exists:departments,id'
        ]);

        $dataToUpdate = [
            'name'          => $request->name,
            'email'         => $request->email,
            'department_id' => $request->department_id,
        ];

        if ($request->filled('password')) {
            $dataToUpdate['password'] = Hash::make($request->password);
        }

        $user->update($dataToUpdate);
        $user->syncRoles($request->role);

        return redirect()->route('users.index')->with('success', 'Data Karyawan berhasil diperbarui!');
    }

    public function destroy(User $user)
    {
        if ($user->hasRole('superadmin')) {
            return redirect()->route('users.index')->withErrors(['Gagal! Akun Superadmin tidak boleh dihapus.']);
        }
        
        $user->delete();
        return redirect()->route('users.index')->with('success', 'Data Karyawan berhasil dihapus!');
    }
}