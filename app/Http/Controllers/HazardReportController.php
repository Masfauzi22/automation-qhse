<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Department;
use App\Models\HazardCategory;
use App\Models\HazardPriority;
use App\Models\HazardReport;
use App\Models\HazardReportPhoto;
use App\Models\HazardReportUpdate;
use App\Models\User;
use App\Services\HazardDuplicateFinder;
use App\Services\HazardNotifier;
use App\Support\Uploads;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * Tasklist Hazard Report.
 *
 * - HSE (qhse) & superadmin : lihat semua laporan, validasi/tolak, verifikasi bukti closing.
 * - PIC departemen (pic_dept): lihat laporan untuk departemennya yang sudah divalidasi, upload bukti closing.
 */
class HazardReportController extends Controller
{
    // Status yang boleh dilihat PIC (laporan yang belum divalidasi / ditolak tidak relevan untuk PIC)
    private const PIC_STATUSES = [
        HazardReport::STATUS_OPEN,
        HazardReport::STATUS_IN_PROGRESS,
        HazardReport::STATUS_PENDING_VERIFICATION,
        HazardReport::STATUS_CLOSED,
    ];

    // =================================================================
    // DAFTAR & DETAIL (HSE + PIC)
    // =================================================================

    public function index(Request $request)
    {
        $user  = $request->user();
        $isHse = $this->isHse($user);

        // Tab yang tersedia
        $allowed = $isHse ? array_keys(HazardReport::STATUSES) : self::PIC_STATUSES;
        $tabs    = collect($allowed)->mapWithKeys(fn ($s) => [$s => HazardReport::STATUSES[$s]['label']])->all() + ['all' => 'Semua'];

        // Tab default: HSE → antrean validasi, PIC → laporan yang harus di-follow up
        $default = $isHse ? HazardReport::STATUS_PENDING_VALIDATION : HazardReport::STATUS_OPEN;
        $status  = $request->query('status', $default);
        // 'follow_up' = gabungan Open + Dalam Proses (dipakai kartu ringkasan)
        if (! array_key_exists($status, $tabs) && $status !== 'follow_up') {
            $status = $default;
        }

        $search     = trim((string) $request->query('q', ''));
        $deptId     = $isHse ? $request->query('department') : null; // PIC tidak perlu filter departemen
        $priorityId = $request->query('priority');

        $reports = $this->visibleTo($user)
            ->with(['category', 'priority', 'picDepartment', 'reporterDepartment'])
            ->when($status === 'follow_up', fn ($q) => $q->whereIn('status', HazardReport::FOLLOW_UP_STATUSES))
            ->when(! in_array($status, ['all', 'follow_up'], true), fn ($q) => $q->where('status', $status))
            ->when($search !== '', fn ($q) => $q->where(function ($w) use ($search) {
                $w->where('report_number', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('reporter_name', 'like', "%{$search}%");
            }))
            ->when($deptId, fn ($q) => $q->where('pic_department_id', $deptId))
            ->when($priorityId, fn ($q) => $q->where('hazard_priority_id', $priorityId))
            // Antrean kerja (validasi / follow up / verifikasi): paling lama di atas. Lainnya: terbaru di atas.
            ->when(
                in_array($status, ['follow_up', HazardReport::STATUS_PENDING_VALIDATION, HazardReport::STATUS_OPEN, HazardReport::STATUS_IN_PROGRESS, HazardReport::STATUS_PENDING_VERIFICATION]),
                fn ($q) => in_array($status, [...HazardReport::FOLLOW_UP_STATUSES, 'follow_up']) ? $q->orderBy('due_date') : $q->oldest(),
                fn ($q) => $q->latest()
            )
            ->paginate(20)
            ->withQueryString();

        $counts = $this->visibleTo($user)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('hazard-reports.manage.index', [
            'reports'      => $reports,
            'counts'       => $counts,
            'overdueCount' => $this->visibleTo($user)->overdue()->count(),
            'tabs'         => $tabs,
            'isHse'        => $isHse,
            'userDept'     => $user->department,
            'status'       => $status,
            'search'       => $search,
            'deptId'       => $deptId,
            'priorityId'   => $priorityId,
            'departments'  => $isHse ? Department::orderBy('name')->get() : collect(),
            'priorities'   => HazardPriority::active()->get(),
        ]);
    }

    public function show(Request $request, HazardReport $hazardReport)
    {
        $user = $request->user();
        abort_unless($this->canView($user, $hazardReport), 403, 'Laporan ini bukan untuk departemen Anda.');

        $hazardReport->load([
            'category', 'priority', 'picDepartment', 'reporterCompany', 'reporterDepartment',
            'validator', 'closingSubmitter', 'closer', 'findingPhotos', 'closingPhotos',
            'updates.user', 'updates.photos', 'duplicateOf', 'duplicates',
        ]);

        $lastUpdate = $hazardReport->updates->last();
        $isHse      = $this->isHse($user);

        return view('hazard-reports.manage.show', [
            'report'        => $hazardReport,
            'isHse'         => $this->isHse($user),
            // Rekam jejak pelapor, hanya untuk HSE (bahan pertimbangan saat validasi)
            'reporterStats' => $this->isHse($user) ? HazardReport::reporterStats($hazardReport->reporter_id) : null,
            // Kandidat duplikat, dicek hanya saat HSE memvalidasi
            'duplicateCandidates' => $isHse && $hazardReport->status === HazardReport::STATUS_PENDING_VALIDATION
                ? app(HazardDuplicateFinder::class)->find($hazardReport)
                : collect(),
            'canFollowUp'   => $this->canFollowUp($user, $hazardReport),
            // HSE boleh menolak perpanjangan terakhir yang belum ditolak
            'canRejectExtension' => $this->isHse($user)
                && $hazardReport->status === HazardReport::STATUS_IN_PROGRESS
                && $lastUpdate?->isExtension(),
            'maxExtensions'          => config('hazard.max_extensions', 2),
            'maxExtensionDays'       => config('hazard.max_extension_days', 14),
            'extensionPhotoRequired' => config('hazard.extension_photo_required', true),
            'categories'    => HazardCategory::active()->get(),
            'priorities'    => HazardPriority::active()->get(),
            'departments'   => Department::orderBy('name')->get(),
            'rejectReasons' => config('hazard.reject_reasons', []),
            'maxPhotos'     => config('hazard.max_photos', 3),
            'maxPhotoKb'    => config('hazard.max_photo_kb', 5120),
        ]);
    }

    // =================================================================
    // VALIDASI HSE
    // =================================================================

    public function validateReport(Request $request, HazardReport $hazardReport)
    {
        $validated = $request->validate([
            'hazard_category_id' => ['required', Rule::exists('hazard_categories', 'id')],
            'hazard_priority_id' => ['required', Rule::exists('hazard_priorities', 'id')],
            'pic_department_id'  => ['required', 'exists:departments,id'],
            'validation_note'    => ['nullable', 'string', 'max:1000'],
        ], [
            'required' => ':attribute wajib diisi.',
        ], [
            'hazard_category_id' => 'Kategori bahaya',
            'hazard_priority_id' => 'Kategori prioritas',
            'pic_department_id'  => 'Departemen PIC',
            'validation_note'    => 'Catatan HSE',
        ]);

        $user     = $request->user();
        $priority = HazardPriority::findOrFail($validated['hazard_priority_id']);

        $data = [
            'hazard_category_id' => $validated['hazard_category_id'],
            'hazard_priority_id' => $priority->id,
            'pic_department_id'  => $validated['pic_department_id'],
            'validation_note'    => $validated['validation_note'] ?? null,
            'validated_by'       => $user->id,
            'validated_at'       => now(),
            'rejection_reason'   => null,
        ];

        if ($hazardReport->closing_submitted_at) {
            // Pelapor sudah memperbaiki di tempat → langsung Closed
            $data += [
                'status'    => HazardReport::STATUS_CLOSED,
                'closed_by' => $user->id,
                'closed_at' => now(),
                'due_date'  => null,
            ];
            $message = "Laporan {$hazardReport->report_number} divalidasi dan langsung Closed karena sudah diperbaiki di tempat.";
        } else {
            $dueDate = today()->addDays($priority->sla_days ?: config('hazard.default_sla_days', 3));
            $data += [
                'status'            => HazardReport::STATUS_OPEN,
                'due_date'          => $dueDate,
                'original_due_date' => $dueDate,
            ];
            $message = "Laporan {$hazardReport->report_number} divalidasi. Batas waktu follow up PIC: {$dueDate->translatedFormat('d F Y')}.";
        }

        if (! $this->transition($hazardReport, HazardReport::STATUS_PENDING_VALIDATION, $data)) {
            return $this->alreadyProcessed($hazardReport);
        }

        // Email notifikasi (gagal kirim tidak membatalkan proses)
        $this->notifier()->validated($hazardReport, $user);

        return redirect()->route('hazard-report.show', $hazardReport)->with('success', $message);
    }

    public function reject(Request $request, HazardReport $hazardReport)
    {
        $reasons = config('hazard.reject_reasons', []);

        $validated = $request->validate([
            'rejection_reason'    => ['required', Rule::in(array_keys($reasons))],
            'validation_note'     => ['nullable', 'string', 'max:1000'],
            // Wajib diisi kalau alasannya duplikat: nomor laporan aslinya
            'duplicate_of_number' => ['nullable', 'required_if:rejection_reason,duplikat', 'string', 'max:50'],
        ], [
            'rejection_reason.required'       => 'Pilih alasan penolakan.',
            'rejection_reason.in'             => 'Alasan penolakan tidak valid.',
            'duplicate_of_number.required_if' => 'Isi nomor laporan asli yang diduplikasi.',
        ]);

        $duplicateOfId = null;
        if ($validated['rejection_reason'] === 'duplikat') {
            $original = HazardReport::where('report_number', trim($validated['duplicate_of_number']))
                ->whereKeyNot($hazardReport->getKey())
                ->first();

            if (! $original) {
                return back()->withInput()->withErrors(['duplicate_of_number' => 'Nomor laporan asli tidak ditemukan.']);
            }
            if ($original->status === HazardReport::STATUS_REJECTED) {
                return back()->withInput()->withErrors(['duplicate_of_number' => 'Laporan asli tidak boleh laporan yang ditolak.']);
            }
            $duplicateOfId = $original->id;
        }

        $ok = $this->transition($hazardReport, HazardReport::STATUS_PENDING_VALIDATION, [
            'status'           => HazardReport::STATUS_REJECTED,
            'rejection_reason' => $validated['rejection_reason'],
            'duplicate_of_id'  => $duplicateOfId,
            'validation_note'  => $validated['validation_note'] ?? null,
            'validated_by'     => $request->user()->id,
            'validated_at'     => now(),
        ]);

        if (! $ok) {
            return $this->alreadyProcessed($hazardReport);
        }

        // Email notifikasi (gagal kirim tidak membatalkan proses)
        $this->notifier()->rejected($hazardReport, $request->user());

        return redirect()->route('hazard-report.show', $hazardReport)
            ->with('success', "Laporan {$hazardReport->report_number} ditolak: {$reasons[$validated['rejection_reason']]}"
                . ($duplicateOfId ? " dari {$validated['duplicate_of_number']}." : '.'));
    }

    // =================================================================
    // FOLLOW UP PIC
    // =================================================================

    public function submitClosing(Request $request, HazardReport $hazardReport)
    {
        $user = $request->user();
        abort_unless($this->canFollowUp($user, $hazardReport), 403, 'Anda bukan PIC untuk laporan ini.');

        $maxPhotos = config('hazard.max_photos', 3);
        $maxKb     = config('hazard.max_photo_kb', 5120);

        $validated = $request->validate([
            'closing_photos'   => ['required', 'array', 'min:1', 'max:' . $maxPhotos],
            'closing_photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:' . $maxKb],
            'closing_note'     => ['required', 'string', 'max:1000'],
        ], [
            'closing_photos.required'   => 'Foto bukti perbaikan wajib diupload minimal 1 foto.',
            'closing_photos.max'        => 'Foto bukti perbaikan maksimal :max foto.',
            'closing_photos.*.image'    => 'File harus berupa gambar.',
            'closing_photos.*.mimes'    => 'Format foto harus JPG, PNG, atau WEBP.',
            'closing_photos.*.max'      => 'Ukuran setiap foto maksimal ' . round($maxKb / 1024) . ' MB.',
            'closing_photos.*.uploaded' => 'Foto gagal diupload. Kemungkinan ukurannya melebihi batas server.',
            'closing_note.required'     => 'Jelaskan perbaikan yang sudah dilakukan.',
        ]);

        $storedPaths = [];

        try {
            $ok = DB::transaction(function () use ($request, $hazardReport, $user, $validated, &$storedPaths) {
                $ok = $this->transition($hazardReport, HazardReport::FOLLOW_UP_STATUSES, [
                    'status'               => HazardReport::STATUS_PENDING_VERIFICATION,
                    'closing_submitted_by' => $user->id,
                    'closing_submitted_at' => now(),
                    'closing_note'         => $validated['closing_note'],
                    'verification_note'    => null,
                ]);

                if (! $ok) {
                    return false;
                }

                $folder = 'hazard-reports/' . $hazardReport->created_at->format('Y/m') . '/' . $hazardReport->id;

                foreach ($request->file('closing_photos', []) as $file) {
                    $storedPaths[] = $path = $file->store($folder, Uploads::disk());
                    $hazardReport->photos()->create([
                        'type'          => HazardReportPhoto::TYPE_CLOSING,
                        'path'          => $path,
                        'original_name' => $file->getClientOriginalName(),
                        'uploaded_by'   => $user->id,
                    ]);
                }

                return true;
            });
        } catch (\Throwable $e) {
            Uploads::delete($storedPaths);
            throw $e;
        }

        if (! $ok) {
            return $this->alreadyProcessed($hazardReport);
        }

        // Email notifikasi (gagal kirim tidak membatalkan proses)
        $this->notifier()->closingSubmitted($hazardReport, $request->user());

        return redirect()->route('hazard-report.show', $hazardReport)
            ->with('success', "Bukti perbaikan untuk {$hazardReport->report_number} terkirim dan menunggu verifikasi HSE.");
    }

    /**
     * PIC belum bisa menyelesaikan perbaikan: catat kendala, tindakan sementara,
     * dan target selesai baru. Langsung berlaku, HSE bisa menolak.
     */
    public function requestExtension(Request $request, HazardReport $hazardReport)
    {
        $user = $request->user();
        abort_unless($this->canFollowUp($user, $hazardReport), 403, 'Anda bukan PIC untuk laporan ini.');

        $maxExt    = config('hazard.max_extensions', 2);
        $maxDays   = config('hazard.max_extension_days', 14);
        $maxPhotos = config('hazard.max_photos', 3);
        $maxKb     = config('hazard.max_photo_kb', 5120);

        if (! $hazardReport->canExtend()) {
            return back()->withErrors(["Batas perpanjangan sudah habis ({$maxExt} kali). Selesaikan perbaikan dan upload bukti, atau hubungi HSE."]);
        }

        $validated = $request->validate([
            'obstacle'           => ['required', 'string', 'max:1000'],
            'temporary_action'   => ['required', 'string', 'max:1000'],
            'new_due_date'       => ['required', 'date', 'after:today', 'before_or_equal:' . today()->addDays($maxDays)->toDateString()],
            'progress_photos'    => [config('hazard.extension_photo_required', true) ? 'required' : 'nullable', 'array', 'max:' . $maxPhotos],
            'progress_photos.*'  => ['image', 'mimes:jpg,jpeg,png,webp', 'max:' . $maxKb],
        ], [
            'obstacle.required'          => 'Jelaskan kendala kenapa belum bisa diselesaikan.',
            'temporary_action.required'  => 'Isi tindakan sementara yang sudah dilakukan supaya area tetap aman.',
            'new_due_date.required'      => 'Isi target selesai yang baru.',
            'new_due_date.after'         => 'Target selesai baru harus setelah hari ini.',
            'new_due_date.before_or_equal' => "Target selesai baru maksimal {$maxDays} hari dari hari ini.",
            'progress_photos.required'   => 'Upload minimal 1 foto tindakan sementara (misalnya barikade atau rambu).',
            'progress_photos.max'        => 'Foto maksimal :max.',
            'progress_photos.*.image'    => 'File harus berupa gambar.',
            'progress_photos.*.mimes'    => 'Format foto harus JPG, PNG, atau WEBP.',
            'progress_photos.*.max'      => 'Ukuran setiap foto maksimal ' . round($maxKb / 1024) . ' MB.',
            'progress_photos.*.uploaded' => 'Foto gagal diupload. Kemungkinan ukurannya melebihi batas server.',
        ]);

        $oldDue      = $hazardReport->due_date;
        $storedPaths = [];

        try {
            $ok = DB::transaction(function () use ($request, $hazardReport, $user, $validated, $oldDue, $maxExt, &$storedPaths) {
                $ok = HazardReport::whereKey($hazardReport->id)
                    ->whereIn('status', HazardReport::FOLLOW_UP_STATUSES)
                    ->where('extension_count', '<', $maxExt)
                    ->update([
                        'status'          => HazardReport::STATUS_IN_PROGRESS,
                        'due_date'        => $validated['new_due_date'],
                        'extension_count' => DB::raw('extension_count + 1'),
                    ]);

                if (! $ok) {
                    return false;
                }

                $update = $hazardReport->updates()->create([
                    'user_id'          => $user->id,
                    'type'             => HazardReportUpdate::TYPE_EXTENSION,
                    'obstacle'         => $validated['obstacle'],
                    'temporary_action' => $validated['temporary_action'],
                    'old_due_date'     => $oldDue,
                    'new_due_date'     => $validated['new_due_date'],
                ]);

                $folder = 'hazard-reports/' . $hazardReport->created_at->format('Y/m') . '/' . $hazardReport->id;

                foreach ($request->file('progress_photos', []) as $file) {
                    $storedPaths[] = $path = $file->store($folder, Uploads::disk());
                    $hazardReport->photos()->create([
                        'hazard_report_update_id' => $update->id,
                        'type'                    => HazardReportPhoto::TYPE_PROGRESS,
                        'path'                    => $path,
                        'original_name'           => $file->getClientOriginalName(),
                        'uploaded_by'             => $user->id,
                    ]);
                }

                return true;
            });
        } catch (\Throwable $e) {
            Uploads::delete($storedPaths);
            throw $e;
        }

        if (! $ok) {
            return $this->alreadyProcessed($hazardReport);
        }

        $newDue = \Illuminate\Support\Carbon::parse($validated['new_due_date'])->translatedFormat('d F Y');

        // Email notifikasi (gagal kirim tidak membatalkan proses)
        $this->notifier()->extensionRequested($hazardReport, $request->user());

        return redirect()->route('hazard-report.show', $hazardReport)
            ->with('success', "Progres tercatat. Batas waktu baru: {$newDue}.");
    }

    /** HSE menolak perpanjangan terakhir: batas waktu kembali ke sebelumnya. */
    public function rejectExtension(Request $request, HazardReport $hazardReport)
    {
        $validated = $request->validate([
            'note' => ['required', 'string', 'max:1000'],
        ], [
            'note.required' => 'Jelaskan kenapa perpanjangan ditolak, supaya PIC tahu harus berbuat apa.',
        ]);

        $last = $hazardReport->updates()->latest('id')->first();

        if (! $last?->isExtension() || $hazardReport->status !== HazardReport::STATUS_IN_PROGRESS) {
            return $this->alreadyProcessed($hazardReport);
        }

        $ok = DB::transaction(function () use ($request, $hazardReport, $validated, $last) {
            $ok = $this->transition($hazardReport, HazardReport::STATUS_IN_PROGRESS, [
                'due_date' => $last->old_due_date,
            ]);

            if ($ok) {
                $hazardReport->updates()->create([
                    'user_id'      => $request->user()->id,
                    'type'         => HazardReportUpdate::TYPE_EXTENSION_REJECTED,
                    'note'         => $validated['note'],
                    'old_due_date' => $last->new_due_date,
                    'new_due_date' => $last->old_due_date,
                ]);
            }

            return $ok;
        });

        if (! $ok) {
            return $this->alreadyProcessed($hazardReport);
        }

        // Email notifikasi (gagal kirim tidak membatalkan proses)
        $this->notifier()->extensionRejected($hazardReport, $request->user());

        return redirect()->route('hazard-report.show', $hazardReport)
            ->with('success', "Perpanjangan ditolak. Batas waktu kembali ke {$last->old_due_date->translatedFormat('d F Y')}.");
    }

    // =================================================================
    // VERIFIKASI HSE ATAS BUKTI CLOSING PIC
    // =================================================================

    public function approveClosing(Request $request, HazardReport $hazardReport)
    {
        $validated = $request->validate([
            'verification_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $ok = $this->transition($hazardReport, HazardReport::STATUS_PENDING_VERIFICATION, [
            'status'            => HazardReport::STATUS_CLOSED,
            'closed_by'         => $request->user()->id,
            'closed_at'         => now(),
            'verification_note' => $validated['verification_note'] ?? null,
        ]);

        if (! $ok) {
            return $this->alreadyProcessed($hazardReport);
        }

        // Email notifikasi (gagal kirim tidak membatalkan proses)
        $this->notifier()->closed($hazardReport, $request->user());

        return redirect()->route('hazard-report.show', $hazardReport)
            ->with('success', "Laporan {$hazardReport->report_number} sudah Closed.");
    }

    public function returnClosing(Request $request, HazardReport $hazardReport)
    {
        $validated = $request->validate([
            'verification_note' => ['required', 'string', 'max:1000'],
        ], [
            'verification_note.required' => 'Jelaskan kenapa bukti perbaikan dikembalikan, supaya PIC tahu apa yang harus diperbaiki.',
        ]);

        $oldPhotos = $hazardReport->closingPhotos()->get();

        $ok = DB::transaction(function () use ($hazardReport, $validated, $oldPhotos) {
            $ok = $this->transition($hazardReport, HazardReport::STATUS_PENDING_VERIFICATION, [
                // Kalau sebelumnya sudah pernah update progres, kembali ke "Dalam Proses"
                'status'               => $hazardReport->isExtended() ? HazardReport::STATUS_IN_PROGRESS : HazardReport::STATUS_OPEN,
                'closing_submitted_by' => null,
                'closing_submitted_at' => null,
                'closing_note'         => null,
                'verification_note'    => $validated['verification_note'],
            ]);

            if ($ok) {
                // PIC harus upload ulang bukti yang baru
                HazardReportPhoto::whereIn('id', $oldPhotos->pluck('id'))->delete();
            }

            return $ok;
        });

        if (! $ok) {
            return $this->alreadyProcessed($hazardReport);
        }

        Uploads::delete($oldPhotos->pluck('path')->all());

        // Email notifikasi (gagal kirim tidak membatalkan proses)
        $this->notifier()->closingReturned($hazardReport, $request->user());

        return redirect()->route('hazard-report.show', $hazardReport)
            ->with('success', "Bukti perbaikan {$hazardReport->report_number} dikembalikan ke PIC.");
    }

    // =================================================================
    // REKAP PELAPOR (HSE): berapa kali tiap orang melapor per bulan
    // =================================================================

    public function reporters(Request $request)
    {
        // Bulan yang direkap, format 2026-11. Kalau tidak valid, pakai bulan ini.
        $month = (string) $request->query('month', now()->format('Y-m'));
        try {
            $start = preg_match('/^\d{4}-\d{2}$/', $month)
                ? Carbon::createFromFormat('!Y-m', $month)->startOfMonth()
                : now()->startOfMonth();
        } catch (\Throwable $e) {
            $start = now()->startOfMonth();
        }
        $end   = $start->copy()->endOfMonth();
        $month = $start->format('Y-m');

        $search    = trim((string) $request->query('q', ''));
        $deptId    = $request->query('department');
        $companyId = $request->query('company');
        $onlyZero  = $request->boolean('zero');
        $target    = (int) config('hazard.monthly_target', 0);

        // Jumlah laporan per pelapor di bulan tersebut (dihitung dari tanggal kirim)
        $monthly = HazardReport::query()
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('
                reporter_id,
                COUNT(*) as total,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as rejected,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as closed
            ', [HazardReport::STATUS_REJECTED, HazardReport::STATUS_PENDING_VALIDATION, HazardReport::STATUS_CLOSED])
            ->groupBy('reporter_id')
            ->get()
            ->keyBy('reporter_id');

        // Total sepanjang waktu, untuk pembanding
        $allTime = HazardReport::query()
            ->selectRaw('reporter_id, COUNT(*) as total')
            ->groupBy('reporter_id')
            ->pluck('total', 'reporter_id');

        $rows = User::with(['company', 'department'])
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%{$search}%")
                ->orWhere('employee_number', 'like', "%{$search}%")))
            ->when($deptId, fn ($q) => $q->where('department_id', $deptId))
            ->when($companyId, fn ($q) => $q->where('company_id', $companyId))
            ->orderBy('name')
            ->get()
            ->map(function (User $u) use ($monthly, $allTime, $target) {
                $m        = $monthly->get($u->id);
                $total    = (int) ($m->total ?? 0);
                $rejected = (int) ($m->rejected ?? 0);
                $pending  = (int) ($m->pending ?? 0);
                $counted  = $total - $rejected; // yang ditolak tidak dihitung ke target

                return (object) [
                    'user'       => $u,
                    'total'      => $total,
                    'valid'      => $total - $rejected - $pending,
                    'rejected'   => $rejected,
                    'pending'    => $pending,
                    'closed'     => (int) ($m->closed ?? 0),
                    'counted'    => $counted,
                    'all_time'   => (int) ($allTime[$u->id] ?? 0),
                    'target_met' => $target > 0 && $counted >= $target,
                ];
            })
            ->when($onlyZero, fn ($c) => $c->where('total', 0))
            ->sortBy([['total', 'desc'], ['user.name', 'asc']])
            ->values();

        $reporting = $rows->where('total', '>', 0)->count();

        return view('hazard-reports.manage.reporters', [
            'rows'       => $rows,
            'summary'    => [
                'reports'    => $rows->sum('total'),
                'reporting'  => $reporting,
                'people'     => $rows->count(),
                'average'    => $rows->count() ? round($rows->sum('total') / $rows->count(), 1) : 0,
                'target_met' => $rows->where('target_met', true)->count(),
            ],
            'target'      => $target,
            'month'       => $month,
            'monthLabel'  => $start->translatedFormat('F Y'),
            'search'      => $search,
            'deptId'      => $deptId,
            'companyId'   => $companyId,
            'onlyZero'    => $onlyZero,
            'departments' => Department::orderBy('name')->get(),
            'companies'   => Company::orderBy('name')->get(),
        ]);
    }

    // =================================================================
    // BANTUAN
    // =================================================================

    private function isHse(User $user): bool
    {
        return $user->hasAnyRole(['superadmin', 'qhse']);
    }

    /** Query laporan yang boleh dilihat user di Tasklist. */
    private function visibleTo(User $user): Builder
    {
        if ($this->isHse($user)) {
            return HazardReport::query();
        }

        // PIC: hanya laporan untuk departemennya yang sudah divalidasi.
        // User tanpa departemen tidak melihat apa pun.
        return HazardReport::query()
            ->where('pic_department_id', $user->department_id ?? 0)
            ->whereIn('status', self::PIC_STATUSES);
    }

    private function canView(User $user, HazardReport $report): bool
    {
        return $this->isHse($user)
            || ($user->department_id
                && (int) $report->pic_department_id === (int) $user->department_id
                && in_array($report->status, self::PIC_STATUSES, true));
    }

    /** Boleh upload bukti closing: PIC departemen terkait, atau superadmin. */
    private function canFollowUp(User $user, HazardReport $report): bool
    {
        if (! in_array($report->status, HazardReport::FOLLOW_UP_STATUSES, true)) {
            return false;
        }

        return $user->hasRole('superadmin')
            || ($user->hasRole('pic_dept')
                && $user->department_id
                && (int) $report->pic_department_id === (int) $user->department_id);
    }

    /**
     * Ubah status hanya kalau status saat ini masih sesuai.
     * Mencegah dua orang memproses laporan yang sama bersamaan.
     */
    private function transition(HazardReport $report, string|array $fromStatus, array $data): bool
    {
        return (bool) HazardReport::whereKey($report->id)
            ->whereIn('status', (array) $fromStatus)
            ->update($data);
    }

    private function notifier(): HazardNotifier
    {
        return app(HazardNotifier::class);
    }

    private function alreadyProcessed(HazardReport $report)
    {
        return redirect()->route('hazard-report.show', $report)
            ->withErrors(['Laporan ini sudah diproses oleh orang lain. Halaman sudah dimuat ulang dengan status terbaru.']);
    }
}