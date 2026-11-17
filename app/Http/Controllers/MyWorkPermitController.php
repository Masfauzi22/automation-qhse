<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\WorkPermit;
use App\Models\WorkPermitCheck;
use App\Models\WorkPermitFile;
use App\Models\WorkPermitGasTest;
use App\Models\WorkPermitLog;
use App\Support\Uploads;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Permit to Work milik pengaju (menu Request QHSE → Permit to Work).
 * Yang boleh mengajukan: jabatan Foreman & Supervisor.
 * Pemeriksaan atasan & approval QHSE ada di WorkPermitController (Tasklist).
 */
class MyWorkPermitController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $status = $request->query('status');

        $permits = WorkPermit::with(['ownerDepartment'])
            ->where('applicant_id', $userId)
            ->when($status && array_key_exists($status, WorkPermit::STATUSES), fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $counts = WorkPermit::where('applicant_id', $userId)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('work-permits.my.index', [
            'permits'  => $permits,
            'counts'   => $counts,
            'status'   => $status,
            'types'    => config('permits.types'),
            'canApply' => $request->user()->canApplyPermit(),
        ]);
    }

    public function create(Request $request)
    {
        $this->authorizeApplicant($request);
        $type = $request->query('type', 'hot_work');

        return view('work-permits.my.form', $this->formData(new WorkPermit([
            'type'                => $type,
            'owner_department_id' => $request->user()->department_id,
            'start_date'      => today(),
            'end_date'        => today(),
            'start_time'      => '08:00',
            'end_time'        => '17:00',
        ]), $request));
    }

    public function store(Request $request)
    {
        $this->authorizeApplicant($request);
        $type   = $request->input('type', 'hot_work');
        $config = WorkPermit::typeConfig($type);
        $data   = $this->validatePermit($request, $type, $config, null);

        $permit = $this->save($request, new WorkPermit(['type' => $type]), $type, $config, $data);

        return redirect()->route('my-permit.show', $permit)
            ->with('success', "Permit {$permit->permit_number} berhasil diajukan dan menunggu pemeriksaan.");
    }

    public function show(Request $request, WorkPermit $workPermit)
    {
        $this->authorizeOwner($request, $workPermit);

        $workPermit->load(['applicantDepartment', 'ownerDepartment', 'checks', 'gasTests', 'dailyLogs.photos', 'files', 'logs', 'jsaFile', 'certificate']);

        $isApplicant = (int) $workPermit->applicant_id === (int) $request->user()->id;

        return view('work-permits.my.show', [
            'permit'             => $workPermit,
            'isApplicant'        => $isApplicant,
            'activationBlocker'  => $workPermit->activationBlocker(),
            'canFinish'          => $isApplicant && $workPermit->canFinish(),
            'canStop'            => $isApplicant && $workPermit->status === WorkPermit::STATUS_ACTIVE,
            'dailyConfirmations' => $workPermit->config()['daily_confirmations'] ?? [],
            'dailyGasTest'       => $workPermit->needsDailyGasTest(),
            'gasLimits'          => $workPermit->gasLimits(),
            'postWatchMinutes'   => $workPermit->postWatchMinutes(),
            'maxPhotos'          => config('permits.max_photos', 3),
            'maxPhotoKb'         => config('permits.max_photo_kb', 5120),
        ]);
    }

    public function edit(Request $request, WorkPermit $workPermit)
    {
        $this->authorizeOwner($request, $workPermit);
        $this->authorizeApplicant($request);
        abort_unless($workPermit->isEditable(), 403, 'Permit ini sedang diproses dan tidak bisa diubah.');

        $workPermit->load(['checks', 'gasTests', 'files', 'jsaFile']);

        return view('work-permits.my.form', $this->formData($workPermit, $request));
    }

    public function update(Request $request, WorkPermit $workPermit)
    {
        $this->authorizeOwner($request, $workPermit);
        $this->authorizeApplicant($request);
        abort_unless($workPermit->isEditable(), 403, 'Permit ini sedang diproses dan tidak bisa diubah.');

        $config     = $workPermit->config();
        $wasPending = $workPermit->status === WorkPermit::STATUS_PENDING_CHECK;
        $data       = $this->validatePermit($request, $workPermit->type, $config, $workPermit);

        $this->save($request, $workPermit, $workPermit->type, $config, $data);

        return redirect()->route('my-permit.show', $workPermit)
            ->with('success', $wasPending
                ? "Perubahan permit {$workPermit->permit_number} disimpan. Masih menunggu pemeriksaan {$workPermit->supervisor_name}."
                : "Permit {$workPermit->permit_number} diajukan ulang dan menunggu pemeriksaan {$workPermit->supervisor_name}.");
    }

    // =================================================================
    // AKTIVASI HARIAN (kolom Day 1–7)
    // =================================================================

    public function activate(Request $request, WorkPermit $workPermit)
    {
        $this->authorizeApplicantOf($request, $workPermit);

        if ($blocker = $workPermit->activationBlocker()) {
            return back()->withErrors(['activate' => $blocker]);
        }

        $config  = $workPermit->config();
        $confirm = array_keys($config['daily_confirmations'] ?? []);
        $maxKb   = config('permits.max_photo_kb', 5120);

        $rules = [
            'daily_note'     => ['nullable', 'string', 'max:500'],
            'daily_photos'   => ['nullable', 'array', 'max:' . config('permits.max_photos', 3)],
            'daily_photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:' . $maxKb],
        ];
        foreach ($confirm as $key) {
            $rules["daily_confirm.{$key}"] = ['accepted'];
        }

        // Ruang Terbatas: ukur gas ulang tepat sebelum pekerja masuk
        $gasLimits = $workPermit->gasLimits();
        if ($workPermit->needsDailyGasTest()) {
            foreach (array_keys($gasLimits) as $gas) {
                $rules["daily_gas.{$gas}"] = ['required', 'numeric', 'min:0', 'max:100000'];
            }
            $rules['daily_gas.tested_by_name'] = ['required', 'string', 'max:150'];
        }

        $data = $request->validate($rules, [
            'daily_confirm.*.accepted' => 'Centang semua konfirmasi kesiapan sebelum mengaktifkan permit hari ini.',
            'daily_photos.*.max'       => 'Ukuran setiap foto maksimal ' . round($maxKb / 1024) . ' MB.',
            'daily_gas.*.required'     => 'Hasil ukur gas hari ini wajib diisi lengkap sebelum pekerja masuk.',
        ]);

        if ($workPermit->needsDailyGasTest() && ($violations = WorkPermitGasTest::violations($data['daily_gas'], $gasLimits))) {
            $labels = collect($violations)->keys()->map(fn ($g) => $gasLimits[$g]['label'])->join(', ');
            return back()->withInput()->withErrors([
                'daily_gas' => "Hasil ukur gas di luar batas aman ({$labels}). JANGAN MASUK. Lakukan sirkulasi udara lalu ukur ulang.",
            ]);
        }

        $user   = $request->user();
        $stored = [];

        try {
            DB::transaction(function () use ($request, $workPermit, $user, $data, $confirm, $gasLimits, &$stored) {
                $log = $workPermit->dailyLogs()->create([
                    'day_number'    => $workPermit->dayNumberFor(today()),
                    'work_date'     => today(),
                    'user_id'       => $user->id,
                    'user_name'     => $user->name,
                    'confirmations' => $confirm,
                    'note'          => $data['daily_note'] ?? null,
                ]);

                if ($workPermit->needsDailyGasTest()) {
                    $workPermit->gasTests()->create(
                        collect(array_keys($gasLimits))->mapWithKeys(fn ($g) => [$g => $data['daily_gas'][$g]])->all() + [
                            'work_permit_daily_log_id' => $log->id,
                            'tested_by_name'           => $data['daily_gas']['tested_by_name'],
                            'tested_at'                => now(),
                            'is_safe'                  => true,
                        ]
                    );
                }

                $folder = 'work-permits/' . now()->format('Y/m') . '/' . $workPermit->id . '/daily';
                foreach ($request->file('daily_photos', []) as $file) {
                    $stored[] = $path = $file->store($folder, Uploads::disk());
                    $workPermit->files()->create([
                        'kind' => WorkPermitFile::KIND_DAILY, 'work_permit_daily_log_id' => $log->id, 'path' => $path,
                        'original_name' => $file->getClientOriginalName(), 'uploaded_by' => $user->id,
                    ]);
                }
            });
        } catch (UniqueConstraintViolationException $e) {
            Uploads::delete($stored);
            return back()->withErrors(['activate' => 'Permit sudah diaktifkan hari ini.']);
        } catch (\Throwable $e) {
            Uploads::delete($stored);
            throw $e;
        }

        $day = $workPermit->dayNumberFor(today());

        return redirect()->route('my-permit.show', $workPermit)
            ->with('success', "Day {$day} aktif. Pekerjaan boleh dimulai sampai jam " . substr((string) $workPermit->end_time, 0, 5) . '.');
    }

    // =================================================================
    // PEKERJAAN SELESAI → CLOSE-OUT
    // =================================================================

    public function finish(Request $request, WorkPermit $workPermit)
    {
        $this->authorizeApplicantOf($request, $workPermit);
        abort_unless($workPermit->canFinish(), 403, 'Permit ini belum bisa di-close-out.');

        $minutes = $workPermit->postWatchMinutes();
        $maxKb   = config('permits.max_photo_kb', 5120);

        $data = $request->validate([
            'work_finished_at'   => ['required', 'date', 'before_or_equal:now'],
            // Hanya jenis permit yang punya pengawasan setelah pekerjaan (mis. Pekerjaan Panas 60 menit)
            'post_watch_until'   => $minutes > 0
                                        ? ['required', 'date', 'after:work_finished_at', 'before_or_equal:now']
                                        : ['nullable'],
            'final_condition'    => ['required', 'string', 'max:1000'],
            'closeout_photos'    => ['required', 'array', 'min:1', 'max:' . config('permits.max_photos', 3)],
            'closeout_photos.*'  => ['image', 'mimes:jpg,jpeg,png,webp', 'max:' . $maxKb],
            'closeout_confirm'   => ['accepted'],
        ], [
            'required'                        => ':attribute wajib diisi.',
            'post_watch_until.after'          => 'Jam selesai pengawasan harus setelah jam pekerjaan selesai.',
            'work_finished_at.before_or_equal'=> 'Jam pekerjaan selesai tidak boleh di masa depan.',
            'post_watch_until.before_or_equal'=> 'Pengawasan belum selesai. Isi close-out setelah pengawasan berakhir.',
            'closeout_photos.required'        => 'Upload minimal 1 foto kondisi area setelah pekerjaan.',
            'closeout_confirm.accepted'       => 'Centang pernyataan bahwa area sudah aman.',
        ], [
            'work_finished_at' => 'Jam pekerjaan selesai',
            'post_watch_until' => 'Jam pengawasan berakhir',
            'final_condition'  => 'Kondisi akhir area',
        ]);

        $finished = \Illuminate\Support\Carbon::parse($data['work_finished_at']);
        $watched  = $minutes > 0 ? \Illuminate\Support\Carbon::parse($data['post_watch_until']) : $finished->copy();

        if ($minutes > 0 && $finished->diffInMinutes($watched) < $minutes) {
            return back()->withInput()->withErrors([
                'post_watch_until' => "Pengawasan setelah pekerjaan minimal {$minutes} menit (butir pengawasan: \"1 jam setelah pekerjaan selesai\").",
            ]);
        }

        $user   = $request->user();
        $stored = [];

        try {
            $ok = DB::transaction(function () use ($request, $workPermit, $user, $data, $finished, $watched, $minutes, &$stored) {
                $updated = WorkPermit::whereKey($workPermit->id)
                    ->whereIn('status', [WorkPermit::STATUS_ACTIVE, WorkPermit::STATUS_EXPIRED])
                    ->update([
                        'status'           => WorkPermit::STATUS_PENDING_CLOSE_CHECK,
                        'work_finished_at' => $finished,
                        'post_watch_until' => $watched,
                        'final_condition'  => $data['final_condition'],
                        'last_note'        => null,
                    ]);
                if (! $updated) {
                    return false;
                }

                // Foto close-out lama (kalau close-out sebelumnya dikembalikan) diganti yang baru
                $old = $workPermit->files()->where('kind', WorkPermitFile::KIND_CLOSEOUT)->get();

                $folder = 'work-permits/' . now()->format('Y/m') . '/' . $workPermit->id . '/closeout';
                foreach ($request->file('closeout_photos', []) as $file) {
                    $stored[] = $path = $file->store($folder, Uploads::disk());
                    $workPermit->files()->create([
                        'kind' => WorkPermitFile::KIND_CLOSEOUT, 'path' => $path,
                        'original_name' => $file->getClientOriginalName(), 'uploaded_by' => $user->id,
                    ]);
                }
                if ($old->isNotEmpty()) {
                    Uploads::delete($old->pluck('path')->all());
                    WorkPermitFile::whereKey($old->pluck('id'))->delete();
                }

                $workPermit->logs()->create([
                    'action'     => 'finished',
                    'user_id'    => $user->id,
                    'user_name'  => $user->name,
                    'user_title' => trim($user->positionLabel() . ' ' . ($user->department?->name ?? '')),
                    'note'       => "Selesai {$finished->format('d/m H:i')}"
                                    . ($minutes > 0 ? ", pengawasan s.d. {$watched->format('H:i')}" : '')
                                    . '. ' . $data['final_condition'],
                ]);
                return true;
            });
        } catch (\Throwable $e) {
            Uploads::delete($stored);
            throw $e;
        }

        if (! $ok) {
            Uploads::delete($stored);
            return redirect()->route('my-permit.show', $workPermit)->withErrors(['Status permit sudah berubah. Halaman dimuat ulang.']);
        }

        return redirect()->route('my-permit.show', $workPermit)
            ->with('success', 'Close-out terkirim dan menunggu pemeriksaan atasan.');
    }

    // =================================================================
    // BANTUAN
    // =================================================================

    private function formData(WorkPermit $permit, Request $request): array
    {
        $config     = $permit->config();
        $linkedItem = WorkPermit::linkedItemOf($config);

        return [
            'permit'      => $permit,
            'config'      => $config,
            'watcher'     => WorkPermit::watcherConfig($config),
            // Pilihan permit yang bisa dikaitkan (mis. permit Pekerjaan Panas untuk butir 8 Ketinggian)
            'linkable'    => $linkedItem ? $this->linkableQuery($linkedItem[1]['linked_type'], $permit)
                                ->orderByDesc('start_date')->limit(100)
                                ->get(['id', 'permit_number', 'type', 'project_name', 'location', 'status', 'start_date', 'end_date'])
                                : collect(),
            'user'        => $request->user()->load(['company', 'department']),
            // Dropdown "Diperiksa oleh": atasan di departemen pengaju
            'checkers'    => WorkPermit::checkerCandidates($request->user()->department_id, $request->user()->position, $request->user()->id),
            'departments' => Department::orderBy('name')->get(),
            'checks'      => $permit->exists ? $permit->checks->keyBy('item_key') : collect(),
            'gasTest'     => $permit->exists ? $permit->openGasTest() : null,
            'gasLimits'   => WorkPermit::gasLimitsFor($config),
            'jsaRequired' => WorkPermit::jsaRequiredFor($config),
            'maxPhotos'   => config('permits.max_photos', 3),
            'maxPhotoKb'  => config('permits.max_photo_kb', 5120),
            'maxJsaKb'    => config('permits.max_jsa_kb', 5120),
        ];
    }

    private function validatePermit(Request $request, string $type, array $config, ?WorkPermit $existing): array
    {
        $maxDays  = $config['max_days'];
        $hasJsa   = $existing?->jsaFile !== null;
        $docKb    = config('permits.max_jsa_kb', 5120);

        // Permit 1 hari (Ruang Terbatas): tanggal selesai = tanggal mulai
        if ($maxDays === 1) {
            $request->merge(['end_date' => $request->input('start_date')]);
        }
        $photoKb  = config('permits.max_photo_kb', 5120);
        $start    = $request->date('start_date');
        $watcher  = WorkPermit::watcherConfig($config);
        $extraMax = (int) ($config['extra_notes'] ?? 0);
        $applicant  = $request->user();
        $checkerIds = WorkPermit::checkerCandidates($applicant->department_id, $applicant->position, $applicant->id)
                        ->pluck('id')->all();

        $rules = [
            'type'                  => ['required', Rule::in(array_keys(config('permits.types')))],
            'owner_department_id'   => ['required', 'exists:departments,id'],
            'project_name'          => ['required', 'string', 'max:255'],
            'location'              => ['required', 'string', 'max:255'],
            'latitude'              => ['nullable', 'numeric', 'between:-90,90', 'required_with:longitude'],
            'longitude'             => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitude'],
            // Pemeriksa dipilih dari dropdown (atasan di departemen pengaju)
            'checker_id'            => ['required', 'integer', Rule::in($checkerIds)],
            'worker_count'          => ['required', 'integer', 'min:1', 'max:500'],
            'tools'                 => ['required', 'string', 'max:1000'],
            'fire_watch_name'       => ['required', 'string', 'max:150'],

            // Jenis pekerjaan hanya untuk form yang punya pilihannya (Panas, Ketinggian)
            'work_types'            => $config['work_types'] ? ['required', 'array', 'min:1'] : ['nullable', 'prohibited'],
            'work_types.*'          => [Rule::in(array_keys($config['work_types']))],
            'work_type_other'       => ['nullable', 'string', 'max:150', Rule::requiredIf((bool) $config['work_types'] && in_array('lain', (array) $request->input('work_types'), true))],

            'start_date'            => ['required', 'date', 'after_or_equal:today'],
            'end_date'              => array_filter(['required', 'date', 'after_or_equal:start_date',
                                        $start ? 'before_or_equal:' . $start->copy()->addDays($maxDays - 1)->toDateString() : null]),
            'start_time'            => ['required', 'date_format:H:i'],
            'end_time'              => ['required', 'date_format:H:i', 'after:start_time'],

            'checks'                => ['required', 'array'],
            'check_notes'           => ['nullable', 'array'],
            'check_notes.*'         => ['nullable', 'string', 'max:500'],
            'check_photos'          => ['nullable', 'array'],
            'check_photos.*'        => ['nullable', 'array', 'max:' . config('permits.max_photos', 3)],
            'check_photos.*.*'      => ['image', 'mimes:jpg,jpeg,png,webp', 'max:' . $photoKb],

            'jsa_file'              => [WorkPermit::jsaRequiredFor($config) && ! $hasJsa ? 'required' : 'nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:' . config('permits.max_jsa_kb', 5120)],
            'declaration'           => ['accepted'],

            // Keterangan lain yang diperlukan (hanya jenis yang punya, mis. Ketinggian)
            'extra_notes'           => ['nullable', 'array', 'max:' . max($extraMax, 0)],
            'extra_notes.*'         => ['nullable', 'string', 'max:255'],
        ];
        $specificMessages = [];

        // Setiap butir checklist wajib dijawab; "Tidak Berlaku" hanya untuk butir yang mengizinkan
        foreach ($config['checklist'] as $key => $item) {
            $rules["checks.{$key}"] = ['required', Rule::in($item['allow_na'] ? ['yes', 'na'] : ['yes'])];
        }

        // Butir yang mengaitkan permit lain (mis. butir 8 Ketinggian → permit Pekerjaan Panas)
        if ($linked = WorkPermit::linkedItemOf($config)) {
            [$linkKey, $linkItem] = $linked;
            $linkLabel = config("permits.types.{$linkItem['linked_type']}.label", 'terkait');

            // Mengelas / memotong / menggerinda di ketinggian → wajib punya permit Pekerjaan Panas
            $hotPicked = array_intersect($linkItem['required_for_work_types'] ?? [], (array) $request->input('work_types'));
            if ($hotPicked) {
                $rules["checks.{$linkKey}"] = ['required', Rule::in(['yes'])];
                $names = collect($hotPicked)->map(fn ($k) => strtolower($config['work_types'][$k] ?? $k))->join(', ', ' & ');
                $specificMessages["checks.{$linkKey}.in"] = "Pekerjaan {$names} wajib disertai permit {$linkLabel}. Ajukan permit {$linkLabel} dulu, lalu pilih nomornya di butir \"{$linkItem['text']}\"";
            }

            $rules['linked_permit_id'] = [
                'nullable',
                Rule::requiredIf($request->input("checks.{$linkKey}") === 'yes'),
                'integer',
                Rule::exists('work_permits', 'id')
                    ->where('type', $linkItem['linked_type'])
                    ->whereIn('status', WorkPermit::LINKABLE_STATUSES)
                    ->whereNull('deleted_at')
                    ->where(fn ($q) => $q->whereDate('end_date', '>=', today())),
            ];
            $specificMessages['linked_permit_id.required'] = "Pilih nomor permit {$linkLabel} yang terkait.";
            $specificMessages['linked_permit_id.exists']   = "Permit {$linkLabel} yang dipilih tidak ditemukan, sudah ditutup, atau sudah lewat masa berlaku.";
        }

        // Lampiran dokumen per butir (mis. denah kabel / pipa pada Penggalian)
        foreach ($config['checklist'] as $key => $item) {
            if (($item['requires'] ?? null) !== 'document') {
                continue;
            }
            $hasDoc = $existing && $existing->files()->where('kind', WorkPermitFile::KIND_CHECK)->where('item_key', $key)->exists();
            $rules["check_docs.{$key}"] = [
                $request->input("checks.{$key}") === 'yes' && ! $hasDoc ? 'required' : 'nullable',
                'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:' . $docKb,
            ];
            $specificMessages["check_docs.{$key}.required"] = 'Lampirkan file untuk butir: "' . $item['text'] . '"';
        }

        // Hasil ukur gas wajib kalau butir pengukuran gas dijawab "Ya"
        $gasLimits = WorkPermit::gasLimitsFor($config);
        $gasKey    = WorkPermit::itemRequiring($config, 'gas_test')[0] ?? null;
        $gasNeeded = $gasKey && $request->input("checks.{$gasKey}") === 'yes';
        foreach (array_keys($gasLimits) as $gas) {
            $rules["gas.{$gas}"] = [$gasNeeded ? 'required' : 'nullable', 'numeric', 'min:0', 'max:1000'];
        }
        $rules['gas.tested_by_name'] = [$gasNeeded ? 'required' : 'nullable', 'string', 'max:150'];
        $rules['gas.tested_at']      = [$gasNeeded ? 'required' : 'nullable', 'date', 'before_or_equal:now'];

        $messages = [
            'checker_id.required' => 'Pilih atasan yang akan memeriksa permit ini.',
            'checker_id.in'       => 'Atasan yang dipilih tidak bisa memeriksa permit ini. Pilih lagi dari daftar.',
            'required'                       => ':attribute wajib diisi.',
            'work_types.required'            => 'Pilih minimal satu jenis pekerjaan.',
            'work_type_other.required'       => 'Tuliskan jenis pekerjaan lain-lain.',
            'start_date.after_or_equal'      => 'Tanggal mulai tidak boleh sebelum hari ini.',
            'end_date.after_or_equal'        => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
            'end_date.before_or_equal'       => "Permit berlaku maksimal {$maxDays} hari. Untuk pekerjaan lebih lama, ajukan permit baru.",
            'end_time.after'                 => 'Jam selesai harus setelah jam mulai.',
            'checks.*.required'              => 'Semua butir Daftar Pemeriksaan Keselamatan wajib dijawab.',
            'checks.*.in'                    => 'Butir wajib harus dipenuhi ("Ya") sebelum permit bisa diajukan.',
            'jsa_file.required'              => 'Lampiran JSA wajib diupload.',
            'jsa_file.mimes'                 => 'JSA harus berupa PDF atau foto (JPG/PNG/WEBP).',
            'jsa_file.max'                   => 'Ukuran file JSA maksimal ' . round(config('permits.max_jsa_kb', 5120) / 1024) . ' MB.',
            'check_photos.*.*.max'           => 'Ukuran setiap foto maksimal ' . round($photoKb / 1024) . ' MB.',
            'gas.*.required'                 => 'Hasil pengukuran gas wajib diisi lengkap.',
            'gas.tested_at.before_or_equal'  => 'Waktu pengukuran gas tidak boleh di masa depan.',
            'work_types.prohibited'          => 'Jenis permit ini tidak memakai pilihan jenis pekerjaan.',
            'check_docs.*.mimes'             => 'Lampiran harus berupa PDF atau foto (JPG/PNG/WEBP).',
            'check_docs.*.max'               => 'Ukuran setiap lampiran maksimal ' . round($docKb / 1024) . ' MB.',
            'declaration.accepted'           => 'Centang pernyataan bahwa data yang diisi sudah benar.',
        ];

        $attributes = [
            'owner_department_id' => 'Departemen pemilik area',
            'project_name'        => 'Nama pekerjaan',
            'location'            => 'Lokasi kerja',
            'checker_id'          => 'Pemeriksa',
            'worker_count'        => 'Jumlah pekerja',
            'tools'               => 'Alat yang digunakan',
            'fire_watch_name'     => $watcher['label'],
            'start_date'          => 'Tanggal mulai',
            'end_date'            => 'Tanggal selesai',
            'start_time'          => 'Jam mulai',
            'end_time'            => 'Jam selesai',
            'gas.tested_by_name'  => 'Nama petugas ukur gas',
            'gas.tested_at'       => 'Waktu pengukuran gas',
        ];

        // Pesan khusus harus di depan supaya tidak kalah oleh pesan umum 'checks.*.in'
        $data = $request->validate($rules, $specificMessages + $messages, $attributes);

        // Batas jam: Ruang Terbatas berlaku maksimal 8 jam
        if ($maxHours = (int) ($config['max_hours'] ?? 0)) {
            $minutes = \Illuminate\Support\Carbon::parse($data['start_time'])->diffInMinutes(\Illuminate\Support\Carbon::parse($data['end_time']));
            if ($minutes > $maxHours * 60) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'end_time' => "Permit {$config['label']} berlaku maksimal {$maxHours} jam. Lebih dari itu, ajukan permit baru.",
                ]);
            }
        }

        // Hasil gas di luar batas aman → permit tidak boleh diajukan
        if ($gasNeeded) {
            $violations = WorkPermitGasTest::violations($data['gas'], $gasLimits);
            if ($violations) {
                $labels = collect($violations)->keys()->map(fn ($g) => $gasLimits[$g]['label'])->join(', ');
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'gas' => "Hasil ukur gas di luar batas aman ({$labels}). Pekerjaan tidak boleh dilakukan sampai kondisi aman dan diukur ulang.",
                ]);
            }
        }

        return $data;
    }

    private function save(Request $request, WorkPermit $permit, string $type, array $config, array $data): WorkPermit
    {
        $user        = $request->user();
        $storedPaths = [];

        try {
            DB::transaction(function () use ($request, $permit, $type, $config, $data, $user, &$storedPaths) {
                $isResubmit = $permit->exists;
                $prevStatus = $permit->status;

                // Kunci baris supaya tidak bentrok dengan atasan yang sedang memeriksa.
                // Kalau ternyata sudah diperiksa duluan, perubahan dibatalkan.
                if ($isResubmit) {
                    $current = WorkPermit::whereKey($permit->id)->lockForUpdate()->value('status');
                    if (! in_array($current, WorkPermit::EDITABLE_STATUSES, true)) {
                        throw new HttpResponseException(
                            redirect()->route('my-permit.show', $permit)
                                ->withErrors(['Permit ini sudah diperiksa atasan, jadi perubahanmu tidak disimpan. Kalau ada yang salah, minta atasan mengembalikan permit untuk revisi.'])
                        );
                    }
                }

                $checker = \App\Models\User::find($data['checker_id']);

                // Data tambahan per jenis permit (kolom JSON details)
                $details = $permit->details ?? [];
                unset($details['extra_notes'], $details['linked_permit_id'], $details['linked_permit_number']);

                if ((int) ($config['extra_notes'] ?? 0) > 0) {
                    $notes = collect($data['extra_notes'] ?? [])->map(fn ($v) => trim((string) $v))->filter()->values()->all();
                    if ($notes) {
                        $details['extra_notes'] = $notes;
                    }
                }
                if (($linked = WorkPermit::linkedItemOf($config)) && ($data['checks'][$linked[0]] ?? null) === 'yes') {
                    $linkedPermit = WorkPermit::find($data['linked_permit_id']);
                    $details['linked_permit_id']     = $linkedPermit?->id;
                    $details['linked_permit_number'] = $linkedPermit?->permit_number;
                }

                $permit->fill([
                    'type'                  => $type,
                    'status'                => WorkPermit::STATUS_PENDING_CHECK,
                    'applicant_id'          => $permit->applicant_id ?? $user->id,
                    'applicant_name'        => $permit->applicant_name ?? $user->name,
                    // Jabatan & departemen saat pengajuan menentukan siapa atasan yang memeriksa
                    'applicant_position'      => $user->position,
                    'applicant_department_id' => $user->department_id,
                    'executor_type'         => 'internal',
                    'contractor_company_id' => null,
                    'owner_department_id'   => $data['owner_department_id'],
                    'project_name'          => $data['project_name'],
                    'location'              => $data['location'],
                    'latitude'              => $data['latitude'] ?? null,
                    'longitude'             => $data['longitude'] ?? null,
                    'checker_id'            => $checker->id,
                    'supervisor_name'       => $checker->name, // catatan nama pemeriksa
                    'worker_count'          => $data['worker_count'],
                    'tools'                 => $data['tools'],
                    'fire_watch_name'       => $data['fire_watch_name'],
                    'work_types'            => array_values($data['work_types'] ?? []),
                    'details'               => $details ?: null,
                    'work_type_other'       => in_array('lain', $data['work_types'] ?? [], true) ? $data['work_type_other'] : null,
                    'start_date'            => $data['start_date'],
                    'end_date'              => $data['end_date'],
                    'start_time'            => $data['start_time'],
                    'end_time'              => $data['end_time'],
                    'last_note'             => null,
                    'submitted_at'          => now(),
                ]);

                if (! $permit->permit_number) {
                    $this->saveWithUniqueNumber($permit, $type);
                } else {
                    $permit->save();
                }

                // Checklist (disalin dengan teksnya)
                foreach ($config['checklist'] as $key => $item) {
                    WorkPermitCheck::updateOrCreate(
                        ['work_permit_id' => $permit->id, 'item_key' => $key],
                        ['item_text' => $item['text'], 'answer' => $data['checks'][$key], 'note' => $data['check_notes'][$key] ?? null],
                    );
                }

                // Hasil ukur gas (gas yang diukur sesuai jenis permit)
                $gasKey = WorkPermit::itemRequiring($config, 'gas_test')[0] ?? null;
                if ($gasKey && ($data['checks'][$gasKey] ?? null) === 'yes') {
                    $gasLimits = WorkPermit::gasLimitsFor($config);
                    $permit->gasTests()->create(
                        collect(array_keys($gasLimits))->mapWithKeys(fn ($g) => [$g => $data['gas'][$g]])->all() + [
                            'tested_by_name' => $data['gas']['tested_by_name'],
                            'tested_at'      => $data['gas']['tested_at'],
                            'is_safe'        => true,
                        ]
                    );
                }

                $folder = 'work-permits/' . now()->format('Y/m') . '/' . $permit->id;

                // JSA (versi baru menggantikan yang lama)
                if ($request->hasFile('jsa_file')) {
                    $old  = $permit->files()->where('kind', WorkPermitFile::KIND_JSA)->get();
                    $file = $request->file('jsa_file');
                    $storedPaths[] = $path = $file->store($folder, Uploads::disk());
                    $permit->files()->create([
                        'kind' => WorkPermitFile::KIND_JSA, 'path' => $path,
                        'original_name' => $file->getClientOriginalName(), 'uploaded_by' => $user->id,
                    ]);
                    if ($old->isNotEmpty()) {
                        Uploads::delete($old->pluck('path')->all());
                        WorkPermitFile::whereKey($old->pluck('id'))->delete();
                    }
                }

                // Lampiran dokumen per butir (versi baru menggantikan yang lama)
                foreach ($request->file('check_docs', []) as $key => $file) {
                    if ((($config['checklist'][$key]['requires'] ?? null) !== 'document') || ! $file) {
                        continue;
                    }
                    $old = $permit->files()->where('kind', WorkPermitFile::KIND_CHECK)->where('item_key', $key)->get();
                    $storedPaths[] = $path = $file->store($folder, Uploads::disk());
                    $permit->files()->create([
                        'kind' => WorkPermitFile::KIND_CHECK, 'item_key' => $key, 'path' => $path,
                        'original_name' => $file->getClientOriginalName(), 'uploaded_by' => $user->id,
                    ]);
                    if ($old->isNotEmpty()) {
                        Uploads::delete($old->pluck('path')->all());
                        WorkPermitFile::whereKey($old->pluck('id'))->delete();
                    }
                }

                // Foto bukti per butir checklist
                foreach ($request->file('check_photos', []) as $key => $files) {
                    if (! array_key_exists($key, $config['checklist'])) {
                        continue;
                    }
                    foreach ((array) $files as $file) {
                        $storedPaths[] = $path = $file->store($folder, Uploads::disk());
                        $permit->files()->create([
                            'kind' => WorkPermitFile::KIND_CHECK, 'item_key' => $key, 'path' => $path,
                            'original_name' => $file->getClientOriginalName(), 'uploaded_by' => $user->id,
                        ]);
                    }
                }

                $permit->logs()->create([
                    'action'     => 'submitted',
                    'user_id'    => $user->id,
                    'user_name'  => $user->name,
                    'user_title' => trim($user->positionLabel() . ' ' . ($user->department?->name ?? '')),
                    'note'       => match (true) {
                        ! $isResubmit                                    => "Diperiksa oleh {$checker->name}",
                        $prevStatus === WorkPermit::STATUS_PENDING_CHECK => "Pengajuan diperbarui sebelum diperiksa. Pemeriksa: {$checker->name}",
                        default                                          => "Diajukan ulang setelah revisi. Pemeriksa: {$checker->name}",
                    },
                ]);
            });
        } catch (\Throwable $e) {
            Uploads::delete($storedPaths);
            throw $e;
        }

        return $permit;
    }

    // Permit lain yang boleh dikaitkan: masih berjalan & belum lewat masa berlaku.
    // Permit yang sedang dikaitkan tetap muncul walau statusnya sudah berubah (supaya terlihat saat revisi).
    private function linkableQuery(string $type, WorkPermit $permit)
    {
        $currentId = $permit->details['linked_permit_id'] ?? null;

        return WorkPermit::ofType($type)->where(fn ($q) => $q
            ->where(fn ($w) => $w->whereIn('status', WorkPermit::LINKABLE_STATUSES)->whereDate('end_date', '>=', today()))
            ->when($currentId, fn ($w) => $w->orWhere('id', $currentId)));
    }

    // Nomor unik; kalau bentrok (dua orang submit bersamaan) coba nomor berikutnya
    private function saveWithUniqueNumber(WorkPermit $permit, string $type): void
    {
        for ($attempt = 1; ; $attempt++) {
            try {
                $permit->permit_number = WorkPermit::generateNumber($type);
                $permit->save();
                return;
            } catch (UniqueConstraintViolationException $e) {
                if ($attempt >= 5) {
                    throw $e;
                }
            }
        }
    }

    // Hanya Foreman & Supervisor yang punya departemen
    private function authorizeApplicant(Request $request): void
    {
        $user = $request->user();

        abort_unless($user->canApplyPermit(), 403, 'Permit to Work hanya bisa diajukan oleh Foreman atau Supervisor. Minta admin mengatur jabatan di akunmu.');
        abort_unless($user->department_id, 403, 'Akunmu belum punya departemen. Minta admin melengkapi data akun.');
    }

    // Hanya pengaju permit itu sendiri (aktivasi harian & close-out)
    private function authorizeApplicantOf(Request $request, WorkPermit $permit): void
    {
        abort_unless((int) $permit->applicant_id === (int) $request->user()->id, 403, 'Hanya pengaju permit yang bisa melakukan ini.');
    }

    private function authorizeOwner(Request $request, WorkPermit $permit): void
    {
        $user = $request->user();

        abort_unless(
            (int) $permit->applicant_id === (int) $user->id || $user->hasAnyRole(['superadmin', 'qhse']),
            403
        );
    }
}