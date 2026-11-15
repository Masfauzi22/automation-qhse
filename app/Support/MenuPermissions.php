<?php

namespace App\Support;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

/**
 * Bantuan permission menu, membaca daftar menu dari config/menus.php.
 */
class MenuPermissions
{
    /**
     * Semua nama permission yang bisa diatur di matriks Akses Menu.
     * Menu "superadmin_only" dan menu "selalu tampil" (key null) tidak termasuk.
     */
    public static function keys(): array
    {
        $keys = [];

        foreach (config('menus', []) as $section) {
            foreach ($section['items'] as $item) {
                if (! empty($item['superadmin_only'])) {
                    continue;
                }

                if (! empty($item['children'])) {
                    foreach ($item['children'] as $child) {
                        if (! empty($child['key'])) {
                            $keys[] = $child['key'];
                        }
                    }
                } elseif (! empty($item['key'])) {
                    $keys[] = $item['key'];
                }
            }
        }

        return array_values(array_unique($keys));
    }

    /**
     * 1. Buat permission yang belum ada di database.
     * 2. Pindahkan permission menu induk lama (misal "menu_tasklist") ke semua submenunya,
     *    lalu hapus permission induk lama itu. Aman dijalankan berkali-kali.
     */
    public static function sync(): void
    {
        $registrar = app(PermissionRegistrar::class);

        foreach (self::keys() as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }
        $registrar->forgetCachedPermissions();

        foreach (config('menus', []) as $section) {
            foreach ($section['items'] as $item) {
                if (empty($item['children']) || empty($item['key'])) {
                    continue;
                }

                $old = Permission::where('name', $item['key'])->where('guard_name', 'web')->first();
                if (! $old) {
                    continue; // sudah pernah dipindahkan
                }

                $childKeys = empty($item['superadmin_only'])
                    ? collect($item['children'])->pluck('key')->filter()->values()->all()
                    : [];

                foreach ($old->roles as $role) {
                    if ($childKeys) {
                        $role->givePermissionTo($childKeys);
                    }
                }

                $old->delete();
            }
        }

        $registrar->forgetCachedPermissions();
    }
}