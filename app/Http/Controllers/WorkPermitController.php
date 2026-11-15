<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WorkPermit;
use App\Models\WorkPermitCertificate;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Tasklist Permit to Work.
 *
 *   Pengajuan (Foreman / Supervisor)
 *     → Diperiksa ATASAN pengaju di departemen yang sama
 *         Foreman    → Supervisor atau Section Head
 *         Supervisor → Section Head
 *     → Approval QHSE → AKTIF
 *
 *   Close-out mengikuti alur yang sama → CLOSED.
 *   Superadmin boleh memproses semua langkah.
 */
class WorkPermitController extends Controller
{
    // who: 'superior' = atasan pengaju, 'qhse' = tim QHSE
    private const STEPS = [
        WorkPermit::STATUS_PENDING_CHECK => [
            'who' => 'superior', 'action' => 'checked', 'next' => WorkPermit::STATUS_PENDING_APPROVAL,
            'return_to' => WorkPermit::STATUS_RETURNED, 'button' => 'Diperiksa – Teruskan ke QHSE',
        ],
        WorkPermit::STATUS_PENDING_APPROVAL => [
            'who' => 'qhse', 'action' => 'approved', 'next' => WorkPermit::STATUS_ACTIVE,
            'return_to' => WorkPermit::STATUS_RETURNED, 'button' => 'Approve – Permit Berlaku',
        ],
        WorkPermit::STATUS_PENDING_CLOSE_CHECK => [
            'who' => 'superior', 'action' => 'close_checked', 'next' => WorkPermit::STATUS_PENDING_CLOSE_APPROVAL,
            'return_to' => WorkPermit::STATUS_ACTIVE, 'button' => 'Diperiksa – Teruskan ke QHSE',
        ],
        WorkPermit::STATUS_PENDING_CLOSE_APPROVAL => [
            'who' => 'qhse', 'action' => 'close_approved', 'next' => WorkPermit::STATUS_CLOSED,
            'return_to' => WorkPermit::STATUS_ACTIVE, 'button' => 'Approve Close-out – Tutup Permit',
        ],
    ];

    // Pernyataan bawaan yang wajib dicentang atasan saat memeriksa di lapangan.
    // Tiap jenis permit bisa punya versinya sendiri di config/permits.php → check_confirmations.
    private const CHECK_CONFIRMATIONS = [
        'checked' => [
            'inspected' => 'Saya sudah memeriksa lokasi dan semua persyaratan di Daftar Pemeriksaan terpenuhi.',
            'area_safe' => 'Area kerja dinyatakan aman dari bahaya kebakaran.',
        ],
        'close_checked' => [
            'area_clean' => 'Saya sudah memeriksa lokasi setelah pekerjaan: tidak ada sisa api / bara, area bersih dan aman.',
        ],
    ];

    public function index(Request $request)
    {
        $user = $request->user();
        $tab  = $request->query('tab', 'todo');
        if (! in_array($tab, ['todo', 'active', 'all'], true)) {
            $tab = 'todo';
        }
        $search = trim((string) $request->query('q', ''));

        $base = fn () => $this->visibleTo($user);

        $permits = $base()
            ->with(['applicantDepartment', 'ownerDepartment'])
            ->when($tab === 'todo', fn ($q) => $this->scopeMyTurn($q, $user))
            ->when($tab === 'active', fn ($q) => $q->whereIn('status', [WorkPermit::STATUS_ACTIVE, WorkPermit::STATUS_STOPPED]))
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('permit_number', 'like', "%{$search}%")
                ->orWhere('project_name', 'like', "%{$search}%")
                ->orWhere('location', 'like', "%{$search}%")
                ->orWhere('applicant_name', 'like', "%{$search}%")))
            ->orderBy($tab === 'todo' ? 'updated_at' : 'start_date', $tab === 'todo' ? 'asc' : 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('work-permits.manage.index', [
            'permits' => $permits,
            'tab'     => $tab,
            'search'  => $search,
            'counts'  => [
                'todo'         => $this->scopeMyTurn($base(), $user)->count(),
                'active'       => $base()->where('status', WorkPermit::STATUS_ACTIVE)->count(),
                'stopped'      => $base()->where('status', WorkPermit::STATUS_STOPPED)->count(),
                'active_today' => $base()->where('status', WorkPermit::STATUS_ACTIVE)
                                    ->whereDate('start_date', '<=', today())->whereDate('end_date', '>=', today())->count(),
                'all'          => $base()->count(),
            ],
            'isHse'     => $this->isHse($user),
            'isChecker' => $user->checkablePositions() !== [],
        ]);
    }

    public function show(Request $request, WorkPermit $workPermit)
    {
        $user = $request->user();
        abort_unless($this->canView($user, $workPermit), 403, 'Permit ini bukan dari departemen Anda.');

        $workPermit->load(['applicantDepartment', 'ownerDepartment', 'checks', 'gasTests', 'dailyLogs.photos', 'files', 'logs', 'jsaFile', 'certificate']);

        $step = self::STEPS[$workPermit->status] ?? null;

        return view('work-permits.manage.show', [
            'permit'        => $workPermit,
            'step'          => $step,
            'canStop'       => $this->canStop($user, $workPermit),
            'canResume'     => $this->isHse($user) && $workPermit->status === WorkPermit::STATUS_STOPPED,
            'canAct'        => $step && $this->canAct($user, $workPermit, $step['who']),
            'confirmations' => $step ? $this->confirmationsFor($workPermit, $step['action']) : [],
            'checkerHint'   => $this->checkerHint($workPermit),
            // Peringatan kalau departemen pengaju belum punya atasan yang bisa memeriksa
            'noSuperior'    => $step && $step['who'] === 'superior' && $this->superiorsFor($workPermit)->isEmpty(),
        ]);
    }

    // Teruskan ke langkah berikutnya
    public function approve(Request $request, WorkPermit $workPermit)
    {
        $user = $request->user();
        $step = self::STEPS[$workPermit->status] ?? null;
        abort_unless($step && $this->canAct($user, $workPermit, $step['who']), 403, 'Anda tidak berwenang untuk langkah ini.');

        $rules = ['note' => ['nullable', 'string', 'max:1000']];
        foreach (array_keys($this->confirmationsFor($workPermit, $step['action'])) as $key) {
            $rules["confirm.{$key}"] = ['accepted'];
        }
        // Penutupan oleh QHSE: wajib menyatakan ada / tidak ada insiden (untuk sertifikat)
        $isClosing = $step['next'] === WorkPermit::STATUS_CLOSED;
        if ($isClosing) {
            $rules['incident']      = ['required', 'in:none,reported'];
            $rules['incident_note'] = ['nullable', 'required_if:incident,reported', 'string', 'max:1000'];
        }

        $validated = $request->validate($rules, [
            'confirm.*.accepted'        => 'Centang semua pernyataan pemeriksaan sebelum meneruskan.',
            'incident.required'         => 'Pilih apakah ada insiden / near miss selama pekerjaan.',
            'incident_note.required_if' => 'Jelaskan insiden / near miss yang terjadi.',
        ]);

        $data = ['status' => $step['next'], 'last_note' => null];
        if ($step['next'] === WorkPermit::STATUS_ACTIVE) {
            $data['activated_at'] = now();
        }
        if ($step['next'] === WorkPermit::STATUS_CLOSED) {
            $data['closed_at'] = now();
        }

        $ok = DB::transaction(function () use ($workPermit, $step, $data, $user, $validated, $isClosing) {
            if (! $this->transition($workPermit, $data)) {
                return false;
            }
            $workPermit->logs()->create([
                'action'     => $step['action'],
                'user_id'    => $user->id,
                'user_name'  => $user->name,
                'user_title' => $this->titleFor($user, $step['who']),
                'note'       => $validated['note'] ?? null,
            ]);

            // Permit Closed → terbitkan Sertifikat Penyelesaian
            if ($isClosing) {
                $this->issueCertificate($workPermit, $user, $validated);
            }
            return true;
        });

        if (! $ok) {
            return $this->alreadyProcessed($workPermit);
        }

        $message = match ($step['next']) {
            WorkPermit::STATUS_ACTIVE => "Permit {$workPermit->permit_number} sekarang AKTIF. Pengaju wajib mengaktifkan setiap hari kerja sebelum mulai.",
            WorkPermit::STATUS_CLOSED => "Permit {$workPermit->permit_number} sudah Closed. Sertifikat Penyelesaian "
                . ($workPermit->certificate()->value('certificate_number') ?? '') . ' diterbitkan.',
            default                   => "Permit {$workPermit->permit_number} diteruskan ke QHSE.",
        };

        return redirect()->route('permit.show', $workPermit)->with('success', $message);
    }

    // Kembalikan ke pengaju untuk direvisi
    public function returnPermit(Request $request, WorkPermit $workPermit)
    {
        $user = $request->user();
        $step = self::STEPS[$workPermit->status] ?? null;
        abort_unless($step && $this->canAct($user, $workPermit, $step['who']), 403, 'Anda tidak berwenang untuk langkah ini.');

        $validated = $request->validate([
            'return_note' => ['required', 'string', 'max:1000'],
        ], [
            'return_note.required' => 'Tuliskan apa yang perlu diperbaiki pengaju.',
        ]);

        $isClosePhase = str_starts_with($step['action'], 'close_');

        $ok = DB::transaction(function () use ($workPermit, $step, $user, $validated, $isClosePhase) {
            if (! $this->transition($workPermit, ['status' => $step['return_to'], 'last_note' => $validated['return_note']])) {
                return false;
            }
            $workPermit->logs()->create([
                'action'     => $isClosePhase ? 'close_returned' : 'returned',
                'user_id'    => $user->id,
                'user_name'  => $user->name,
                'user_title' => $this->titleFor($user, $step['who']),
                'note'       => $validated['return_note'],
            ]);
            return true;
        });

        if (! $ok) {
            return $this->alreadyProcessed($workPermit);
        }

        return redirect()->route('permit.show', $workPermit)
            ->with('success', "Permit {$workPermit->permit_number} dikembalikan ke pengaju untuk direvisi.");
    }

    // =================================================================
    // STOP WORK & LANJUTKAN
    // =================================================================

    // Hentikan pekerjaan: QHSE, atasan pengaju, atau pengaju sendiri
    public function stop(Request $request, WorkPermit $workPermit)
    {
        $user = $request->user();
        abort_unless($this->canStop($user, $workPermit), 403, 'Anda tidak berwenang menghentikan pekerjaan ini.');

        $data = $request->validate(['stop_reason' => ['required', 'string', 'max:1000']], [
            'stop_reason.required' => 'Tuliskan alasan pekerjaan dihentikan.',
        ]);

        $ok = DB::transaction(function () use ($workPermit, $user, $data) {
            $updated = WorkPermit::whereKey($workPermit->id)->where('status', WorkPermit::STATUS_ACTIVE)
                ->update(['status' => WorkPermit::STATUS_STOPPED, 'last_note' => $data['stop_reason']]);
            if (! $updated) {
                return false;
            }
            $workPermit->logs()->create([
                'action' => 'stopped', 'user_id' => $user->id, 'user_name' => $user->name,
                'user_title' => $this->stopperTitle($user, $workPermit), 'note' => $data['stop_reason'],
            ]);
            return true;
        });

        if (! $ok) {
            return back()->withErrors(['Status permit sudah berubah.']);
        }

        return back()->with('success', "Pekerjaan pada permit {$workPermit->permit_number} DIHENTIKAN. Hanya QHSE yang bisa mengizinkan lanjut.");
    }

    // Lanjutkan pekerjaan yang dihentikan: hanya QHSE
    public function resume(Request $request, WorkPermit $workPermit)
    {
        $user = $request->user();
        abort_unless($this->isHse($user), 403, 'Hanya QHSE yang bisa mengizinkan pekerjaan dilanjutkan.');

        $data = $request->validate(['resume_note' => ['required', 'string', 'max:1000']], [
            'resume_note.required' => 'Tuliskan kondisi yang sudah diperbaiki sebelum pekerjaan dilanjutkan.',
        ]);

        $ok = DB::transaction(function () use ($workPermit, $user, $data) {
            $updated = WorkPermit::whereKey($workPermit->id)->where('status', WorkPermit::STATUS_STOPPED)
                ->update(['status' => WorkPermit::STATUS_ACTIVE, 'last_note' => null]);
            if (! $updated) {
                return false;
            }
            $workPermit->logs()->create([
                'action' => 'resumed', 'user_id' => $user->id, 'user_name' => $user->name,
                'user_title' => 'QHSE', 'note' => $data['resume_note'],
            ]);
            return true;
        });

        if (! $ok) {
            return back()->withErrors(['Status permit sudah berubah.']);
        }

        return back()->with('success', "Permit {$workPermit->permit_number} AKTIF kembali. Pengaju perlu mengaktifkan ulang kalau hari ini belum diaktifkan.");
    }

    // =================================================================
    // BANTUAN
    // =================================================================

    // Sertifikat dengan nomor unik; kalau bentrok (bersamaan) coba nomor berikutnya
    private function issueCertificate(WorkPermit $permit, User $user, array $validated): void
    {
        $incidentFree = ($validated['incident'] ?? 'none') === 'none';

        for ($attempt = 1; ; $attempt++) {
            try {
                WorkPermitCertificate::create([
                    'work_permit_id'     => $permit->id,
                    'certificate_number' => WorkPermitCertificate::generateNumber(),
                    'verify_token'       => WorkPermitCertificate::newToken(),
                    'incident_free'      => $incidentFree,
                    'incident_note'      => $incidentFree ? null : ($validated['incident_note'] ?? null),
                    'issued_by'          => $user->id,
                    'issued_by_name'     => $user->name,
                    'issued_at'          => now(),
                ]);
                return;
            } catch (UniqueConstraintViolationException $e) {
                // Sudah ada sertifikat untuk permit ini → selesai
                if (WorkPermitCertificate::where('work_permit_id', $permit->id)->exists() || $attempt >= 5) {
                    return;
                }
            }
        }
    }

    private function canStop(User $user, WorkPermit $permit): bool
    {
        return $permit->status === WorkPermit::STATUS_ACTIVE && (
            $this->isHse($user)
            || (int) $permit->applicant_id === (int) $user->id
            || $user->canCheckPermitFrom($permit->applicant_position, $permit->applicant_department_id)
        );
    }

    private function stopperTitle(User $user, WorkPermit $permit): string
    {
        return match (true) {
            $user->hasRole('qhse')                          => 'QHSE',
            (int) $permit->applicant_id === (int) $user->id => 'Pengaju',
            default                                         => trim($user->positionLabel() . ' ' . ($user->department?->name ?? '')),
        };
    }

    private function isHse(User $user): bool
    {
        return $user->hasAnyRole(['superadmin', 'qhse']);
    }

    // HSE melihat semua; atasan (Supervisor / Section Head) melihat permit dari departemennya
    private function visibleTo(User $user): Builder
    {
        $query = WorkPermit::query();

        if ($this->isHse($user)) {
            return $query;
        }

        return $query
            ->where('applicant_department_id', $user->department_id ?? 0)
            ->whereIn('applicant_position', $user->checkablePositions() ?: ['-']);
    }

    // Permit yang sedang menunggu tindakan user ini
    private function scopeMyTurn(Builder $query, User $user): Builder
    {
        if ($user->hasRole('superadmin')) {
            return $query->whereIn('status', array_keys(self::STEPS));
        }

        return $query->where(function ($q) use ($user) {
            // Sebagai atasan
            $positions = $user->checkablePositions();
            if ($positions) {
                $q->orWhere(fn ($w) => $w
                    ->whereIn('status', [WorkPermit::STATUS_PENDING_CHECK, WorkPermit::STATUS_PENDING_CLOSE_CHECK])
                    ->where('applicant_department_id', $user->department_id)
                    ->whereIn('applicant_position', $positions)
                    ->where('applicant_id', '!=', $user->id));
            }
            // Sebagai QHSE
            if ($user->hasRole('qhse')) {
                $q->orWhereIn('status', [WorkPermit::STATUS_PENDING_APPROVAL, WorkPermit::STATUS_PENDING_CLOSE_APPROVAL]);
            }
            // Bukan keduanya → tidak ada
            if (! $positions && ! $user->hasRole('qhse')) {
                $q->whereRaw('1 = 0');
            }
        });
    }

    private function canView(User $user, WorkPermit $permit): bool
    {
        return $this->isHse($user)
            || $user->canCheckPermitFrom($permit->applicant_position, $permit->applicant_department_id);
    }

    private function canAct(User $user, WorkPermit $permit, string $who): bool
    {
        if ($user->hasRole('superadmin')) {
            return true;
        }

        return match ($who) {
            'qhse'     => $user->hasRole('qhse'),
            'superior' => (int) $permit->applicant_id !== (int) $user->id
                          && $user->canCheckPermitFrom($permit->applicant_position, $permit->applicant_department_id),
            default    => false,
        };
    }

    // Atasan yang bisa memeriksa permit ini
    private function superiorsFor(WorkPermit $permit)
    {
        return User::where('department_id', $permit->applicant_department_id)
            ->whereNotNull('position')
            ->get()
            ->filter(fn (User $u) => $u->id !== $permit->applicant_id
                && $u->canCheckPermitFrom($permit->applicant_position, $permit->applicant_department_id));
    }

    // Teks "siapa yang memeriksa", mis. "Supervisor atau Section Head MAINTENANCE"
    private function checkerHint(WorkPermit $permit): string
    {
        $rank = User::positionRank($permit->applicant_position);
        $labels = collect(User::POSITIONS)
            ->filter(fn ($p) => $p['rank'] > $rank && $p['rank'] >= User::PERMIT_CHECKER_MIN_RANK)
            ->pluck('label')
            ->join(', ', ' atau ');

        return $labels . ' ' . ($permit->applicantDepartment?->name ?? '');
    }

    // Jabatan yang tercetak di kotak tanda tangan
    private function titleFor(User $user, string $who): string
    {
        $title = match (true) {
            $who === 'qhse' => 'QHSE',
            (bool) $user->position => trim($user->positionLabel() . ' ' . ($user->department?->name ?? '')),
            default => 'Atasan pengaju',
        };

        $byAdmin = $user->hasRole('superadmin') && ! ($who === 'qhse' ? $user->hasRole('qhse') : $user->position);

        return $title . ($byAdmin ? ' (oleh superadmin)' : '');
    }

    // Ubah status hanya kalau status saat ini masih sama (cegah diproses dua orang bersamaan)
    private function transition(WorkPermit $permit, array $data): bool
    {
        return (bool) WorkPermit::whereKey($permit->id)
            ->where('status', $permit->status)
            ->update($data);
    }

    private function alreadyProcessed(WorkPermit $permit)
    {
        return redirect()->route('permit.show', $permit)
            ->withErrors(['Permit ini sudah diproses oleh orang lain. Halaman sudah dimuat ulang dengan status terbaru.']);
    }

    // Pernyataan pemeriksaan sesuai jenis permit
    private function confirmationsFor(WorkPermit $permit, string $action): array
    {
        return $permit->config()['check_confirmations'][$action] ?? self::CHECK_CONFIRMATIONS[$action] ?? [];
    }
}