@php
    $hasQuiz   = $campaign->questions->isNotEmpty();
    $completed = $response?->isCompleted();
    $ended     = $campaign->isEnded();
    $failed    = $lastAttempt && ! $lastAttempt->passed;

    // Jawaban yang ditampilkan di ulasan: hasil percobaan terakhir (gagal) atau percobaan yang lulus
    $reviewAttempt = $failed ? $lastAttempt : $passedAttempt;
    $answersByQ    = $reviewAttempt ? $reviewAttempt->answers->keyBy('safety_campaign_question_id') : collect();

    // Tampilkan ulasan soal: setelah gagal, setelah selesai, atau di mode pratinjau
    $showReview = $hasQuiz && ($failed || $completed || $preview);

    $images = $campaign->images->map(fn ($i) => ['url' => $i->url(), 'name' => $i->original_name])->values();
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('my-safety-campaign.index') }}" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700" title="Kembali">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Safety Campaign</h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Notifikasi --}}
            @if (session('success'))
                <div class="flex items-center gap-3 rounded-r-lg border-l-4 border-emerald-500 bg-emerald-50 p-4">
                    <svg class="h-5 w-5 shrink-0 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    <p class="text-sm font-medium text-emerald-800">{{ session('success') }}</p>
                </div>
            @endif
            @if (session('warning'))
                <div class="flex items-center gap-3 rounded-r-lg border-l-4 border-amber-500 bg-amber-50 p-4">
                    <svg class="h-5 w-5 shrink-0 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3h.008M10.29 3.86L1.82 18a1.5 1.5 0 001.29 2.25h17.78a1.5 1.5 0 001.29-2.25L13.71 3.86a1.5 1.5 0 00-2.42 0z"/></svg>
                    <p class="text-sm font-medium text-amber-800">{{ session('warning') }}</p>
                </div>
            @endif
            @if ($errors->any())
                <div class="rounded-r-lg border-l-4 border-red-500 bg-red-50 p-4">
                    <ul class="list-disc list-inside text-sm text-red-700 space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($preview)
                <div class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800">
                    <b>Mode pratinjau.</b> Kamu melihat campaign ini sebagai HSE. Kunjunganmu tidak tercatat sebagai pembaca.
                </div>
            @endif

            {{-- ================= Poster ================= --}}
            @if ($images->isNotEmpty())
                <section class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm" x-data="{ active: 0, images: @js($images) }">
                    <a :href="images[active].url" target="_blank" class="block bg-gray-900">
                        <img :src="images[active].url" :alt="images[active].name" src="{{ $images[0]['url'] }}"
                             class="mx-auto max-h-[75vh] w-full object-contain">
                    </a>
                    @if ($images->count() > 1)
                        <div class="flex gap-2 overflow-x-auto p-3">
                            <template x-for="(img, i) in images" :key="i">
                                <button type="button" @click="active = i"
                                        :class="active === i ? 'ring-2 ring-emerald-500' : 'opacity-60 hover:opacity-100'"
                                        class="h-16 w-16 shrink-0 overflow-hidden rounded-lg bg-gray-100 transition">
                                    <img :src="img.url" alt="" class="h-full w-full object-cover">
                                </button>
                            </template>
                        </div>
                    @endif
                </section>
            @endif

            {{-- ================= Isi ================= --}}
            <section class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                <div class="flex flex-wrap items-center gap-2 text-xs">
                    <span class="rounded-full bg-emerald-50 px-2.5 py-1 font-semibold text-emerald-700">Safety Campaign</span>
                    <span class="text-gray-500">
                        {{ $campaign->start_date->translatedFormat('d M Y') }}
                        @if ($campaign->end_date) – {{ $campaign->end_date->translatedFormat('d M Y') }} @endif
                    </span>
                </div>
                <h1 class="mt-3 text-2xl font-bold text-gray-900">{{ $campaign->title }}</h1>
                <div class="mt-4 text-[15px] leading-relaxed text-gray-700 whitespace-pre-line">{{ $campaign->message }}</div>
            </section>

            {{-- ================= Status / Tindakan ================= --}}
            @if ($completed)
                <section class="rounded-2xl border border-emerald-200 bg-emerald-50 p-6">
                    <div class="flex items-start gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-600 text-white">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        </span>
                        <div>
                            <p class="font-bold text-emerald-900">Campaign selesai</p>
                            <p class="text-sm text-emerald-800">
                                Kamu menyelesaikannya pada {{ $response->completed_at->translatedFormat('d F Y, H:i') }}.
                                @if ($hasQuiz && $response->first_attempt_total)
                                    Nilai percobaan pertama: <b>{{ $response->first_attempt_score }}/{{ $response->first_attempt_total }}</b>
                                    @if ($response->attempts_count > 1) (lulus di percobaan ke-{{ $response->attempts_count }}) @endif.
                                @endif
                            </p>
                        </div>
                    </div>
                </section>
            @elseif ($ended && ! $preview)
                <section class="rounded-2xl border border-gray-200 bg-gray-50 p-6 text-sm text-gray-600">
                    Campaign ini sudah berakhir pada {{ $campaign->end_date->translatedFormat('d F Y') }} dan tidak bisa dikerjakan lagi.
                </section>
            @elseif (! $preview && ! $hasQuiz)
                {{-- Tanpa soal: konfirmasi baca --}}
                <section class="rounded-2xl border-2 border-emerald-200 bg-white p-6 shadow-sm">
                    <p class="text-sm text-gray-600">Sudah membaca dan memahami pesan di atas? Konfirmasi di bawah ini.</p>
                    <form method="POST" action="{{ route('my-safety-campaign.acknowledge', $campaign) }}" class="mt-4" x-data="{ busy: false }" @submit="busy = true">
                        @csrf
                        <button type="submit" :disabled="busy"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-6 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-700 disabled:opacity-60 sm:w-auto">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            Saya sudah membaca & memahami
                        </button>
                    </form>
                </section>
            @endif

            {{-- ================= Ulasan soal (setelah menjawab / selesai / pratinjau) ================= --}}
            @if ($showReview)
                <section class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                    <h3 class="font-bold text-gray-900">
                        @if ($failed) Hasil jawabanmu: {{ $lastAttempt->score }}/{{ $lastAttempt->total }}
                        @elseif ($preview) Soal pemahaman (pratinjau)
                        @else Ulasan jawaban
                        @endif
                    </h3>
                    @if ($failed)
                        <p class="text-sm text-gray-500">Baca pembahasan pada soal yang salah, lalu coba lagi. Semua jawaban harus benar untuk menyelesaikan campaign.</p>
                    @endif

                    <ol class="mt-5 space-y-5">
                        @foreach ($campaign->questions as $qi => $question)
                            @php
                                $answer    = $answersByQ->get($question->id);
                                $isCorrect = $answer?->is_correct;
                            @endphp
                            <li class="rounded-xl border p-4 {{ $answer ? ($isCorrect ? 'border-emerald-200 bg-emerald-50/40' : 'border-red-200 bg-red-50/40') : 'border-gray-200' }}">
                                <div class="flex items-start justify-between gap-3">
                                    <p class="font-semibold text-gray-900">{{ $qi + 1 }}. {{ $question->question }}</p>
                                    @if ($answer)
                                        <span class="shrink-0 rounded-full px-2 py-0.5 text-xs font-bold {{ $isCorrect ? 'bg-emerald-600 text-white' : 'bg-red-600 text-white' }}">
                                            {{ $isCorrect ? 'Benar' : 'Salah' }}
                                        </span>
                                    @endif
                                </div>
                                <ul class="mt-3 space-y-1.5 text-sm">
                                    @foreach ($question->options as $oi => $option)
                                        @php $chosen = $answer && (int) $answer->safety_campaign_option_id === (int) $option->id; @endphp
                                        <li class="flex items-center gap-2 rounded-lg px-3 py-2
                                                   {{ $option->is_correct ? 'bg-emerald-100 font-semibold text-emerald-900' : ($chosen ? 'bg-red-100 text-red-800 line-through' : 'text-gray-600') }}">
                                            <span class="w-5 font-bold">{{ chr(65 + $oi) }}.</span>
                                            <span class="flex-1">{{ $option->option_text }}</span>
                                            @if ($chosen) <span class="text-xs font-semibold no-underline">jawabanmu</span> @endif
                                        </li>
                                    @endforeach
                                </ul>
                                @if ($question->explanation)
                                    <p class="mt-3 rounded-lg bg-white px-3 py-2 text-sm text-gray-700 ring-1 ring-gray-100">
                                        <span class="font-semibold">Pembahasan:</span> {{ $question->explanation }}
                                    </p>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                </section>
            @endif

            {{-- ================= Form soal ================= --}}
            @if ($hasQuiz && ! $completed && ! $ended && ! $preview)
                <section class="rounded-2xl border-2 border-emerald-200 bg-white p-6 shadow-sm" x-data="{ show: {{ $failed ? 'false' : 'true' }}, busy: false }">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <h3 class="font-bold text-gray-900">{{ $failed ? 'Coba lagi' : 'Cek pemahamanmu' }}</h3>
                            <p class="text-sm text-gray-500">{{ $campaign->questions->count() }} soal. Jawab semuanya dengan benar untuk menyelesaikan campaign.</p>
                        </div>
                        <button type="button" x-show="! show" @click="show = true"
                                class="shrink-0 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Kerjakan ulang</button>
                    </div>

                    <form x-show="show" method="POST" action="{{ route('my-safety-campaign.quiz', $campaign) }}" @submit="busy = true" class="mt-5 space-y-6"
                          @if ($failed) style="display: none;" @endif>
                        @csrf
                        @foreach ($campaign->questions as $qi => $question)
                            <fieldset>
                                <legend class="font-semibold text-gray-900">{{ $qi + 1 }}. {{ $question->question }}</legend>
                                <div class="mt-3 space-y-2">
                                    @foreach ($question->options as $oi => $option)
                                        <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-gray-200 px-4 py-3 text-sm transition hover:border-emerald-300 hover:bg-emerald-50/40 has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50">
                                            <input type="radio" name="answers[{{ $question->id }}]" value="{{ $option->id }}" required
                                                   @checked((string) old("answers.{$question->id}") === (string) $option->id)
                                                   class="h-4 w-4 border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                            <span class="w-5 font-bold text-gray-500">{{ chr(65 + $oi) }}.</span>
                                            <span class="text-gray-800">{{ $option->option_text }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </fieldset>
                        @endforeach

                        <button type="submit" :disabled="busy"
                                class="inline-flex w-full items-center justify-center rounded-xl bg-emerald-600 px-6 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-700 disabled:opacity-60 sm:w-auto">
                            <span x-text="busy ? 'Memeriksa...' : 'Kirim Jawaban'">Kirim Jawaban</span>
                        </button>
                    </form>
                </section>
            @endif

            {{-- ================= Komentar ================= --}}
            @unless ($preview)
                <section id="komentar" class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
                    <h3 class="font-bold text-gray-900">Tanggapan / pertanyaan untuk tim QHSE</h3>
                    <p class="text-sm text-gray-500">Opsional. Misalnya pertanyaan, saran, atau kondisi di lapangan yang terkait campaign ini.</p>

                    <form method="POST" action="{{ route('my-safety-campaign.comment', $campaign) }}" class="mt-4 space-y-3">
                        @csrf
                        <textarea name="comment" rows="3" maxlength="{{ $maxComment }}"
                                  placeholder="Tulis tanggapanmu di sini..."
                                  class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">{{ old('comment', $response?->comment) }}</textarea>
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-xs text-gray-400">
                                @if ($response?->commented_at) Terakhir disimpan {{ $response->commented_at->diffForHumans() }} @endif
                            </span>
                            <button type="submit" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                                Simpan Tanggapan
                            </button>
                        </div>
                    </form>
                </section>
            @endunless

        </div>
    </div>
</x-app-layout>