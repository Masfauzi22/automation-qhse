<?php

/*
|--------------------------------------------------------------------------
| Google Sheets (dashboard Hazard Report)
|--------------------------------------------------------------------------
| Kunci service account dibaca dengan urutan:
|   1. GOOGLE_SERVICE_ACCOUNT_JSON  → isi file JSON, boleh di-base64 (dipakai di Vercel)
|   2. GOOGLE_SERVICE_ACCOUNT_FILE  → lokasi file JSON (default untuk laptop)
|
| Spreadsheet harus di-share (Viewer) ke email service account.
*/

return [

    'service_account' => [
        'json' => env('GOOGLE_SERVICE_ACCOUNT_JSON'),
        'file' => env('GOOGLE_SERVICE_ACCOUNT_FILE', storage_path('app/google/service-account.json')),
    ],

    'hazard_sheet' => [
        'spreadsheet_id' => env('HAZARD_SHEET_ID', '180yi4d8Sed-oZWUs2qs_ymON7k7bcodHVfyA-9uqA4o'),
        'tab'            => env('HAZARD_SHEET_TAB', 'DASHBOARD HAZARD REPORT 2026'),
        'tahun'          => (int) env('HAZARD_SHEET_YEAR', 2026),

        // Data disimpan sementara supaya dashboard cepat dan tidak membebani Google
        'cache_minutes'  => (int) env('HAZARD_SHEET_CACHE_MINUTES', 10),

        // Kalau Google gagal diakses, jangan coba lagi selama sekian menit (halaman tidak ikut lambat)
        'retry_after_failure_minutes' => 2,

        // Target % close (konfirmasi ke tim HSE)
        'target_close'   => 85,
    ],

];