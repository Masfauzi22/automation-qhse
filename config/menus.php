<?php

/*
|--------------------------------------------------------------------------
| Daftar Menu Aplikasi
|--------------------------------------------------------------------------
| Satu sumber untuk:
|   - Sidebar (resources/views/layouts/navigation.blade.php)
|   - Matriks Akses Menu (MenuAccessController)
|   - Pembuatan permission (MenuPermissionSeeder / halaman Akses Menu)
|
| Jenis item:
|   - Link tunggal : 'key' = nama permission. 'key' => null artinya selalu tampil untuk semua user.
|   - Grup         : punya 'children'. Permission ada di tiap child, grup tampil kalau minimal satu child diizinkan.
|                    'key' di grup hanya dipakai untuk memindahkan permission lama (menu induk) ke submenu.
|   - 'superadmin_only' => true : hanya superadmin, tidak bisa diatur di matriks.
|
| Child dengan 'route' => null = halaman belum dibuat (tampil label "Soon").
| Setelah menambah menu di sini, buka halaman Akses Menu supaya permission-nya otomatis dibuat.
*/

return [

    [
        'section' => 'Utama',
        'items' => [
            [
                'key'    => null, // selalu tampil (halaman pertama setelah login)
                'label'  => 'Dashboard',
                'route'  => 'dashboard',
                'active' => ['dashboard'],
                'icon'   => '<path d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/>',
            ],
            [
                'key'    => 'menu_documents',
                'label'  => 'Document Control',
                'route'  => 'documents.index',
                'active' => ['documents.*'],
                'icon'   => '<path d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>',
            ],
        ],
    ],

    [
        'section' => 'Administrasi',
        'items' => [
            [
                'key'             => 'menu_user_management',
                'label'           => 'Manajemen Akses',
                'superadmin_only' => true,
                'icon'            => '<path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>',
                'children'        => [
                    ['key' => null, 'label' => 'Data User',       'route' => 'users.index',       'active' => ['users.*']],
                    ['key' => null, 'label' => 'Data Role',       'route' => 'roles.index',       'active' => ['roles.*']],
                    ['key' => null, 'label' => 'Data Departemen', 'route' => 'departments.index', 'active' => ['departments.*']],
                    ['key' => null, 'label' => 'Data Perusahaan', 'route' => 'companies.index',   'active' => ['companies.*']],
                    ['key' => null, 'label' => 'Akses Menu',      'route' => 'menu-access.index', 'active' => ['menu-access.*']],
                ],
            ],
            [
                'key'      => 'menu_master_data',
                'label'    => 'Master Data',
                'icon'     => '<path d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125"/>',
                'children' => [
                    ['key' => 'menu_master_data.sampling_subject',   'label' => 'Kategori Subject', 'route' => 'sampling-subjects.index',   'active' => ['sampling-subjects.*']],
                    ['key' => 'menu_master_data.sampling_parameter', 'label' => 'Parameter Uji',    'route' => 'sampling-parameters.index', 'active' => ['sampling-parameters.*']],
                ],
            ],
        ],
    ],

    [
        'section' => 'Operasional',
        'items' => [
            [
                'key'      => 'menu_request_qhse',
                'label'    => 'Request QHSE',
                'icon'     => '<path d="M4 15a8 8 0 1116 0M2.5 16.5h19a1 1 0 011 1v.5a1 1 0 01-1 1h-19a1 1 0 01-1-1v-.5a1 1 0 011-1zM11 4v3M9 4h6"/>',
                'children' => [
                    ['key' => 'menu_request_qhse.fit_to_work',    'label' => 'Fit to Work',     'route' => 'fit-to-work.index', 'active' => ['fit-to-work.*']],
                    ['key' => 'menu_request_qhse.permit_to_work', 'label' => 'Permit to Work',  'route' => 'my-permit.index',   'active' => ['my-permit.*']],
                    ['key' => 'menu_request_qhse.ppe',            'label' => 'PPE (Equipment)', 'route' => null,                'active' => ['ppe.*']],
                    ['key' => 'menu_request_qhse.simper',         'label' => 'SIMPER',          'route' => 'simper.index',      'active' => ['simper.*']],
                ],
            ],
            [
                'key'      => 'menu_tasklist',
                'label'    => 'Tasklist',
                'icon'     => '<path d="M9 4.5h6a1 1 0 011 1V6h1a2 2 0 012 2v11a2 2 0 01-2 2H7a2 2 0 01-2-2V8a2 2 0 012-2h1V5.5a1 1 0 011-1zM9 12h6M9 15.5h6M9 8.5h6"/>',
                'children' => [
                    ['key' => 'menu_tasklist.hazard_report', 'label' => 'Hazard Report', 'route' => 'hazard-report.index', 'active' => ['hazard-report.*']],
                    ['key' => 'menu_tasklist.investigation', 'label' => 'Investigation', 'route' => null,                  'active' => ['investigation.*']],
                    ['key' => 'menu_tasklist.safety_campaign', 'label' => 'Safety Campaign', 'route' => 'safety-campaigns.index', 'active' => ['safety-campaigns.*']],
                    ['key' => 'menu_tasklist.permit_to_work',  'label' => 'Permit to Work',  'route' => 'permit.index',           'active' => ['permit.*']],
                ],
            ],
            [
                'key'      => 'menu_environmental',
                'label'    => 'Environmental',
                'icon'     => '<path d="M12 21c-4.5-1-8-4.5-8-9.5C4 6 8 3 12 3s8 3 8 8.5c0 5-3.5 8.5-8 9.5zM12 21V9m0 0c2.5-1.5 4-3.5 4-6M12 9c-2.5-1.5-4-3.5-4-6"/>',
                'children' => [
                    ['key' => 'menu_environmental.daily_monitoring', 'label' => 'Daily Monitoring',          'route' => 'daily-monitoring.index', 'active' => ['daily-monitoring.*']],
                    ['key' => 'menu_environmental.sampling',         'label' => 'Sampling',                  'route' => 'env-sampling.index',     'active' => ['env-sampling.*']],
                    ['key' => 'menu_environmental.waste_lb3',        'label' => 'Waste Management (LB3)',    'route' => 'waste-lb3.index',        'active' => ['waste-lb3.*']],
                    ['key' => 'menu_environmental.waste_domestic',   'label' => 'Waste Management (Sampah)', 'route' => 'waste-domestic.index',   'active' => ['waste-sampah.*', 'waste-domestic.*']],
                ],
            ],
        ],
    ],

    [
        'section' => 'Personal',
        'items' => [
            [
                'key'      => 'menu_personal_record',
                'label'    => 'Personal Record',
                'icon'     => '<path d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.964 0a9 9 0 10-11.964 0m11.964 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z"/>',
                'children' => [
                    ['key' => 'menu_personal_record.hazard_report', 'label' => 'Hazard Report', 'route' => 'my-hazard-report.index', 'active' => ['my-hazard-report.*']],
                    ['key' => 'menu_personal_record.investigation', 'label' => 'Investigation', 'route' => null,                     'active' => ['my-investigation.*']],
                    ['key' => 'menu_personal_record.safety_campaign', 'label' => 'Safety Campaign', 'route' => 'my-safety-campaign.index', 'active' => ['my-safety-campaign.*']],
                    ['key' => 'menu_personal_record.safety_talk',   'label' => 'Safety Talk',   'route' => null,                     'active' => ['safety-talk.*']],
                    ['key' => 'menu_personal_record.training',      'label' => 'Training',      'route' => null,                     'active' => ['training.*']],
                ],
            ],
        ],
    ],

];