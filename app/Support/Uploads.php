<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

/**
 * Tempat simpan file upload (foto hazard, poster campaign, dll).
 * Mengikuti FILESYSTEM_DISK di .env, sama seperti Document Control:
 *  - Laptop : FILESYSTEM_DISK=public  (storage/app/public + php artisan storage:link)
 *  - Vercel : FILESYSTEM_DISK=s3      (Supabase Storage)
 */
class Uploads
{
    public static function disk(): string
    {
        return config('filesystems.default');
    }

    // URL file untuk <img src="..."> / link download
    public static function url(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        $disk = static::disk();

        // Disk lokal: pakai asset() supaya ikut alamat yang sedang dibuka (127.0.0.1 / localhost)
        if (config("filesystems.disks.{$disk}.driver") === 'local') {
            return asset('storage/' . ltrim($path, '/'));
        }

        // Supabase / S3: SUPABASE_S3_URL + path
        return Storage::disk($disk)->url($path);
    }

    // Hapus file; kalau storage error, cukup dicatat di log tanpa menggagalkan proses
    public static function delete(array|string|null $paths): void
    {
        $paths = array_values(array_filter((array) $paths));
        if (! $paths) {
            return;
        }

        try {
            Storage::disk(static::disk())->delete($paths);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}