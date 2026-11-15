<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Reminder email Hazard Report setiap hari (jam & zona waktu diatur di config/hazard.php)
// Lokal: jalankan `php artisan schedule:work` di terminal terpisah.
// Vercel: tidak memakai ini, tapi Vercel Cron → /cron/hazard-reminders.
Schedule::command('hazard:send-reminders')
    ->dailyAt(config('hazard.notifications.reminder_time', '07:00'))
    ->timezone(config('hazard.notifications.reminder_timezone', 'Asia/Jakarta'));