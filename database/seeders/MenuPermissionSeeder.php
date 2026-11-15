<?php

namespace Database\Seeders;

use App\Support\MenuPermissions;
use Illuminate\Database\Seeder;

class MenuPermissionSeeder extends Seeder
{
    /**
     * Buat permission menu & submenu dari config/menus.php,
     * dan pindahkan centangan menu induk lama ke submenunya.
     */
    public function run(): void
    {
        MenuPermissions::sync();
    }
}