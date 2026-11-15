<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\SafetyCampaign;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/**
 * Kelola Safety Campaign (menu Tasklist → Safety Campaign).
 * Tahap 2: buat, edit, terbitkan, hapus. Rekap respon menyusul di Tahap 4.
 */
class SafetyCampaignController extends Controller
{
    public function index()
    {
        $campaigns = SafetyCampaign::with(['departments', 'images'])
            ->withCount([
                'questions',
                'responses as completed_count' => fn ($q) => $q->whereNotNull('completed_at'),
            ])
            ->latest()
            ->paginate(15);

        // Untuk menghitung jumlah karyawan yang ditargetkan tiap campaign
        $totalUsers   = User::count();
        $usersPerDept = User::whereNotNull('department_id')
            ->selectRaw('department_id, COUNT(*) as total')
            ->groupBy('department_id')
            ->pluck('total', 'department_id');

        return view('safety-campaigns.manage.index', compact('campaigns', 'totalUsers', 'usersPerDept'));
    }

    public function create()
    {
        $campaign = new SafetyCampaign([
            'start_date' => today(),
            'target_all' => true,
        ]);

        return view('safety-campaigns.manage.form', $this->formData($campaign, []));
    }

    public function store(Request $request)
    {
        $data = $this->validateCampaign($request);

        $storedPaths = [];

        try {
            $campaign = DB::transaction(function () use ($request, $data, &$storedPaths) {
                $publish = $request->input('action') === 'publish';

                $campaign = SafetyCampaign::create([
                    'title'        => $data['title'],
                    'message'      => $data['message'],
                    'start_date'   => $data['start_date'],
                    'end_date'     => $data['end_date'] ?? null,
                    'target_all'   => $data['target_all'],
                    'is_published' => $publish,
                    'published_at' => $publish ? now() : null,
                    'created_by'   => $request->user()->id,
                ]);

                $campaign->departments()->sync($data['target_all'] ? [] : $data['departments']);
                $storedPaths = $this->storeImages($campaign, $request->file('images', []), 0);
                $this->saveQuestions($campaign, $data['questions']);

                return $campaign;
            });
        } catch (\Throwable $e) {
            Storage::disk('public')->delete($storedPaths);
            throw $e;
        }

        $message = $campaign->is_published
            ? "Campaign \"{$campaign->title}\" berhasil diterbitkan."
            : "Campaign \"{$campaign->title}\" disimpan sebagai draft.";

        return redirect()->route('safety-campaigns.index')->with('success', $message);
    }

    public function edit(SafetyCampaign $safetyCampaign)
    {
        $safetyCampaign->load(['departments', 'images', 'questions.options']);

        // Soal yang sudah ada, dalam format yang dipakai penyusun soal di form
        $questions = $safetyCampaign->questions->map(fn ($q) => [
            'question'    => $q->question,
            'explanation' => $q->explanation,
            'options'     => $q->options->pluck('option_text')->all(),
            'correct'     => (int) $q->options->search(fn ($o) => $o->is_correct),
        ])->all();

        return view('safety-campaigns.manage.form', $this->formData($safetyCampaign, $questions));
    }

    public function update(Request $request, SafetyCampaign $safetyCampaign)
    {
        $locked = $safetyCampaign->questionsLocked();
        $data   = $this->validateCampaign($request, $safetyCampaign, $locked);

        $storedPaths  = [];
        $removedPaths = [];

        try {
            DB::transaction(function () use ($request, $data, $safetyCampaign, $locked, &$storedPaths, &$removedPaths) {
                $publish = $request->input('action') === 'publish';

                $safetyCampaign->update([
                    'title'        => $data['title'],
                    'message'      => $data['message'],
                    'start_date'   => $data['start_date'],
                    'end_date'     => $data['end_date'] ?? null,
                    'target_all'   => $data['target_all'],
                    'is_published' => $publish || $safetyCampaign->is_published,
                    'published_at' => $safetyCampaign->published_at ?? ($publish ? now() : null),
                ]);

                $safetyCampaign->departments()->sync($data['target_all'] ? [] : $data['departments']);

                // Hapus foto yang dicentang "hapus"
                $toRemove = $safetyCampaign->images()->whereIn('id', $data['remove_images'])->get();
                $removedPaths = $toRemove->pluck('path')->all();
                $safetyCampaign->images()->whereIn('id', $toRemove->pluck('id'))->delete();

                // Tambah foto baru di urutan paling belakang
                $nextOrder   = (int) $safetyCampaign->images()->max('sort_order') + 1;
                $storedPaths = $this->storeImages($safetyCampaign, $request->file('images', []), $nextOrder);

                if (! $locked) {
                    $this->saveQuestions($safetyCampaign, $data['questions']);
                }
            });
        } catch (\Throwable $e) {
            Storage::disk('public')->delete($storedPaths);
            throw $e;
        }

        Storage::disk('public')->delete($removedPaths);

        return redirect()->route('safety-campaigns.index')
            ->with('success', "Campaign \"{$safetyCampaign->title}\" berhasil diperbarui.");
    }

    public function togglePublish(SafetyCampaign $safetyCampaign)
    {
        $publish = ! $safetyCampaign->is_published;

        $safetyCampaign->update([
            'is_published' => $publish,
            'published_at' => $safetyCampaign->published_at ?? ($publish ? now() : null),
        ]);

        return back()->with('success', $publish
            ? "Campaign \"{$safetyCampaign->title}\" diterbitkan."
            : "Campaign \"{$safetyCampaign->title}\" ditarik kembali menjadi draft.");
    }

    public function destroy(SafetyCampaign $safetyCampaign)
    {
        $safetyCampaign->delete(); // soft delete, data respon tetap tersimpan

        return redirect()->route('safety-campaigns.index')
            ->with('success', "Campaign \"{$safetyCampaign->title}\" dihapus.");
    }

    // =================================================================
    // BANTUAN
    // =================================================================

    private function formData(SafetyCampaign $campaign, array $existingQuestions): array
    {
        return [
            'campaign'         => $campaign,
            'departments'      => Department::orderBy('name')->get(),
            'selectedDepts'    => old('departments', $campaign->exists ? $campaign->departments->pluck('id')->all() : []),
            'initialQuestions' => old('questions', $existingQuestions),
            'locked'           => $campaign->exists && $campaign->questionsLocked(),
            'config'           => config('safety_campaign'),
        ];
    }

    private function validateCampaign(Request $request, ?SafetyCampaign $campaign = null, bool $locked = false): array
    {
        $cfg = config('safety_campaign');

        $rules = [
            'title'           => ['required', 'string', 'max:255'],
            'message'         => ['required', 'string', 'max:5000'],
            'start_date'      => ['required', 'date'],
            'end_date'        => ['nullable', 'date', 'after_or_equal:start_date'],
            'target_all'      => ['required', 'boolean'],
            'departments'     => ['nullable', 'required_if:target_all,0', 'array'],
            'departments.*'   => ['integer', 'exists:departments,id'],
            'images'          => ['nullable', 'array', 'max:' . $cfg['max_images']],
            'images.*'        => ['image', 'mimes:jpg,jpeg,png,webp', 'max:' . $cfg['max_image_kb']],
            'remove_images'   => ['nullable', 'array'],
            'remove_images.*' => ['integer'],
        ];

        if (! $locked) {
            $rules += [
                'questions'               => ['nullable', 'array', 'max:' . $cfg['max_questions']],
                'questions.*.question'    => ['required', 'string', 'max:1000'],
                'questions.*.explanation' => ['nullable', 'string', 'max:1000'],
                'questions.*.options'     => ['required', 'array', 'min:' . $cfg['min_options'], 'max:' . $cfg['max_options']],
                'questions.*.options.*'   => ['required', 'string', 'max:500'],
                'questions.*.correct'     => ['required', 'integer', 'min:0'],
            ];
        }

        $data = $request->validate($rules, [
            'required'                         => ':attribute wajib diisi.',
            'max.string'                       => ':attribute maksimal :max karakter.',
            'end_date.after_or_equal'          => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
            'departments.required_if'          => 'Pilih minimal satu departemen target.',
            'images.max'                       => 'Foto maksimal :max.',
            'images.*.image'                   => 'File poster harus berupa gambar.',
            'images.*.mimes'                   => 'Format poster harus JPG, PNG, atau WEBP.',
            'images.*.max'                     => 'Ukuran setiap poster maksimal ' . round($cfg['max_image_kb'] / 1024) . ' MB.',
            'images.*.uploaded'                => 'Poster gagal diupload. Kemungkinan ukurannya melebihi batas server.',
            'questions.max'                    => 'Soal maksimal :max.',
            'questions.*.question.required'    => 'Pertanyaan pada setiap soal wajib diisi.',
            'questions.*.options.min'          => 'Setiap soal minimal :min pilihan jawaban.',
            'questions.*.options.max'          => 'Setiap soal maksimal :max pilihan jawaban.',
            'questions.*.options.*.required'   => 'Pilihan jawaban tidak boleh kosong.',
            'questions.*.correct.required'     => 'Tandai jawaban yang benar pada setiap soal.',
        ], [
            'title'      => 'Judul',
            'message'    => 'Pesan campaign',
            'start_date' => 'Tanggal mulai',
            'end_date'   => 'Tanggal selesai',
        ]);

        // ---- Cek tambahan yang tidak bisa ditulis sebagai aturan biasa ----
        $errors = [];

        // Jumlah poster setelah penghapusan + tambahan harus 1..max
        $existing = $campaign
            ? $campaign->images()->whereNotIn('id', $data['remove_images'] ?? [])->count()
            : 0;
        $totalImages = $existing + count($request->file('images', []));
        if ($totalImages < 1) {
            $errors['images'] = 'Upload minimal 1 foto/poster campaign.';
        } elseif ($totalImages > $cfg['max_images']) {
            $errors['images'] = "Total foto/poster maksimal {$cfg['max_images']}.";
        }

        // Jawaban benar harus menunjuk ke pilihan yang ada
        foreach (($data['questions'] ?? []) as $i => $q) {
            if (! isset($q['options'][$q['correct']])) {
                $errors["questions.{$i}.correct"] = 'Soal nomor ' . ($i + 1) . ': tandai jawaban yang benar.';
            }
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        $data['target_all']    = (bool) $data['target_all'];
        $data['departments']   = $data['departments'] ?? [];
        $data['remove_images'] = $data['remove_images'] ?? [];
        $data['questions']     = array_values($data['questions'] ?? []);

        return $data;
    }

    /** Simpan foto, kembalikan daftar path yang tersimpan (untuk dihapus kalau gagal). */
    private function storeImages(SafetyCampaign $campaign, array $files, int $startOrder): array
    {
        $paths = [];

        foreach (array_values($files) as $i => $file) {
            $paths[] = $path = $file->store('safety-campaigns/' . $campaign->id, 'public');
            $campaign->images()->create([
                'path'          => $path,
                'original_name' => $file->getClientOriginalName(),
                'sort_order'    => $startOrder + $i,
            ]);
        }

        return $paths;
    }

    /** Ganti seluruh soal dengan isi form (hanya kalau soal belum dikunci). */
    private function saveQuestions(SafetyCampaign $campaign, array $questions): void
    {
        $campaign->questions()->delete(); // pilihan jawaban ikut terhapus (cascade)

        foreach ($questions as $qi => $q) {
            $question = $campaign->questions()->create([
                'question'    => $q['question'],
                'explanation' => $q['explanation'] ?? null,
                'sort_order'  => $qi,
            ]);

            foreach (array_values($q['options']) as $oi => $text) {
                $question->options()->create([
                    'option_text' => $text,
                    'is_correct'  => $oi === (int) $q['correct'],
                    'sort_order'  => $oi,
                ]);
            }
        }
    }
}