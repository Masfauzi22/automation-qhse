<?php

namespace App\Http\Controllers;

use App\Models\SafetyCampaign;
use App\Models\SafetyCampaignAttempt;
use App\Models\SafetyCampaignResponse;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Safety Campaign untuk karyawan (menu Personal Record → Safety Campaign).
 * Lihat campaign, jawab soal / konfirmasi baca, dan beri komentar.
 */
class MySafetyCampaignController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $tab  = in_array($request->query('tab'), ['pending', 'done', 'all'], true) ? $request->query('tab') : 'pending';

        $completedByMe = fn ($r) => $r->where('user_id', $user->id)->whereNotNull('completed_at');

        // Campaign yang sudah terbit, sudah mulai, dan ditujukan ke user ini
        $base = SafetyCampaign::query()
            ->where('is_published', true)
            ->whereDate('start_date', '<=', today())
            ->visibleTo($user);

        $campaigns = (clone $base)
            ->with(['images', 'responses' => fn ($r) => $r->where('user_id', $user->id)])
            ->withCount('questions')
            ->when($tab === 'pending', fn ($q) => $q->active()->whereDoesntHave('responses', $completedByMe))
            ->when($tab === 'done', fn ($q) => $q->whereHas('responses', $completedByMe))
            ->latest('start_date')
            ->paginate(12)
            ->withQueryString();

        $counts = [
            'pending' => SafetyCampaign::pendingFor($user)->count(),
            'done'    => (clone $base)->whereHas('responses', $completedByMe)->count(),
            'all'     => (clone $base)->count(),
        ];

        return view('safety-campaigns.my.index', compact('campaigns', 'counts', 'tab'));
    }

    public function show(Request $request, SafetyCampaign $safetyCampaign)
    {
        $user    = $request->user();
        $preview = $this->ensureVisible($safetyCampaign, $user);

        $safetyCampaign->load(['images', 'questions.options']);

        // Catat kapan pertama kali dibuka (tidak untuk mode pratinjau HSE)
        $response = $preview ? null : $this->responseFor($safetyCampaign, $user);

        // Urutan pilihan jawaban diacak per orang, tapi tetap sama setiap kali dibuka ulang
        foreach ($safetyCampaign->questions as $question) {
            $question->setRelation(
                'options',
                $question->options->sortBy(fn ($o) => crc32($user->id . '-' . $o->id))->values()
            );
        }

        // Hasil percobaan yang baru saja dikirim (dari redirect setelah submit)
        $lastAttempt = null;
        if ($response && ($attemptId = session('quiz_attempt_id'))) {
            $lastAttempt = SafetyCampaignAttempt::with('answers')
                ->where('safety_campaign_response_id', $response->id)
                ->find($attemptId);
        }

        // Jawaban terakhir yang lulus, untuk ditampilkan sebagai ulasan setelah selesai
        $passedAttempt = $response?->isCompleted() && $safetyCampaign->hasQuiz()
            ? $response->attempts()->with('answers')->where('passed', true)->latest('id')->first()
            : null;

        return view('safety-campaigns.my.show', [
            'campaign'      => $safetyCampaign,
            'response'      => $response,
            'preview'       => $preview,
            'lastAttempt'   => $lastAttempt,
            'passedAttempt' => $passedAttempt,
            'maxComment'    => config('safety_campaign.max_comment_length', 1000),
        ]);
    }

    /** Konfirmasi "sudah membaca & memahami" (campaign tanpa soal). */
    public function acknowledge(Request $request, SafetyCampaign $safetyCampaign)
    {
        $user = $request->user();
        $this->ensureCanRespond($safetyCampaign, $user);
        abort_if($safetyCampaign->hasQuiz(), 422, 'Campaign ini harus diselesaikan dengan menjawab soal.');

        $response = $this->responseFor($safetyCampaign, $user);
        if (! $response->completed_at) {
            $response->update(['completed_at' => now()]);
        }

        return redirect()->route('my-safety-campaign.show', $safetyCampaign)
            ->with('success', 'Terima kasih, kamu sudah menyelesaikan campaign ini.');
    }

    /** Kirim jawaban soal. */
    public function submitQuiz(Request $request, SafetyCampaign $safetyCampaign)
    {
        $user = $request->user();
        $this->ensureCanRespond($safetyCampaign, $user);

        $questions = $safetyCampaign->questions()->with('options')->get();
        abort_if($questions->isEmpty(), 422, 'Campaign ini tidak punya soal.');

        // Setiap soal wajib dijawab dengan pilihan miliknya sendiri
        $rules = [];
        foreach ($questions as $question) {
            $rules["answers.{$question->id}"] = ['required', Rule::in($question->options->pluck('id')->all())];
        }
        $validated = $request->validate($rules, [
            'answers.*.required' => 'Semua soal wajib dijawab.',
            'answers.*.in'       => 'Jawaban tidak valid. Muat ulang halaman lalu coba lagi.',
        ]);

        $response = $this->responseFor($safetyCampaign, $user);

        $attempt = DB::transaction(function () use ($response, $questions, $validated) {
            // Kunci baris respon supaya klik ganda tidak membuat percobaan dobel
            $response = SafetyCampaignResponse::whereKey($response->id)->lockForUpdate()->first();

            if ($response->completed_at) {
                return null; // sudah lulus sebelumnya
            }

            $score   = 0;
            $answers = [];
            foreach ($questions as $question) {
                $optionId  = (int) $validated['answers'][$question->id];
                $isCorrect = (bool) $question->options->firstWhere('id', $optionId)?->is_correct;
                $score    += $isCorrect ? 1 : 0;
                $answers[] = [
                    'safety_campaign_question_id' => $question->id,
                    'safety_campaign_option_id'   => $optionId,
                    'is_correct'                  => $isCorrect,
                ];
            }

            $total     = $questions->count();
            $passed    = $score === $total;
            $attemptNo = $response->attempts_count + 1;

            $attempt = $response->attempts()->create([
                'attempt_no' => $attemptNo,
                'score'      => $score,
                'total'      => $total,
                'passed'     => $passed,
            ]);
            $attempt->answers()->createMany($answers);

            $response->update(array_filter([
                'attempts_count'      => $attemptNo,
                'first_attempt_score' => $attemptNo === 1 ? $score : null,
                'first_attempt_total' => $attemptNo === 1 ? $total : null,
                'completed_at'        => $passed ? now() : null,
            ], fn ($v) => $v !== null));

            return $attempt;
        });

        if (! $attempt) {
            return redirect()->route('my-safety-campaign.show', $safetyCampaign)
                ->with('success', 'Kamu sudah menyelesaikan campaign ini sebelumnya.');
        }

        $message = $attempt->passed
            ? "Semua jawaban benar ({$attempt->score}/{$attempt->total}). Campaign selesai, terima kasih!"
            : "Nilaimu {$attempt->score}/{$attempt->total}. Baca pembahasannya, lalu coba lagi.";

        return redirect()->route('my-safety-campaign.show', $safetyCampaign)
            ->with($attempt->passed ? 'success' : 'warning', $message)
            ->with('quiz_attempt_id', $attempt->id);
    }

    /** Simpan / ubah komentar. */
    public function comment(Request $request, SafetyCampaign $safetyCampaign)
    {
        $user = $request->user();
        abort_if($this->ensureVisible($safetyCampaign, $user), 403, 'Mode pratinjau tidak bisa memberi komentar.');

        $validated = $request->validate([
            'comment' => ['nullable', 'string', 'max:' . config('safety_campaign.max_comment_length', 1000)],
        ], [
            'comment.max' => 'Komentar maksimal :max karakter.',
        ]);

        $comment = trim((string) ($validated['comment'] ?? '')) ?: null;

        $this->responseFor($safetyCampaign, $user)->update([
            'comment'      => $comment,
            'commented_at' => $comment ? now() : null,
        ]);

        return redirect()->to(route('my-safety-campaign.show', $safetyCampaign) . '#komentar')
            ->with('success', $comment ? 'Komentar tersimpan. Terima kasih atas masukannya!' : 'Komentar dihapus.');
    }

    // =================================================================
    // BANTUAN
    // =================================================================

    /**
     * Pastikan campaign boleh dibuka user ini.
     * Kembalikan true kalau ini mode pratinjau (HSE/superadmin yang bukan target).
     */
    private function ensureVisible(SafetyCampaign $campaign, User $user): bool
    {
        $isHse = $user->hasAnyRole(['superadmin', 'qhse']);

        if ($campaign->is_published && $campaign->start_date->lte(today()) && $campaign->isTargetedTo($user)) {
            return false;
        }

        // HSE boleh melihat campaign apa pun sebagai pratinjau, tanpa tercatat sebagai pembaca
        abort_unless($isHse, 404);

        return true;
    }

    /** Pastikan user boleh mengirim jawaban / konfirmasi. */
    private function ensureCanRespond(SafetyCampaign $campaign, User $user): void
    {
        abort_if($this->ensureVisible($campaign, $user), 403, 'Mode pratinjau tidak bisa mengerjakan campaign.');
        abort_if($campaign->isEnded(), 403, 'Campaign ini sudah berakhir.');
    }

    /** Ambil (atau buat) baris respon user untuk campaign ini. */
    private function responseFor(SafetyCampaign $campaign, User $user): SafetyCampaignResponse
    {
        $keys = ['safety_campaign_id' => $campaign->id, 'user_id' => $user->id];

        try {
            return SafetyCampaignResponse::firstOrCreate($keys, [
                'department_id'   => $user->department_id,
                'first_viewed_at' => now(),
            ]);
        } catch (UniqueConstraintViolationException) {
            // Dua tab dibuka bersamaan: baris sudah dibuat oleh request lain
            return SafetyCampaignResponse::where($keys)->firstOrFail();
        }
    }
}