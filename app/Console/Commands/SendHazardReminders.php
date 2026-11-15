<?php

namespace App\Console\Commands;

use App\Models\HazardReport;
use App\Notifications\HazardSummaryMail;
use App\Services\HazardNotifier;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

/**
 * Reminder harian Hazard Report:
 *  - ke PIC  : temuan departemennya yang lewat batas waktu / jatuh tempo dalam 1 hari
 *  - ke HSE  : antrean validasi yang lama, bukti yang menunggu verifikasi, dan daftar terlambat
 *
 * Jalankan manual : php artisan hazard:send-reminders
 * Paksa kirim lagi: php artisan hazard:send-reminders --force
 * Otomatis        : dijadwalkan di routes/console.php (lokal) atau Vercel Cron (produksi)
 */
class SendHazardReminders extends Command
{
    protected $signature = 'hazard:send-reminders {--force : Kirim walau hari ini sudah pernah dikirim}';

    protected $description = 'Kirim email reminder Hazard Report ke PIC dan HSE';

    private const MAX_ITEMS = 20; // maksimal laporan yang ditulis di satu email

    public function handle(HazardNotifier $notifier): int
    {
        if (! config('hazard.notifications.enabled') || ! config('hazard.notifications.events.daily_reminder', true)) {
            $this->warn('Email Hazard Report sedang dimatikan di config/hazard.php.');
            return self::SUCCESS;
        }

        // Cegah terkirim dua kali di hari yang sama (mis. cron terpanggil ulang)
        $lockKey = 'hazard-reminders:' . today()->toDateString();
        if (! $this->option('force') && ! Cache::add($lockKey, now()->toDateTimeString(), now()->endOfDay())) {
            $this->info('Reminder hari ini sudah dikirim. Pakai --force untuk mengirim ulang.');
            return self::SUCCESS;
        }

        $daysBefore = (int) config('hazard.notifications.remind_days_before', 1);

        // Semua laporan yang masih ditunggu tindakan PIC dan sudah/hampir jatuh tempo
        $followUps = HazardReport::with(['reporterCompany', 'category', 'priority', 'picDepartment'])
            ->whereIn('status', HazardReport::FOLLOW_UP_STATUSES)
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<=', today()->addDays($daysBefore))
            ->orderBy('due_date')
            ->get();

        $sentPic = $this->remindPics($notifier, $followUps);
        $sentHse = $this->remindHse($notifier, $followUps);

        // Tandai laporan yang sudah diingatkan
        if ($followUps->isNotEmpty()) {
            HazardReport::whereKey($followUps->pluck('id'))->update(['last_reminded_at' => now()]);
        }

        $this->info("Reminder terkirim: {$sentPic} email ke PIC, {$sentHse} email ke HSE.");

        return self::SUCCESS;
    }

    // Satu email per departemen, dikirim ke semua PIC departemen tersebut
    private function remindPics(HazardNotifier $notifier, Collection $followUps): int
    {
        $sent = 0;

        foreach ($followUps->groupBy('pic_department_id') as $deptId => $reports) {
            $pics = $notifier->picFor((int) $deptId);
            if ($pics->isEmpty()) {
                continue; // departemen tanpa PIC tetap masuk daftar di email HSE
            }

            $overdue = $reports->filter->isOverdue()->values();
            $soon    = $reports->reject->isOverdue()->values();
            $dept    = $reports->first()->picDepartment?->name;

            $sections = [];
            if ($overdue->isNotEmpty()) {
                $sections[] = $this->section(
                    "Lewat Batas Waktu ({$overdue->count()})",
                    "Berikut kami sampaikan {$overdue->count()} temuan potensi bahaya departemen {$dept} yang sudah melewati batas waktu tindak lanjut. Mohon segera ditindaklanjuti melalui tautan masing-masing temuan berikut:",
                    'follow_up',
                    $overdue,
                );
            }
            if ($soon->isNotEmpty()) {
                $sections[] = $this->section(
                    "Jatuh Tempo Segera ({$soon->count()})",
                    "Berikut {$soon->count()} temuan potensi bahaya departemen {$dept} yang akan jatuh tempo:",
                    'follow_up',
                    $soon,
                );
            }

            $sent += $this->deliver($pics, new HazardSummaryMail(
                subject: "Reminder Tindak Lanjut Temuan Potensi Bahaya | {$dept} | {$overdue->count()} terlambat, {$soon->count()} jatuh tempo",
                title: 'Reminder Tindak Lanjut Temuan Potensi Bahaya',
                sections: $sections,
                actionUrl: route('hazard-report.index', ['status' => 'follow_up']),
                footer: ['Apabila perbaikan membutuhkan waktu lebih lama (misalnya menunggu material atau vendor), mohon gunakan menu "Belum Bisa / Perpanjang" pada laporan agar tim HSE mengetahui progresnya.'],
            ));
        }

        return $sent;
    }

    // Ringkasan harian untuk HSE
    private function remindHse(HazardNotifier $notifier, Collection $followUps): int
    {
        $with = ['reporterCompany', 'category', 'priority', 'picDepartment'];

        $pendingValidation = HazardReport::with($with)
            ->where('status', HazardReport::STATUS_PENDING_VALIDATION)
            ->oldest()
            ->get();

        $pendingVerification = HazardReport::with($with)
            ->where('status', HazardReport::STATUS_PENDING_VERIFICATION)
            ->oldest('closing_submitted_at')
            ->get();

        $overdue = $followUps->filter->isOverdue()->values();

        if ($pendingValidation->isEmpty() && $pendingVerification->isEmpty() && $overdue->isEmpty()) {
            return 0;
        }

        $per = now()->translatedFormat('d F Y H:i');
        $sections = [];

        if ($pendingValidation->isNotEmpty()) {
            $hours = (int) config('hazard.notifications.pending_validation_hours', 24);
            $late  = $pendingValidation->filter(fn ($r) => $r->created_at->lte(now()->subHours($hours)))->count();
            $sections[] = $this->section(
                "Menunggu Validasi ({$pendingValidation->count()})",
                "Berikut kami sampaikan {$pendingValidation->count()} temuan potensi bahaya yang menunggu proses validasi per {$per}"
                    . ($late ? ", {$late} di antaranya sudah lebih dari {$hours} jam" : '')
                    . '. Mohon dapat melakukan validasi dengan mengakses tautan masing-masing temuan berikut:',
                'validation',
                $pendingValidation,
            );
        }

        if ($pendingVerification->isNotEmpty()) {
            $sections[] = $this->section(
                "Menunggu Verifikasi Bukti Perbaikan ({$pendingVerification->count()})",
                "Berikut {$pendingVerification->count()} temuan yang sudah diperbaiki PIC dan menunggu verifikasi HSE:",
                'verification',
                $pendingVerification,
            );
        }

        if ($overdue->isNotEmpty()) {
            $noPic = $overdue->pluck('picDepartment.name', 'pic_department_id')
                ->filter(fn ($name, $deptId) => $notifier->picFor((int) $deptId)->isEmpty())
                ->filter()->unique()->values();

            $sections[] = $this->section(
                "Follow Up PIC Lewat Batas Waktu ({$overdue->count()})",
                "Berikut {$overdue->count()} temuan yang belum ditindaklanjuti PIC melewati batas waktu"
                    . ($noPic->isNotEmpty() ? '. Perhatian: departemen ' . $noPic->join(', ', ' dan ') . ' belum memiliki akun PIC' : '')
                    . ':',
                'follow_up',
                $overdue,
            );
        }

        return $this->deliver($notifier->hse(), new HazardSummaryMail(
            subject: "Summary Temuan Potensi Bahaya | {$pendingValidation->count()} validasi, {$pendingVerification->count()} verifikasi, {$overdue->count()} terlambat",
            title: 'Summary Temuan Potensi Bahaya',
            sections: $sections,
            actionUrl: route('hazard-report.index'),
        ));
    }

    private function section(string $heading, string $intro, string $mode, Collection $reports): array
    {
        return [
            'heading' => $heading,
            'intro'   => $intro,
            'mode'    => $mode,
            'reports' => $reports->take(self::MAX_ITEMS)->values(),
            'more'    => max(0, $reports->count() - self::MAX_ITEMS),
        ];
    }

    private function lateDays(HazardReport $r): int
    {
        return (int) $r->due_date->diffInDays(today());
    }

    private function deliver(Collection $users, HazardSummaryMail $mail): int
    {
        if ($users->isEmpty()) {
            return 0;
        }

        try {
            Notification::send($users, $mail);
            return 1;
        } catch (\Throwable $e) {
            Log::error("Reminder hazard gagal: {$e->getMessage()}", ['subject' => $mail->subject]);
            $this->error("Gagal mengirim: {$mail->subject} ({$e->getMessage()})");
            return 0;
        }
    }
}