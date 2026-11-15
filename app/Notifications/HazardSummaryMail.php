<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Email ringkasan berbentuk tabel (reminder harian PIC & HSE).
 * Tampilan: resources/views/emails/hazard-summary.blade.php
 *
 * $sections = [
 *     [
 *         'heading' => 'Menunggu Validasi',         // judul tabel
 *         'intro'   => 'Berikut kami sampaikan ...', // kalimat pembuka tabel
 *         'mode'    => 'validation',                // validation | verification | follow_up
 *         'reports' => Collection<HazardReport>,
 *         'more'    => 0,                           // jumlah laporan yang tidak ditampilkan
 *     ],
 * ]
 */
class HazardSummaryMail extends Notification
{
    use Queueable;

    public function __construct(
        public string $subject,
        public string $title,
        public array $sections,
        public ?string $actionUrl = null,
        public array $footer = [],
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->subject)
            ->view('emails.hazard-summary', [
                'title'     => $this->title,
                'name'      => $notifiable->name ?? null,
                'sections'  => $this->sections,
                'actionUrl' => $this->actionUrl,
                'footer'    => $this->footer,
            ]);
    }
}