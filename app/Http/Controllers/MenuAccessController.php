<?php

namespace App\Http\Controllers;

use App\Support\MenuPermissions;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class MenuAccessController extends Controller
{
    public function index()
    {
        // Pastikan semua permission menu & submenu dari config/menus.php sudah ada
        MenuPermissions::sync();

        // Semua role KECUALI superadmin (superadmin otomatis bisa semua lewat Gate::before)
        $roles = Role::with('permissions')->where('name', '!=', 'superadmin')->orderBy('name')->get();

        // Permission yang dimiliki tiap role: [role_id => ['menu_x.y', ...]]
        $rolePermissions = $roles->mapWithKeys(fn ($role) => [
            $role->id => $role->permissions->pluck('name')->all(),
        ]);

        return view('menu_access.index', [
            'roles'           => $roles,
            'sections'        => config('menus', []),
            'rolePermissions' => $rolePermissions,
        ]);
    }

    public function update(Request $request)
    {
        $roles   = Role::where('name', '!=', 'superadmin')->get();
        $allowed = MenuPermissions::keys();
        $input   = $request->input('permissions', []);

        foreach ($roles as $role) {
            // Hanya permission yang memang terdaftar di config/menus.php yang diterima
            $checked = array_values(array_intersect(array_keys($input[$role->id] ?? []), $allowed));

            $role->syncPermissions($checked);
        }

        // WAJIB: bersihkan cache permission Spatie
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->back()->with('success', 'Hak Akses Menu berhasil diperbarui!');
    }
}