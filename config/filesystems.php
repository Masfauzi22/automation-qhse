<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Lokal  : FILESYSTEM_DISK=public  (storage/app/public + php artisan storage:link)
    | Vercel : FILESYSTEM_DISK=s3      (Supabase Storage)
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => rtrim(env('APP_URL', 'http://localhost'), '/').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        // Dipakai untuk Supabase Storage (S3-compatible).
        // Nama variabel sengaja TIDAK memakai awalan AWS_ karena variabel AWS_*
        // dipakai/ditimpa oleh runtime Vercel sendiri.
        's3' => [
            'driver' => 's3',
            'key' => env('SUPABASE_S3_KEY'),
            'secret' => env('SUPABASE_S3_SECRET'),
            'region' => env('SUPABASE_S3_REGION', 'ap-southeast-1'),
            'bucket' => env('SUPABASE_S3_BUCKET', 'documents'),
            'url' => env('SUPABASE_S3_URL'),
            'endpoint' => env('SUPABASE_S3_ENDPOINT'),
            'use_path_style_endpoint' => true,

            // AWS SDK versi baru otomatis mengirim checksum tambahan yang kadang
            // ditolak layanan S3-compatible. Kirim checksum hanya jika memang diwajibkan.
            'request_checksum_calculation' => 'when_required',
            'response_checksum_validation' => 'when_required',

            // Tampilkan error asli kalau upload gagal (bukan diam-diam mengembalikan false)
            'throw' => true,
            // Catat error ke log (terlihat di Vercel -> Logs)
            'report' => true,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];