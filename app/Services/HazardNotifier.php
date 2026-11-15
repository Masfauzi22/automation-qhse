<?php

namespace App\Services;

use App\Models\HazardReport;
use App\Models\User;
use App\Notifications\HazardReportMail;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

/**
 * Semua email Hazard Report dikirim lewat sini.
 * Kalau email gagal terkirim, proses aplikasi tetap jalan (error hanya dicatat di log).
 *
 * Pemakaian: app(HazardNotifier::class)->validated($report, $actor);
 */
class HazardNotifier
{
    // =================================================================
    // EMAIL PER KEJADIAN
    // =================================================================

    // Pelapor mengirim laporan baru → HSE
    public function newReport(HazardReport $report): void
    {
        $report = $this->fresh($report);

        $this->send('new_report', $this->hse(), new HazardReportMail(
            subject: "[Hazard Baru] {$report->report_number} - {$report->location}",
            lines: [
                "Ada laporan hazard baru yang menunggu validasi.",
                ...$this->summary($report),
            ],
            actionText: 'Validasi Laporan',
            actionUrl: route('hazard-report.show', $report),
        ), $report->reporter_id);
    }

    // HSE memvalidasi → PIC (atau pelapor kalau langsung Closed)
    public function validated(HazardReport $report, ?User $actor = null): void
    {
        $report = $this->fresh($report);

        if ($report->status === HazardReport::STATUS_CLOSED) {
            $this->closed($report, $actor, 'Laporanmu divalidasi HSE dan langsung Closed karena sudah diperbaiki di tempat.');
            return;
        }

        $this->sendToPic($report, new HazardReportMail(
            subject: "[Follow Up] {$report->report_number} - batas {$report->due_date?->translatedFormat('d M Y')}",
            lines: [
                "Ada temuan hazard untuk departemen **{$report->picDepartment?->name}** yang perlu ditindaklanjuti.",
                ...$this->summary($report),
                "**Batas waktu:** {$report->due_date?->translatedFormat('l, d F Y')}",
                ...($report->validation_note ? ["**Catatan HSE:** {$report->validation_note}"] : []),
            ],
            actionText: 'Tindak Lanjuti',
            actionUrl: route('hazard-report.show', $report),
        ), $actor);

        $this->send('reporter_updates', $this->reporter($report), new HazardReportMail(
            subject: "[Divalidasi] {$report->report_number}",
            lines: [
                "Laporanmu sudah divalidasi HSE dan diteruskan ke departemen **{$report->picDepartment?->name}**.",
                "Terima kasih sudah peduli dengan keselamatan di area kerja.",
            ],
            actionText: 'Lihat Laporan',
            actionUrl: route('my-hazard-report.show', $report),
            level: 'success',
        ), $actor?->id);
    }

    // HSE menolak → pelapor
    public function rejected(HazardReport $report, ?User $actor = null): void
    {
        $report = $this->fresh($report);

        $lines = ["Laporan **{$report->report_number}** tidak dilanjutkan oleh HSE.", "**Alasan:** {$report->rejectionReasonLabel()}"];
        if ($report->duplicateOf) {
            $lines[] = "Bahaya ini sudah dilaporkan sebelumnya di **{$report->duplicateOf->report_number}** dan sedang/sudah ditangani.";
        }
        if ($report->validation_note) {
            $lines[] = "**Catatan HSE:** {$report->validation_note}";
        }
        $lines[] = 'Tetap semangat melapor, setiap laporan membantu menjaga area kerja tetap aman.';

        $this->send('reporter_updates', $this->reporter($report), new HazardReportMail(
            subject: "[Ditolak] {$report->report_number}",
            lines: $lines,
            actionText: 'Lihat Laporan',
            actionUrl: route('my-hazard-report.show', $report),
        ), $actor?->id);
    }

    // PIC mengirim bukti perbaikan → HSE
    public function closingSubmitted(HazardReport $report, ?User $actor = null): void
    {
        $report = $this->fresh($report);

        $this->send('closing_submitted', $this->hse(), new HazardReportMail(
            subject: "[Verifikasi] {$report->report_number} - bukti perbaikan dari PIC",
            lines: [
                "PIC departemen **{$report->picDepartment?->name}** sudah mengirim bukti perbaikan.",
                ...$this->summary($report),
                "**Perbaikan:** {$report->closing_note}",
            ],
            actionText: 'Verifikasi Bukti',
            actionUrl: route('hazard-report.show', $report),
        ), $actor?->id);
    }

    // HSE mengembalikan bukti perbaikan → PIC
    public function closingReturned(HazardReport $report, ?User $actor = null): void
    {
        $report = $this->fresh($report);

        $this->sendToPic($report, new HazardReportMail(
            subject: "[Dikembalikan] {$report->report_number} - bukti perbaikan perlu diperbaiki",
            lines: [
                "Bukti perbaikan untuk **{$report->report_number}** dikembalikan oleh HSE.",
                "**Catatan HSE:** {$report->verification_note}",
                "**Batas waktu:** {$report->due_date?->translatedFormat('l, d F Y')}",
            ],
            actionText: 'Perbaiki & Kirim Ulang',
            actionUrl: route('hazard-report.show', $report),
            level: 'error',
        ), $actor);
    }

    // Laporan Closed → pelapor + PIC
    public function closed(HazardReport $report, ?User $actor = null, ?string $message = null): void
    {
        $report = $this->fresh($report);

        $this->send('reporter_updates', $this->reporter($report), new HazardReportMail(
            subject: "[Closed] {$report->report_number}",
            lines: [
                $message ?? "Temuan dari laporanmu **{$report->report_number}** sudah diperbaiki dan diverifikasi HSE.",
                "**Lokasi:** {$report->location}",
                'Terima kasih, laporanmu membuat area kerja lebih aman.',
            ],
            actionText: 'Lihat Hasil Perbaikan',
            actionUrl: route('my-hazard-report.show', $report),
            level: 'success',
        ), $actor?->id);

        if ($report->closing_submitted_by && (int) $report->closing_submitted_by !== (int) $report->reporter_id) {
            $this->sendToPic($report, new HazardReportMail(
                subject: "[Closed] {$report->report_number} - perbaikan diterima HSE",
                lines: ["Bukti perbaikan untuk **{$report->report_number}** sudah diterima HSE. Laporan Closed."],
                actionText: 'Lihat Laporan',
                actionUrl: route('hazard-report.show', $report),
                level: 'success',
            ), $actor, notifyHseIfNoPic: false);
        }
    }

    // PIC memperpanjang batas waktu → HSE
    public function extensionRequested(HazardReport $report, ?User $actor = null): void
    {
        $report = $this->fresh($report);
        $update = $report->updates->last();

        $this->send('extension', $this->hse(), new HazardReportMail(
            subject: "[Perpanjangan] {$report->report_number} - s.d. {$report->due_date?->translatedFormat('d M Y')}",
            lines: [
                "PIC departemen **{$report->picDepartment?->name}** memperpanjang batas waktu ({$report->extension_count}x).",
                "**Batas:** {$update?->old_due_date?->translatedFormat('d M Y')} → {$report->due_date?->translatedFormat('d M Y')}",
                "**Kendala:** {$update?->obstacle}",
                "**Tindakan sementara:** {$update?->temporary_action}",
                'Perpanjangan langsung berlaku. Buka laporan kalau ingin menolaknya.',
            ],
            actionText: 'Lihat Progres',
            actionUrl: route('hazard-report.show', $report),
        ), $actor?->id);
    }

    // HSE menolak perpanjangan → PIC
    public function extensionRejected(HazardReport $report, ?User $actor = null): void
    {
        $report = $this->fresh($report);
        $update = $report->updates->last();

        $this->sendToPic($report, new HazardReportMail(
            subject: "[Perpanjangan Ditolak] {$report->report_number}",
            lines: [
                "Perpanjangan batas waktu untuk **{$report->report_number}** ditolak HSE.",
                "**Alasan:** {$update?->note}",
                "**Batas waktu kembali ke:** {$report->due_date?->translatedFormat('l, d F Y')}",
            ],
            actionText: 'Tindak Lanjuti',
            actionUrl: route('hazard-report.show', $report),
            level: 'error',
        ), $actor);
    }

    // =================================================================
    // PENERIMA
    // =================================================================

    public function hse(): Collection
    {
        return User::role(config('hazard.notifications.hse_roles', ['qhse']))
            ->whereNotNull('email')
            ->get();
    }

    public function picFor(int $departmentId): Collection
    {
        return User::role('pic_dept')
            ->where('department_id', $departmentId)
            ->whereNotNull('email')
            ->get();
    }

    private function reporter(HazardReport $report): Collection
    {
        return collect([$report->reporter])->filter(fn ($u) => $u?->email);
    }

    // =================================================================
    // BANTUAN
    // =================================================================

    // Kirim ke PIC; kalau departemen belum punya akun PIC, beri tahu HSE
    private function sendToPic(HazardReport $report, HazardReportMail $mail, ?User $actor, bool $notifyHseIfNoPic = true): void
    {
        $pics = $this->picFor($report->pic_department_id);

        if ($pics->isEmpty() && $notifyHseIfNoPic) {
            $mail->lines[] = "⚠️ Departemen **{$report->picDepartment?->name}** belum punya akun PIC, jadi email ini dikirim ke HSE. Tambahkan user dengan role pic_dept di departemen tersebut.";
            $this->send('pic_updates', $this->hse(), $mail, $actor?->id);
            return;
        }

        $this->send('pic_updates', $pics, $mail, $actor?->id);
    }

    private function send(string $event, Collection $users, HazardReportMail $mail, ?int $exceptUserId = null): void
    {
        if (! config('hazard.notifications.enabled') || ! config("hazard.notifications.events.{$event}", true)) {
            return;
        }

        // Orang yang melakukan aksi tidak perlu dapat email tentang aksinya sendiri
        $users = $users->reject(fn (User $u) => $exceptUserId && $u->id === $exceptUserId)->unique('id');
        if ($users->isEmpty()) {
            return;
        }

        try {
            Notification::send($users, $mail);
        } catch (\Throwable $e) {
            Log::error("Email hazard [{$event}] gagal: {$e->getMessage()}", ['subject' => $mail->subject]);
        }
    }

    // Data terbaru dari database (status baru saja diubah lewat query langsung)
    private function fresh(HazardReport $report): HazardReport
    {
        return $report->fresh(['reporter', 'category', 'priority', 'picDepartment', 'duplicateOf', 'updates']) ?? $report;
    }

    private function summary(HazardReport $report): array
    {
        return [
            "**No. Laporan:** {$report->report_number}",
            "**Lokasi:** {$report->location}",
            "**Kategori / Prioritas:** {$report->category?->name} / {$report->priority?->name}",
            "**Temuan:** " . \Illuminate\Support\Str::limit($report->description, 200),
            "**Pelapor:** {$report->reporter_name}",
        ];
    }
}