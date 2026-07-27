<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class MenuAccessController extends Controller
{
    public function index()
    {
        // Ambil semua role KECUALI superadmin (karena superadmin otomatis bisa melihat semua)
        $roles = Role::where('name', '!=', 'superadmin')->get();

        // Daftar menu utama disamakan dengan @can di navigation.blade.php
        $menus = [
            'menu_documents'       => 'Menu Document Control',
            'menu_user_management' => 'Menu Manajemen Akses (User, Role, Dept)',
            'menu_master_data'     => 'Menu Master Data (Subject & Parameter)',
            'menu_request_qhse'    => 'Menu Request QHSE (SIMPER, FTW, dll)',
            'menu_tasklist'        => 'Menu Tasklist (Hazard, Investigation)',
            'menu_environmental'   => 'Menu Environmental Monitoring',
            'menu_personal_record' => 'Menu Personal Record',
        ];

        // Looping otomatis untuk membuat permission di database jika belum ada
        foreach (array_keys($menus) as $permissionName) {
            Permission::firstOrCreate(
                ['name' => $permissionName],
                ['guard_name' => 'web']
            );
        }

        return view('menu_access.index', compact('roles', 'menus'));
    }

    public function update(Request $request)
    {
        // Ambil semua role kecuali superadmin
        $roles = Role::where('name', '!=', 'superadmin')->get();

        // Data checkbox dari form
        $permissionsData = $request->input('permissions', []);

        foreach ($roles as $role) {
            // Ambil daftar permission yang dicentang untuk role ini
            $checkedPermissions = isset($permissionsData[$role->id]) ? array_keys($permissionsData[$role->id]) : [];

            // Sinkronisasi permission ke role (Otomatis menghapus yang tidak dicentang, dan menambah yang dicentang)
            $role->syncPermissions($checkedPermissions);
        }

        // WAJIB: paksa clear cache permission Spatie
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return redirect()->back()->with('success', 'Hak Akses Menu berhasil diperbarui!');
    }
}