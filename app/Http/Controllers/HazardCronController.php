<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

/**
 * Dipanggil oleh Vercel Cron setiap hari (Vercel tidak bisa menjalankan `schedule:work`).
 * Vercel mengirim header "Authorization: Bearer <CRON_SECRET>".
 * Tes manual (kirim ulang walau hari ini sudah terkirim): tambahkan ?force=1
 */
class HazardCronController extends Controller
{
    public function __invoke(Request $request)
    {
        $secret = config('hazard.notifications.cron_secret');

        abort_if(blank($secret) || ! hash_equals('Bearer ' . $secret, (string) $request->header('Authorization')), 403);

        Artisan::call('hazard:send-reminders', $request->boolean('force') ? ['--force' => true] : []);

        return response()->json([
            'ok'     => true,
            'output' => trim(Artisan::output()),
        ]);
    }
}