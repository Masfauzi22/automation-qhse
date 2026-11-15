<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Email Hazard Report serbaguna (laporan baru, divalidasi, ditolak, closed, reminder, dll).
 * Isi email disusun oleh App\Services\HazardNotifier.
 *
 * Sengaja tidak memakai antrean (queue) supaya langsung terkirim juga di Vercel,
 * yang tidak punya queue worker.
 */
class HazardReportMail extends Notification
{
    use Queueable;

    /**
     * @param  string[]  $lines  baris isi email (boleh pakai **tebal**)
     * @param  string  $level  'info' | 'success' | 'error' (warna tombol)
     */
    public function __construct(
        public string $subject,
        public array $lines,
        public ?string $actionText = null,
        public ?string $actionUrl = null,
        public string $level = 'info',
        public array $footer = [],
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject($this->subject)
            ->greeting('Halo ' . ($notifiable->name ?? '') . ',');

        if ($this->level === 'success') {
            $mail->success();
        } elseif ($this->level === 'error') {
            $mail->error();
        }

        foreach ($this->lines as $line) {
            $mail->line($line);
        }

        if ($this->actionText && $this->actionUrl) {
            $mail->action($this->actionText, $this->actionUrl);
        }

        foreach ($this->footer as $line) {
            $mail->line($line);
        }

        return $mail->salutation('Salam,  ' . PHP_EOL . 'Tim QHSE · ' . config('app.name'));
    }
}