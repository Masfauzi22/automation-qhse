<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\HazardCategory;
use App\Models\HazardPriority;
use App\Models\HazardReport;
use App\Models\HazardReportPhoto;
use App\Services\HazardNotifier;
use App\Support\Uploads;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * Hazard Report milik user yang sedang login (menu Personal Record).
 * Validasi HSE dan follow up PIC dibuat terpisah di tahap berikutnya.
 */
class MyHazardReportController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $status = $request->query('status');

        $reports = HazardReport::with(['category', 'priority', 'picDepartment'])
            ->where('reporter_id', $userId)
            ->when($status && array_key_exists($status, HazardReport::STATUSES), fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        // Jumlah laporan per status, untuk kartu ringkasan
        $counts = HazardReport::where('reporter_id', $userId)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $stats = HazardReport::reporterStats($userId);

        return view('hazard-reports.my.index', compact('reports', 'counts', 'status', 'stats'));
    }

    public function create(Request $request)
    {
        return view('hazard-reports.my.create', [
            'user'        => $request->user()->load(['company', 'department']),
            'stats'       => HazardReport::reporterStats($request->user()->id),
            'categories'  => HazardCategory::active()->get(),
            'priorities'  => HazardPriority::active()->get(),
            'departments' => Department::orderBy('name')->get(),
            'minDate'     => today()->subDays(config('hazard.max_backdate_days', 7)),
            'maxPhotos'   => config('hazard.max_photos', 3),
            'maxPhotoKb'  => config('hazard.max_photo_kb', 5120),
        ]);
    }

    public function store(Request $request)
    {
        $maxPhotos = config('hazard.max_photos', 3);
        $maxKb     = config('hazard.max_photo_kb', 5120);
        $minDate   = today()->subDays(config('hazard.max_backdate_days', 7));

        $validated = $request->validate([
            'finding_date'       => ['required', 'date', 'before_or_equal:today', 'after_or_equal:' . $minDate->toDateString()],
            'hazard_category_id' => ['required', Rule::exists('hazard_categories', 'id')->where('is_active', true)],
            'hazard_priority_id' => ['required', Rule::exists('hazard_priorities', 'id')->where('is_active', true)],
            'location'           => ['required', 'string', 'max:255'],
            'latitude'           => [config('hazard.map.require_coordinates') ? 'required' : 'nullable', 'numeric', 'between:-90,90', 'required_with:longitude'],
            'longitude'          => [config('hazard.map.require_coordinates') ? 'required' : 'nullable', 'numeric', 'between:-180,180', 'required_with:latitude'],
            'location_accuracy'  => ['nullable', 'integer', 'min:0', 'max:65535'],
            'description'        => ['required', 'string', 'max:2000'],
            'potential_hazard'   => ['required', 'string', 'max:2000'],
            'recommendation'     => ['required', 'string', 'max:2000'],
            'pic_department_id'  => ['required', 'exists:departments,id'],
            'finding_photos'     => ['required', 'array', 'min:1', 'max:' . $maxPhotos],
            'finding_photos.*'   => ['image', 'mimes:jpg,jpeg,png,webp', 'max:' . $maxKb],
            'is_closed_on_site'  => ['nullable', 'boolean'],
            'closing_photos'     => ['nullable', 'required_if:is_closed_on_site,1', 'array', 'max:' . $maxPhotos],
            'closing_photos.*'   => ['image', 'mimes:jpg,jpeg,png,webp', 'max:' . $maxKb],
            'closing_note'       => ['nullable', 'string', 'max:1000'],
        ], [
            'required'                         => ':attribute wajib diisi.',
            'max.string'                       => ':attribute maksimal :max karakter.',
            'image'                            => 'File :attribute harus berupa gambar.',
            'mimes'                            => 'Format :attribute harus JPG, PNG, atau WEBP.',
            'finding_date.before_or_equal'     => 'Tanggal temuan tidak boleh melebihi hari ini.',
            'finding_date.after_or_equal'      => 'Tanggal temuan maksimal mundur ' . config('hazard.max_backdate_days', 7) . ' hari.',
            'latitude.required'                => 'Tandai titik lokasi temuan di peta.',
            'longitude.required'               => 'Tandai titik lokasi temuan di peta.',
            'finding_photos.required'          => 'Foto bukti temuan wajib diupload minimal 1 foto.',
            'finding_photos.max'               => 'Foto bukti temuan maksimal :max foto.',
            'closing_photos.required_if'       => 'Foto bukti closing wajib diupload kalau temuan langsung diperbaiki.',
            'closing_photos.max'               => 'Foto bukti closing maksimal :max foto.',
            'finding_photos.*.max'             => 'Ukuran setiap foto maksimal ' . round($maxKb / 1024) . ' MB.',
            'closing_photos.*.max'             => 'Ukuran setiap foto maksimal ' . round($maxKb / 1024) . ' MB.',
            'finding_photos.*.uploaded'        => 'Foto gagal diupload. Kemungkinan ukurannya melebihi batas server.',
            'closing_photos.*.uploaded'        => 'Foto gagal diupload. Kemungkinan ukurannya melebihi batas server.',
        ], [
            'finding_date'       => 'Tanggal temuan',
            'hazard_category_id' => 'Kategori bahaya',
            'hazard_priority_id' => 'Kategori prioritas',
            'location'           => 'Lokasi temuan',
            'latitude'           => 'Titik lokasi (latitude)',
            'longitude'          => 'Titik lokasi (longitude)',
            'description'        => 'Deskripsi temuan',
            'potential_hazard'   => 'Potensi bahaya',
            'recommendation'     => 'Rekomendasi perbaikan',
            'pic_department_id'  => 'Departemen PIC',
            'finding_photos'     => 'Foto bukti temuan',
            'finding_photos.*'   => 'foto bukti temuan',
            'closing_photos'     => 'Foto bukti closing',
            'closing_photos.*'   => 'foto bukti closing',
            'closing_note'       => 'Catatan perbaikan',
        ]);

        $user         = $request->user();
        $closedOnSite = $request->boolean('is_closed_on_site');
        $storedPaths  = [];

        try {
            $report = DB::transaction(function () use ($request, $validated, $user, $closedOnSite, &$storedPaths) {

                $report = $this->createWithUniqueNumber([
                    // Data pelapor disalin dari akun saat submit
                    'reporter_id'              => $user->id,
                    'reporter_name'            => $user->name,
                    'reporter_employee_number' => $user->employee_number,
                    'reporter_company_id'      => $user->company_id,
                    'reporter_department_id'   => $user->department_id,

                    'finding_date'       => $validated['finding_date'],
                    'hazard_category_id' => $validated['hazard_category_id'],
                    'hazard_priority_id' => $validated['hazard_priority_id'],
                    'location'           => $validated['location'],
                    'latitude'           => $validated['latitude'] ?? null,
                    'longitude'          => $validated['longitude'] ?? null,
                    'location_accuracy'  => isset($validated['latitude']) ? ($validated['location_accuracy'] ?? null) : null,
                    'description'        => $validated['description'],
                    'potential_hazard'   => $validated['potential_hazard'],
                    'recommendation'     => $validated['recommendation'],
                    'pic_department_id'  => $validated['pic_department_id'],

                    // Selalu menunggu validasi HSE dulu, termasuk yang sudah diperbaiki di tempat
                    'status' => HazardReport::STATUS_PENDING_VALIDATION,

                    // Kalau langsung diperbaiki di tempat, pelapor tercatat sebagai pengirim bukti closing
                    'closing_submitted_by' => $closedOnSite ? $user->id : null,
                    'closing_submitted_at' => $closedOnSite ? now() : null,
                    'closing_note'         => $closedOnSite ? ($validated['closing_note'] ?? null) : null,
                ]);

                $folder = 'hazard-reports/' . now()->format('Y/m') . '/' . $report->id;

                foreach ($request->file('finding_photos', []) as $file) {
                    $storedPaths[] = $path = $file->store($folder, Uploads::disk());
                    $report->photos()->create([
                        'type'          => HazardReportPhoto::TYPE_FINDING,
                        'path'          => $path,
                        'original_name' => $file->getClientOriginalName(),
                        'uploaded_by'   => $user->id,
                    ]);
                }

                if ($closedOnSite) {
                    foreach ($request->file('closing_photos', []) as $file) {
                        $storedPaths[] = $path = $file->store($folder, Uploads::disk());
                        $report->photos()->create([
                            'type'          => HazardReportPhoto::TYPE_CLOSING,
                            'path'          => $path,
                            'original_name' => $file->getClientOriginalName(),
                            'uploaded_by'   => $user->id,
                        ]);
                    }
                }

                return $report;
            });
        } catch (\Throwable $e) {
            // Kalau gagal di tengah jalan, hapus foto yang sudah terlanjur tersimpan
            Uploads::delete($storedPaths);
            throw $e;
        }

        // Email ke HSE: ada laporan baru (gagal kirim tidak membatalkan laporan)
        app(HazardNotifier::class)->newReport($report);

        return redirect()
            ->route('my-hazard-report.show', $report)
            ->with('success', "Laporan {$report->report_number} berhasil dikirim dan menunggu validasi HSE.");
    }

    public function show(Request $request, HazardReport $hazardReport)
    {
        $user = $request->user();

        // Hanya pelapor sendiri, HSE, atau superadmin yang boleh melihat
        abort_unless(
            (int) $hazardReport->reporter_id === (int) $user->id || $user->hasAnyRole(['superadmin', 'qhse']),
            403
        );

        $hazardReport->load([
            'category', 'priority', 'picDepartment', 'reporterCompany', 'reporterDepartment',
            'validator', 'closingSubmitter', 'closer', 'findingPhotos', 'closingPhotos',
            'updates.user', 'updates.photos', 'duplicateOf',
        ]);

        return view('hazard-reports.my.show', ['report' => $hazardReport]);
    }

    /**
     * Buat laporan dengan nomor unik. Kalau dua orang submit di detik yang sama
     * dan dapat nomor sama, coba lagi dengan nomor berikutnya.
     */
    private function createWithUniqueNumber(array $data): HazardReport
    {
        for ($attempt = 1; ; $attempt++) {
            try {
                return HazardReport::create($data + ['report_number' => HazardReport::generateNumber()]);
            } catch (UniqueConstraintViolationException $e) {
                if ($attempt >= 5) {
                    throw $e;
                }
            }
        }
    }
}