<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Department;
use App\Models\Company; // <-- BARU: data perusahaan
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        // Panggil relasi roles, department, dan company sekaligus
        $users = User::with(['roles', 'department', 'company'])->get(); 
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $roles       = Role::all();
        $departments = Department::all();
        $companies   = Company::orderBy('name')->get(); // <-- untuk dropdown perusahaan
        return view('users.create', compact('roles', 'departments', 'companies'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'required|string|email|max:255|unique:users',
            'password'        => 'required|string|min:8',
            'role'            => 'required',
            'department_id'   => 'nullable|exists:departments,id',
            'company_id'      => 'nullable|exists:companies,id',
            'employee_number' => 'nullable|string|max:50|unique:users,employee_number',
        ], [
            'employee_number.unique' => 'ID Karyawan sudah dipakai oleh user lain.',
        ]);

        $user = User::create([
            'name'            => $request->name,
            'email'           => $request->email,
            'password'        => Hash::make($request->password),
            'department_id'   => $request->department_id,
            'company_id'      => $request->company_id,
            'employee_number' => $request->employee_number,
        ]);

        $user->assignRole($request->role);

        return redirect()->route('users.index')->with('success', 'Karyawan / User berhasil ditambahkan!');
    }

    public function edit(User $user)
    {
        $roles       = Role::all();
        $departments = Department::all();
        $companies   = Company::orderBy('name')->get();
        return view('users.edit', compact('user', 'roles', 'departments', 'companies'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password'        => 'nullable|string|min:8', 
            'role'            => 'required',
            'department_id'   => 'nullable|exists:departments,id',
            'company_id'      => 'nullable|exists:companies,id',
            // Unik, kecuali milik user yang sedang diedit
            'employee_number' => ['nullable', 'string', 'max:50', Rule::unique('users', 'employee_number')->ignore($user->id)],
        ], [
            'employee_number.unique' => 'ID Karyawan sudah dipakai oleh user lain.',
        ]);

        $dataToUpdate = [
            'name'            => $request->name,
            'email'           => $request->email,
            'department_id'   => $request->department_id,
            'company_id'      => $request->company_id,
            'employee_number' => $request->employee_number,
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