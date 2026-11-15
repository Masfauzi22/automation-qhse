<?php

namespace App\Http\Controllers;

use App\Models\WorkPermit;
use App\Models\WorkPermitCertificate;
use Illuminate\Http\Request;

/**
 * Sertifikat Penyelesaian Permit to Work.
 *  - show   : halaman sertifikat siap cetak / simpan PDF (login)
 *  - verify : halaman publik dari QR code untuk mengecek keaslian (tanpa login)
 */
class WorkPermitCertificateController extends Controller
{
    public function show(Request $request, WorkPermit $workPermit)
    {
        $user = $request->user();

        abort_unless(
            (int) $workPermit->applicant_id === (int) $user->id
            || $user->hasAnyRole(['superadmin', 'qhse'])
            || $user->canCheckPermitFrom($workPermit->applicant_position, $workPermit->applicant_department_id),
            403
        );

        $workPermit->load(['certificate', 'applicantDepartment', 'ownerDepartment', 'dailyLogs', 'logs']);
        abort_unless($workPermit->certificate, 404, 'Sertifikat belum terbit. Permit harus Closed terlebih dahulu.');

        return view('work-permits.certificate', $this->data($workPermit));
    }

    public function verify(string $token)
    {
        $certificate = WorkPermitCertificate::where('verify_token', $token)->firstOrFail();
        $permit = $certificate->permit()->withTrashed()
            ->with(['certificate', 'applicantDepartment', 'ownerDepartment', 'dailyLogs', 'logs'])
            ->firstOrFail();

        return view('work-permits.certificate-verify', $this->data($permit));
    }

    private function data(WorkPermit $permit): array
    {
        return [
            'permit'       => $permit,
            'certificate'  => $permit->certificate,
            'config'       => $permit->config(),
            'activeDays'   => $permit->dailyLogs->count(),
            'watchMinutes' => $permit->work_finished_at && $permit->post_watch_until
                ? (int) $permit->work_finished_at->diffInMinutes($permit->post_watch_until) : null,
            'stopCount'    => $permit->logs->where('action', 'stopped')->count(),
            'openChecked'  => $permit->signature('checked'),
            'openApproved' => $permit->signature('approved'),
            'closeChecked' => $permit->signature('close_checked'),
            'closeApproved'=> $permit->signature('close_approved'),
        ];
    }
}