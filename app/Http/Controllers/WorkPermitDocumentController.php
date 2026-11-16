<?php

namespace App\Http\Controllers;

use App\Models\WorkPermit;
use Illuminate\Http\Request;

/**
 * Surat Izin Kerja (PTW) versi digital, siap cetak / simpan PDF.
 * Tersedia setelah permit di-approve QHSE, dipakai sebagai bukti izin saat sidak di lapangan.
 */
class WorkPermitDocumentController extends Controller
{
    // Status yang sudah punya izin dari QHSE
    private const AVAILABLE_STATUSES = [
        WorkPermit::STATUS_ACTIVE,
        WorkPermit::STATUS_STOPPED,
        WorkPermit::STATUS_PENDING_CLOSE_CHECK,
        WorkPermit::STATUS_PENDING_CLOSE_APPROVAL,
        WorkPermit::STATUS_CLOSED,
        WorkPermit::STATUS_EXPIRED,
    ];

    public static function isAvailable(WorkPermit $permit): bool
    {
        return in_array($permit->status, self::AVAILABLE_STATUSES, true);
    }

    public function show(Request $request, WorkPermit $workPermit)
    {
        $user = $request->user();

        abort_unless(
            (int) $workPermit->applicant_id === (int) $user->id
            || $user->hasAnyRole(['superadmin', 'qhse'])
            || $user->canCheckPermitFrom($workPermit->applicant_position, $workPermit->applicant_department_id),
            403
        );
        abort_unless(self::isAvailable($workPermit), 404, 'Surat Izin baru tersedia setelah permit disetujui QHSE.');

        $workPermit->load(['checks', 'gasTests', 'dailyLogs', 'logs', 'applicantDepartment', 'ownerDepartment', 'contractor']);

        return view('work-permits.document', [
            'permit'   => $workPermit,
            'config'   => $workPermit->config(),
            'stamp'    => $this->stamp($workPermit),
            'printedBy'=> $user->name,
        ]);
    }

    // Cap status besar di pojok dokumen: [teks, warna, keterangan]
    private function stamp(WorkPermit $permit): array
    {
        $today = today();

        return match ($permit->status) {
            WorkPermit::STATUS_ACTIVE => match (true) {
                $today->lt($permit->start_date) => ['BELUM BERLAKU', '#b45309', 'Mulai berlaku ' . $permit->start_date->translatedFormat('d M Y')],
                $today->gt($permit->end_date)   => ['MASA BERLAKU HABIS', '#b91c1c', 'Berakhir ' . $permit->end_date->translatedFormat('d M Y') . ', wajib close-out'],
                default => (function () use ($permit, $today) {
                    $day = $permit->dayNumberFor($today);
                    $log = $permit->dailyLogFor($today);

                    return $log
                        ? ['AKTIF', '#047857', "Day {$day} diaktifkan " . $log->created_at->format('H:i') . ' oleh ' . $log->user_name]
                        : ['AKTIF', '#047857', "Day {$day} BELUM diaktifkan, pekerjaan belum boleh dimulai"];
                })(),
            },
            WorkPermit::STATUS_STOPPED => ['DIHENTIKAN', '#b91c1c', 'Stop Work, pekerjaan tidak boleh dilanjutkan sampai diizinkan QHSE'],
            WorkPermit::STATUS_PENDING_CLOSE_CHECK,
            WorkPermit::STATUS_PENDING_CLOSE_APPROVAL => ['PEKERJAAN SELESAI', '#475569', 'Sedang proses close-out'],
            WorkPermit::STATUS_CLOSED  => ['CLOSED', '#475569', 'Permit sudah ditutup ' . ($permit->closed_at?->translatedFormat('d M Y') ?? '')],
            WorkPermit::STATUS_EXPIRED => ['KEDALUWARSA', '#b91c1c', 'Masa berlaku habis tanpa close-out'],
            default                    => ['TIDAK BERLAKU', '#b91c1c', ''],
        };
    }
}