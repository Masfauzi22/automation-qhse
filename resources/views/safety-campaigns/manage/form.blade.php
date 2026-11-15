@php
    $isEdit   = $campaign->exists;
    $input    = 'block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500';
    $label    = 'block text-sm font-semibold text-gray-700 mb-1.5';
    $hint     = 'mt-1 text-xs text-gray-500';
    $errorCls = 'mt-1 text-xs font-medium text-red-600';
    $maxMb    = round($config['max_image_kb'] / 1024);
    $targetAll = old('target_all', $campaign->target_all ?? true) ? 1 : 0;
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('safety-campaigns.index') }}" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700" title="Kembali">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $isEdit ? 'Edit Safety Campaign' : 'Buat Safety Campaign' }}</h2>
                @if ($isEdit)
                    <p class="text-xs text-gray-500">Status: <span class="font-semibold">{{ $campaign->statusLabel() }}</span></p>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            @if ($errors->any())
                <div class="mb-6 rounded-lg border-l-4 border-red-500 bg-red-50 p-4">
                    <p class="text-sm font-semibold text-red-800">Campaign belum bisa disimpan. Periksa kembali isian berikut:</p>
                    <ul class="mt-2 list-disc list-inside text-sm text-red-700 space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ $isEdit ? route('safety-campaigns.update', $campaign) : route('safety-campaigns.store') }}"
                  method="POST" enctype="multipart/form-data" class="space-y-6"
                  x-data="{ submitting: false }" @submit="submitting = true">
                @csrf
                @if ($isEdit) @method('PUT') @endif

                <!-- 1. Isi Campaign -->
                <section class="bg-white rounded-xl border border-gray-100 shadow-sm">
                    <div class="border-b border-gray-100 px-6 py-4">
                        <h3 class="text-sm font-bold text-gray-900">1. Isi Campaign</h3>
                    </div>
                    <div class="px-6 py-5 space-y-5">
                        <div>
                            <label for="title" class="{{ $label }}">Judul <span class="text-red-500">*</span></label>
                            <input type="text" id="title" name="title" required maxlength="255"
                                   value="{{ old('title', $campaign->title) }}" placeholder="Contoh: Gunakan APD Lengkap di Area Workshop"
                                   class="{{ $input }}">
                            @error('title') <p class="{{ $errorCls }}">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="message" class="{{ $label }}">Pesan Campaign <span class="text-red-500">*</span></label>
                            <textarea id="message" name="message" rows="5" required maxlength="5000"
                                      placeholder="Jelaskan pesan keselamatan yang ingin disampaikan"
                                      class="{{ $input }}">{{ old('message', $campaign->message) }}</textarea>
                            @error('message') <p class="{{ $errorCls }}">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label for="start_date" class="{{ $label }}">Tanggal Mulai <span class="text-red-500">*</span></label>
                                <input type="date" id="start_date" name="start_date" required
                                       value="{{ old('start_date', $campaign->start_date?->toDateString()) }}" class="{{ $input }}">
                                @error('start_date') <p class="{{ $errorCls }}">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="end_date" class="{{ $label }}">Tanggal Selesai</label>
                                <input type="date" id="end_date" name="end_date"
                                       value="{{ old('end_date', $campaign->end_date?->toDateString()) }}" class="{{ $input }}">
                                <p class="{{ $hint }}">Kosongkan kalau tidak ada batas waktu.</p>
                                @error('end_date') <p class="{{ $errorCls }}">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </section>

                <!-- 2. Poster -->
                <section class="bg-white rounded-xl border border-gray-100 shadow-sm">
                    <div class="border-b border-gray-100 px-6 py-4">
                        <h3 class="text-sm font-bold text-gray-900">2. Foto / Poster</h3>
                        <p class="text-xs text-gray-500">Minimal 1, maksimal {{ $config['max_images'] }} foto. Foto pertama jadi sampul campaign.</p>
                    </div>
                    <div class="px-6 py-5 space-y-5">

                        @if ($isEdit && $campaign->images->isNotEmpty())
                            <div>
                                <p class="{{ $label }}">Foto saat ini</p>
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                    @foreach ($campaign->images as $image)
                                        <label class="group relative block cursor-pointer" x-data="{ remove: {{ in_array($image->id, old('remove_images', [])) ? 'true' : 'false' }} }">
                                            <div class="aspect-square overflow-hidden rounded-lg border-2 transition" :class="remove ? 'border-red-400 opacity-40' : 'border-gray-200'">
                                                <img src="{{ $image->url() }}" alt="{{ $image->original_name }}" class="h-full w-full object-cover">
                                            </div>
                                            <span class="mt-1.5 flex items-center gap-1.5 text-xs" :class="remove ? 'font-semibold text-red-600' : 'text-gray-500'">
                                                <input type="checkbox" name="remove_images[]" value="{{ $image->id }}" x-model="remove"
                                                       class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                                                Hapus foto ini
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div x-data="photoPicker({{ $config['max_images'] }}, {{ $config['max_image_kb'] }}, { maxSide: 2000, quality: 0.85 })">
                            <label class="{{ $label }}">{{ $isEdit ? 'Tambah foto' : 'Upload foto' }} @unless ($isEdit)<span class="text-red-500">*</span>@endunless</label>
                            <label class="relative flex cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 px-4 py-6 text-center transition hover:border-emerald-400 hover:bg-emerald-50/40">
                                <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                                <span class="mt-2 text-sm font-medium text-gray-700">Klik untuk pilih poster</span>
                                <span class="text-xs text-gray-500">JPG, PNG, WEBP, masing-masing maksimal {{ $maxMb }} MB</span>
                                <input type="file" name="images[]" multiple accept="image/jpeg,image/png,image/webp" class="sr-only" @change="pick($event)">
                            </label>
                            <p x-show="compressing" class="mt-1 text-xs font-medium text-blue-600" style="display: none;">Mengompres foto...</p>
                            <p x-show="info" x-text="info" class="mt-1 text-xs text-emerald-700" style="display: none;"></p>
                            <p x-show="error" x-text="error" class="{{ $errorCls }}" style="display: none;"></p>
                            <div x-show="previews.length" class="mt-3 grid grid-cols-2 sm:grid-cols-4 gap-3" style="display: none;">
                                <template x-for="p in previews" :key="p.url">
                                    <div class="aspect-square overflow-hidden rounded-lg border border-gray-200 bg-gray-100">
                                        <img :src="p.url" :alt="p.name" class="h-full w-full object-cover">
                                    </div>
                                </template>
                            </div>
                            @error('images') <p class="{{ $errorCls }}">{{ $message }}</p> @enderror
                            @error('images.*') <p class="{{ $errorCls }}">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </section>

                <!-- 3. Target -->
                <section class="bg-white rounded-xl border border-gray-100 shadow-sm" x-data="{ targetAll: '{{ $targetAll }}' }">
                    <div class="border-b border-gray-100 px-6 py-4">
                        <h3 class="text-sm font-bold text-gray-900">3. Target Pembaca</h3>
                    </div>
                    <div class="px-6 py-5 space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <label class="flex cursor-pointer items-start gap-3 rounded-lg border p-4 transition" :class="targetAll === '1' ? 'border-emerald-500 bg-emerald-50/50' : 'border-gray-200'">
                                <input type="radio" name="target_all" value="1" x-model="targetAll" class="mt-0.5 border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                <span>
                                    <span class="block text-sm font-semibold text-gray-800">Semua karyawan</span>
                                    <span class="block text-xs text-gray-500">Campaign umum untuk seluruh perusahaan.</span>
                                </span>
                            </label>
                            <label class="flex cursor-pointer items-start gap-3 rounded-lg border p-4 transition" :class="targetAll === '0' ? 'border-emerald-500 bg-emerald-50/50' : 'border-gray-200'">
                                <input type="radio" name="target_all" value="0" x-model="targetAll" class="mt-0.5 border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                <span>
                                    <span class="block text-sm font-semibold text-gray-800">Departemen tertentu</span>
                                    <span class="block text-xs text-gray-500">Misalnya khusus area kerja berisiko tertentu.</span>
                                </span>
                            </label>
                        </div>

                        <div x-show="targetAll === '0'" x-transition style="{{ $targetAll ? 'display: none;' : '' }}">
                            <p class="{{ $label }}">Pilih departemen</p>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                @foreach ($departments as $dept)
                                    <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50 has-[:checked]:border-emerald-400 has-[:checked]:bg-emerald-50">
                                        <input type="checkbox" name="departments[]" value="{{ $dept->id }}" @checked(in_array($dept->id, $selectedDepts))
                                               class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                        {{ $dept->name }}
                                    </label>
                                @endforeach
                            </div>
                            @error('departments') <p class="{{ $errorCls }}">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </section>

                <!-- 4. Soal -->
                <section class="bg-white rounded-xl border border-gray-100 shadow-sm">
                    <div class="border-b border-gray-100 px-6 py-4">
                        <h3 class="text-sm font-bold text-gray-900">4. Soal Pemahaman <span class="font-normal text-gray-400">(opsional)</span></h3>
                        <p class="text-xs text-gray-500">Maksimal {{ $config['max_questions'] }} soal pilihan ganda. Tanpa soal, karyawan cukup konfirmasi sudah membaca.</p>
                    </div>

                    @if ($locked)
                        {{-- Soal sudah dikunci karena ada yang mengerjakan --}}
                        <div class="px-6 py-5 space-y-4">
                            <div class="flex items-start gap-3 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                                <svg class="mt-0.5 h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                Soal tidak bisa diubah karena sudah ada karyawan yang mengerjakan. Ini supaya rekap jawaban tetap akurat.
                            </div>
                            @foreach ($campaign->questions as $qi => $q)
                                <div class="rounded-lg border border-gray-200 p-4">
                                    <p class="text-sm font-semibold text-gray-900">{{ $qi + 1 }}. {{ $q->question }}</p>
                                    <ul class="mt-2 space-y-1 text-sm">
                                        @foreach ($q->options as $opt)
                                            <li class="flex items-center gap-2 {{ $opt->is_correct ? 'font-semibold text-emerald-700' : 'text-gray-600' }}">
                                                <span class="h-2 w-2 rounded-full {{ $opt->is_correct ? 'bg-emerald-500' : 'bg-gray-300' }}"></span>
                                                {{ $opt->option_text }}
                                            </li>
                                        @endforeach
                                    </ul>
                                    @if ($q->explanation)
                                        <p class="mt-2 text-xs text-gray-500"><span class="font-semibold">Pembahasan:</span> {{ $q->explanation }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="px-6 py-5 space-y-4"
                             x-data="questionBuilder(@js($initialQuestions), {{ $config['max_questions'] }}, {{ $config['min_options'] }}, {{ $config['max_options'] }})">

                            <template x-if="questions.length === 0">
                                <div class="rounded-lg border border-dashed border-gray-300 px-4 py-6 text-center text-sm text-gray-500">
                                    Belum ada soal. Karyawan cukup klik <b>"Saya sudah membaca & memahami"</b>.
                                </div>
                            </template>

                            <template x-for="(q, qi) in questions" :key="q.uid">
                                <div class="rounded-xl border border-gray-200 bg-gray-50/50 p-4 space-y-4">
                                    <div class="flex items-center justify-between">
                                        <p class="text-sm font-bold text-gray-900">Soal <span x-text="qi + 1"></span></p>
                                        <button type="button" @click="removeQuestion(qi)" class="text-xs font-semibold text-red-600 hover:text-red-800">Hapus soal</button>
                                    </div>

                                    <div>
                                        <label class="{{ $label }}">Pertanyaan <span class="text-red-500">*</span></label>
                                        <textarea :name="`questions[${qi}][question]`" x-model="q.question" rows="2" required maxlength="1000"
                                                  placeholder="Contoh: APD apa yang wajib dipakai saat masuk area workshop?"
                                                  class="{{ $input }}"></textarea>
                                    </div>

                                    <div>
                                        <p class="{{ $label }}">Pilihan jawaban <span class="font-normal text-gray-400">(klik bulatan untuk menandai jawaban benar)</span></p>
                                        <div class="space-y-2">
                                            <template x-for="(opt, oi) in q.options" :key="opt.uid">
                                                <div class="flex items-center gap-2">
                                                    <input type="radio" :name="`questions[${qi}][correct]`" :value="oi" required
                                                           :checked="q.correct === oi" @change="q.correct = oi"
                                                           class="h-5 w-5 border-gray-300 text-emerald-600 focus:ring-emerald-500"
                                                           title="Tandai sebagai jawaban benar">
                                                    <input type="text" :name="`questions[${qi}][options][${oi}]`" x-model="opt.text" required maxlength="500"
                                                           :placeholder="`Pilihan ${String.fromCharCode(65 + oi)}`"
                                                           :class="q.correct === oi ? 'border-emerald-400 bg-emerald-50' : ''"
                                                           class="{{ $input }}">
                                                    <button type="button" x-show="q.options.length > minOptions" @click="removeOption(q, oi)"
                                                            class="shrink-0 rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-red-600" title="Hapus pilihan">
                                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                                    </button>
                                                </div>
                                            </template>
                                        </div>
                                        <button type="button" x-show="q.options.length < maxOptions" @click="addOption(q)"
                                                class="mt-2 text-xs font-semibold text-emerald-700 hover:text-emerald-900">+ Tambah pilihan</button>
                                    </div>

                                    <div>
                                        <label class="{{ $label }}">Pembahasan <span class="font-normal text-gray-400">(opsional, tampil setelah karyawan menjawab)</span></label>
                                        <textarea :name="`questions[${qi}][explanation]`" x-model="q.explanation" rows="2" maxlength="1000"
                                                  placeholder="Jelaskan kenapa jawaban itu benar"
                                                  class="{{ $input }}"></textarea>
                                    </div>
                                </div>
                            </template>

                            <button type="button" x-show="questions.length < maxQuestions" @click="addQuestion()"
                                    class="inline-flex items-center gap-2 rounded-lg border border-emerald-300 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-100">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6"/></svg>
                                Tambah Soal
                            </button>
                        </div>
                    @endif
                </section>

                <!-- Tombol -->
                <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-3">
                    <a href="{{ route('safety-campaigns.index') }}" class="text-center text-sm font-medium text-gray-500 hover:text-gray-700 px-4 py-2.5">Batal</a>

                    @if ($isEdit && $campaign->is_published)
                        <button type="submit" name="action" value="save" :disabled="submitting"
                                class="rounded-lg bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 disabled:opacity-60">
                            Simpan Perubahan
                        </button>
                    @else
                        <button type="submit" name="action" value="draft" :disabled="submitting"
                                class="rounded-lg border border-gray-300 bg-white px-6 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 disabled:opacity-60">
                            Simpan Draft
                        </button>
                        <button type="submit" name="action" value="publish" :disabled="submitting"
                                class="rounded-lg bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 disabled:opacity-60">
                            Simpan & Terbitkan
                        </button>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <script>

        // Penyusun soal pilihan ganda
        function questionBuilder(initial, maxQuestions, minOptions, maxOptions) {
            let uid = 0;
            const makeOption = (text = '') => ({ uid: ++uid, text });
            const makeQuestion = (q = {}) => ({
                uid: ++uid,
                question: q.question ?? '',
                explanation: q.explanation ?? '',
                correct: (q.correct === undefined || q.correct === null || q.correct === '') ? null : Number(q.correct),
                options: (q.options && q.options.length ? Object.values(q.options) : Array(minOptions).fill('')).map(t => makeOption(t ?? '')),
            });

            return {
                maxQuestions, minOptions, maxOptions,
                questions: (Array.isArray(initial) ? initial : Object.values(initial || {})).map(makeQuestion),

                addQuestion() {
                    if (this.questions.length < this.maxQuestions) this.questions.push(makeQuestion());
                },
                removeQuestion(index) {
                    if (confirm('Hapus soal ini?')) this.questions.splice(index, 1);
                },
                addOption(q) {
                    if (q.options.length < this.maxOptions) q.options.push(makeOption());
                },
                removeOption(q, index) {
                    if (q.options.length <= this.minOptions) return;
                    q.options.splice(index, 1);
                    // Geser tanda jawaban benar supaya tetap menunjuk pilihan yang sama
                    if (q.correct === index) q.correct = null;
                    else if (q.correct !== null && q.correct > index) q.correct--;
                },
            };
        }
    </script>
    <x-photo-picker-script />
</x-app-layout>