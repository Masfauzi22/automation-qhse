@php
    $isEdit   = $permit->exists;
    $isReturned = $permit->status === \App\Models\WorkPermit::STATUS_RETURNED;
    $submitLabel = ! $isEdit ? 'Ajukan Permit' : ($isReturned ? 'Ajukan Ulang' : 'Simpan Perubahan');
    $checkerOld  = (string) old('checker_id', $permit->checker_id);
    $input    = 'block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500';
    $label    = 'block text-sm font-semibold text-gray-700 mb-1.5';
    $hint     = 'mt-1 text-xs text-gray-500';
    $errorCls = 'mt-1 text-xs font-medium text-red-600';
    $maxMb    = round($maxPhotoKb / 1024);

    $workTypes  = old('work_types', $permit->work_types ?? []);
    $answers    = old('checks', $checks->map(fn ($c) => $c->answer)->all());
    $notes      = old('check_notes', $checks->map(fn ($c) => $c->note)->all());
    $gasOld     = old('gas', $gasTest
        ? collect(array_keys($gasLimits))->mapWithKeys(fn ($g) => [$g => $gasTest->$g])->all() + [
            'tested_by_name' => $gasTest->tested_by_name, 'tested_at' => $gasTest->tested_at?->format('Y-m-d\TH:i'),
        ]
        : []);
    $fmtTime = fn ($t) => $t ? substr((string) $t, 0, 5) : '';

    $color      = $config['icon_color'] ?? '#dc2626';
    $texts      = $config['texts'] ?? [];
    $extraMax   = (int) ($config['extra_notes'] ?? 0);
    $extraOld   = old('extra_notes', $permit->extraNotes());
    $linkedId   = old('linked_permit_id', $permit->details['linked_permit_id'] ?? null);
    $icons = [
        'flame'  => ['M15.362 5.214A8.252 8.252 0 0112 21 8.25 8.25 0 016.038 7.048 8.287 8.287 0 009 9.6a8.983 8.983 0 013.361-6.867 8.21 8.21 0 003 2.48z', 'M12 18a3.75 3.75 0 00.495-7.467 5.99 5.99 0 00-1.925 3.546 5.974 5.974 0 01-2.133-1A3.75 3.75 0 0012 18z'],
        'height' => ['M4.5 12.75l7.5-7.5 7.5 7.5m-15 6l7.5-7.5 7.5 7.5'],
        'dig'    => ['M19.5 5.25l-7.5 7.5-7.5-7.5m15 6l-7.5 7.5-7.5-7.5'],
        'box'    => ['M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9'],
    ];
    $iconPaths = $icons[$config['icon'] ?? 'flame'] ?? $icons['flame'];
    $hasWorkTypes = ! empty($config['work_types']);
    $singleDay    = (int) $config['max_days'] === 1;
    $maxHours     = (int) ($config['max_hours'] ?? 0);
    $docFiles     = $isEdit ? $permit->files->where('kind', 'check')->groupBy('item_key') : collect();
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <span class="flex h-9 w-9 items-center justify-center rounded-lg" style="background-color: {{ $color }}14; color: {{ $color }}">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    @foreach ($iconPaths as $d)<path stroke-linecap="round" stroke-linejoin="round" d="{{ $d }}"/>@endforeach
                </svg>
            </span>
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $isEdit ? ($isReturned ? 'Revisi Permit ' : 'Edit Permit ') . $permit->permit_number : 'Ajukan Permit ' . $config['label'] }}</h2>
                <p class="text-xs text-gray-500">{{ $config['title'] }} · {{ $config['form_number'] }} Rev. {{ $config['form_rev'] }}</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            @if ($isEdit && $isReturned && $permit->last_note)
                <div class="mb-6 rounded-lg border-l-4 border-orange-500 bg-orange-50 p-4">
                    <p class="text-sm font-semibold text-orange-800">Catatan dari pemeriksa:</p>
                    <p class="mt-1 text-sm text-orange-800 whitespace-pre-line">{{ $permit->last_note }}</p>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 rounded-lg border-l-4 border-red-500 bg-red-50 p-4">
                    <p class="text-sm font-semibold text-red-800">Permit belum bisa diajukan. Periksa kembali isian berikut:</p>
                    <ul class="mt-2 list-disc list-inside text-sm text-red-700 space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Perhatian (dari form kertas) --}}
            <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-900">
                <p class="font-bold">PERHATIAN</p>
                <p class="mt-1">{{ $texts['perhatian'] ?? '' }}</p>
                <p class="mt-2 text-xs">Permit berlaku <b>{{ $singleDay ? '1 hari' . ($maxHours ? ", maksimal {$maxHours} jam" : '') : "maksimal {$config['max_days']} hari" }}</b> dan baru boleh dipakai bekerja setelah diperiksa atasanmu dan di-approve QHSE.</p>
            </div>

            <form method="POST" enctype="multipart/form-data"
                  action="{{ $isEdit ? route('my-permit.update', $permit) : route('my-permit.store') }}"
                  x-data="{
                      submitting: false,
                      workTypes: @js(array_values($workTypes)),
                  }"
                  @submit="submitting = true"
                  class="space-y-6">
                @csrf
                @if ($isEdit) @method('PUT') @endif
                <input type="hidden" name="type" value="{{ $permit->type }}">

                {{-- ============ 1. PELAKSANAAN PEKERJAAN ============ --}}
                <section class="bg-white rounded-xl border border-gray-100 shadow-sm">
                    <div class="border-b border-gray-100 px-6 py-4">
                        <h3 class="text-sm font-bold text-gray-900">1. Pelaksanaan Pekerjaan</h3>
                        <p class="text-xs text-gray-500">Diajukan oleh <b>{{ $user->name }}</b> · {{ $user->positionLabel() }} {{ $user->department?->name }}{{ $user->company ? ' · ' . $user->company->name : '' }}</p>
                        <p class="mt-1 text-xs text-emerald-700">Setelah diajukan, permit diperiksa oleh atasanmu ({{ $user->position === 'foreman' ? 'Supervisor atau Section Head' : 'Section Head' }} {{ $user->department?->name }}), lalu di-approve QHSE.</p>
                    </div>
                    <div class="px-6 py-5 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5">
                        <div class="sm:col-span-2">
                            <label class="{{ $label }}">Nama Pekerjaan <span class="text-red-500">*</span></label>
                            <input type="text" name="project_name" required maxlength="255" class="{{ $input }}"
                                   value="{{ old('project_name', $permit->project_name) }}" placeholder="Contoh: Perbaikan handrail walkway jetty">
                        </div>

                        <div>
                            <label class="{{ $label }}">Departemen Pemilik Area <span class="text-red-500">*</span></label>
                            <select name="owner_department_id" required class="{{ $input }}">
                                <option value="">-- Pilih departemen --</option>
                                @foreach ($departments as $dept)
                                    <option value="{{ $dept->id }}" @selected(old('owner_department_id', $permit->owner_department_id) == $dept->id)>{{ $dept->name }}</option>
                                @endforeach
                            </select>
                            <p class="{{ $hint }}">Departemen yang memiliki area tempat pekerjaan dilakukan (sebagai informasi).</p>
                        </div>

                        <div>
                            <label class="{{ $label }}">Lokasi Kerja <span class="text-red-500">*</span></label>
                            <input type="text" name="location" required maxlength="255" class="{{ $input }}"
                                   value="{{ old('location', $permit->location) }}" placeholder="Contoh: Jetty ship loader, walkway sisi kanan">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="{{ $label }}">Titik Lokasi di Peta <span class="font-normal text-gray-400">(sangat disarankan)</span></label>
                            <x-hazard-report.map editable :color="$color" speed="1.2s"
                                :lat="old('latitude', $permit->latitude)" :lng="old('longitude', $permit->longitude)" />
                        </div>

                        {{-- Pemeriksa dipilih dari data user: atasan di departemen pengaju --}}
                        <div>
                            <label for="checker_id" class="{{ $label }}">Diperiksa oleh <span class="text-red-500">*</span></label>
                            @if ($checkers->isEmpty())
                                <p class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-800">
                                    Belum ada akun Supervisor / Section Head di departemen {{ $user->department?->name }}. Minta admin mengatur jabatan di Data User.
                                </p>
                            @else
                                <select id="checker_id" name="checker_id" required class="{{ $input }}">
                                    <option value="">-- Pilih atasan --</option>
                                    @foreach ($checkers->groupBy(fn ($u) => $u->positionLabel()) as $positionLabel => $group)
                                        <optgroup label="{{ $positionLabel }}">
                                            @foreach ($group as $c)
                                                <option value="{{ $c->id }}" @selected($checkerOld === (string) $c->id)>{{ $c->name }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            @endif
                            @error('checker_id') <p class="{{ $errorCls }}">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="{{ $label }}">Jumlah Pekerja <span class="text-red-500">*</span></label>
                            <input type="number" name="worker_count" required min="1" max="500" class="{{ $input }}"
                                   value="{{ old('worker_count', $permit->worker_count) }}" placeholder="Orang">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="{{ $label }}">Alat yang Digunakan <span class="text-red-500">*</span></label>
                            <textarea name="tools" rows="2" required maxlength="1000" class="{{ $input }}"
                                      placeholder="{{ $texts['tools'] ?? '' }}">{{ old('tools', $permit->tools) }}</textarea>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="{{ $label }}">{{ $watcher['label'] }} <span class="text-red-500">*</span></label>
                            <input type="text" name="fire_watch_name" required maxlength="150" class="{{ $input }}"
                                   value="{{ old('fire_watch_name', $permit->fire_watch_name) }}" placeholder="{{ $watcher['placeholder'] }}">
                        </div>
                    </div>
                </section>

                {{-- ============ 2. JENIS PEKERJAAN & WAKTU ============ --}}
                <section class="bg-white rounded-xl border border-gray-100 shadow-sm">
                    <div class="border-b border-gray-100 px-6 py-4">
                        <h3 class="text-sm font-bold text-gray-900">2. {{ $hasWorkTypes ? 'Jenis Pekerjaan & ' : '' }}Waktu Pemberlakuan</h3>
                    </div>
                    <div class="px-6 py-5 space-y-5">
                        @if ($hasWorkTypes)
                        <div>
                            <p class="{{ $label }}">Jenis Pekerjaan Yang Akan Dilakukan <span class="text-red-500">*</span></p>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                @foreach ($config['work_types'] as $key => $text)
                                    <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm has-[:checked]:border-red-300 has-[:checked]:bg-red-50">
                                        <input type="checkbox" name="work_types[]" value="{{ $key }}" x-model="workTypes"
                                               class="rounded border-gray-300 text-red-600 focus:ring-red-500"> {{ $text }}
                                    </label>
                                @endforeach
                            </div>
                            <div class="mt-2" x-show="workTypes.includes('lain')" style="{{ in_array('lain', $workTypes, true) ? '' : 'display: none;' }}">
                                <input type="text" name="work_type_other" maxlength="150" class="{{ $input }}"
                                       value="{{ old('work_type_other', $permit->work_type_other) }}" placeholder="Sebutkan jenis pekerjaan lainnya">
                            </div>
                        </div>
                        @endif

                        <div class="grid grid-cols-2 {{ $singleDay ? 'sm:grid-cols-3' : 'sm:grid-cols-4' }} gap-4">
                            <div class="{{ $singleDay ? 'col-span-2 sm:col-span-1' : '' }}">
                                <label class="{{ $label }}">{{ $singleDay ? 'Hari / Tanggal' : 'Tanggal Mulai' }} <span class="text-red-500">*</span></label>
                                <input type="date" name="start_date" required class="{{ $input }}" min="{{ today()->toDateString() }}"
                                       value="{{ old('start_date', $permit->start_date?->toDateString()) }}">
                            </div>
                            @unless ($singleDay)
                                <div>
                                    <label class="{{ $label }}">s/d Tanggal <span class="text-red-500">*</span></label>
                                    <input type="date" name="end_date" required class="{{ $input }}" min="{{ today()->toDateString() }}"
                                           value="{{ old('end_date', $permit->end_date?->toDateString()) }}">
                                </div>
                            @endunless
                            <div>
                                <label class="{{ $label }}">Jam Mulai <span class="text-red-500">*</span></label>
                                <input type="time" name="start_time" required class="{{ $input }}" value="{{ old('start_time', $fmtTime($permit->start_time)) }}">
                            </div>
                            <div>
                                <label class="{{ $label }}">Jam Selesai <span class="text-red-500">*</span></label>
                                <input type="time" name="end_time" required class="{{ $input }}" value="{{ old('end_time', $fmtTime($permit->end_time)) }}">
                            </div>
                        </div>
                        @if ($singleDay)
                            <p class="{{ $hint }}">Berlaku 1 hari{{ $maxHours ? ", maksimal {$maxHours} jam" : '' }}. Lebih dari itu, ajukan permit baru. Sebelum pekerja masuk, Spv wajib menekan <b>"Aktifkan Hari Ini"</b>{{ ($config['daily_gas_test'] ?? false) ? ' dan mengisi hasil ukur gas terbaru' : '' }}.</p>
                        @else
                            <p class="{{ $hint }}">Maksimal {{ $config['max_days'] }} hari. Setiap hari kerja, Spv wajib menekan <b>"Aktifkan Hari Ini"</b> sebelum pekerjaan dimulai (pengganti kolom Day 1–7 di form kertas).</p>
                        @endif
                    </div>
                </section>

                {{-- ============ 3. DAFTAR PEMERIKSAAN KESELAMATAN ============ --}}
                <section class="bg-white rounded-xl border border-gray-100 shadow-sm">
                    <div class="border-b border-gray-100 px-6 py-4">
                        <h3 class="text-sm font-bold text-gray-900">3. {{ $texts['checklist_title'] ?? 'Daftar Pemeriksaan Keselamatan' }}</h3>
                        <p class="text-xs text-gray-500">Semua butir wajib dijawab. Butir yang belum terpenuhi harus dipenuhi dulu sebelum permit diajukan.</p>
                    </div>

                    <div class="divide-y divide-gray-100">
                        @foreach ($config['checklist'] as $key => $item)
                            @php $answer = $answers[$key] ?? ''; @endphp
                            <div class="px-6 py-4" x-data="{ ...photoPicker({{ $maxPhotos }}, {{ $maxPhotoKb }}), ans: @js($answer) }">
                                <div class="flex gap-3">
                                    <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-gray-100 text-xs font-bold text-gray-600">{{ $loop->iteration }}</span>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm text-gray-800">{{ $item['text'] }}</p>

                                        <div class="mt-2 flex flex-wrap gap-2">
                                            <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-gray-200 px-3 py-1.5 text-sm has-[:checked]:border-emerald-400 has-[:checked]:bg-emerald-50 has-[:checked]:text-emerald-800">
                                                <input type="radio" name="checks[{{ $key }}]" value="yes" @checked($answer === 'yes') x-model="ans"
                                                       class="text-emerald-600 focus:ring-emerald-500"> Ya / Terpenuhi
                                            </label>
                                            @if ($item['allow_na'])
                                                <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-gray-200 px-3 py-1.5 text-sm has-[:checked]:border-gray-400 has-[:checked]:bg-gray-100">
                                                    <input type="radio" name="checks[{{ $key }}]" value="na" @checked($answer === 'na') x-model="ans"
                                                           class="text-gray-600 focus:ring-gray-500"> Tidak Berlaku
                                                </label>
                                            @endif
                                        </div>

                                        @if (($item['requires'] ?? null) === 'jsa')
                                            <p class="mt-2 text-xs text-gray-500">File JSA diupload di bagian 4 di bawah.</p>
                                        @endif

                                        {{-- Kaitkan permit lain (mis. permit Pekerjaan Panas) --}}
                                        @if (($item['requires'] ?? null) === 'linked_permit')
                                            @php
                                                $linkLabel = config("permits.types.{$item['linked_type']}.label");
                                                $mustYes   = $item['required_for_work_types'] ?? [];
                                            @endphp
                                            @if ($mustYes)
                                                <p x-show="@js($mustYes).some(t => workTypes.includes(t))" style="display: none;"
                                                   class="mt-2 rounded-md bg-amber-50 px-2.5 py-1.5 text-xs font-medium text-amber-800">
                                                    Kamu memilih jenis pekerjaan {{ collect($mustYes)->map(fn ($k) => strtolower($config['work_types'][$k] ?? $k))->join(' / ') }}, jadi butir ini wajib "Ya" dan harus ada permit {{ $linkLabel }}.
                                                </p>
                                            @endif
                                            <div x-show="ans === 'yes'" style="{{ $answer === 'yes' ? '' : 'display: none;' }}"
                                                 class="mt-3 rounded-lg border border-red-200 bg-red-50/40 p-3">
                                                <label class="block text-xs font-semibold text-gray-700">Nomor permit {{ $linkLabel }} terkait <span class="text-red-500">*</span></label>
                                                @if ($linkable->isEmpty())
                                                    <p class="mt-1 text-xs text-red-700">Belum ada permit {{ $linkLabel }} yang sedang berjalan. Ajukan permit {{ $linkLabel }} dulu, lalu kembali ke form ini.</p>
                                                @else
                                                    <select name="linked_permit_id" class="{{ $input }} mt-1" :required="ans === 'yes'">
                                                        <option value="">-- Pilih permit --</option>
                                                        @foreach ($linkable as $lp)
                                                            <option value="{{ $lp->id }}" @selected((string) $linkedId === (string) $lp->id)>
                                                                {{ $lp->permit_number }} · {{ \Illuminate\Support\Str::limit($lp->project_name, 40) }} · {{ $lp->statusLabel() }} ({{ $lp->start_date->format('d/m') }}–{{ $lp->end_date->format('d/m') }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <p class="mt-1 text-[11px] text-gray-500">Permit ini baru bisa diaktifkan harian kalau permit {{ $linkLabel }} terkait juga berstatus Aktif.</p>
                                                @endif
                                                @error('linked_permit_id') <p class="{{ $errorCls }}">{{ $message }}</p> @enderror
                                            </div>
                                        @endif

                                        {{-- Hasil ukur gas --}}
                                        @if (($item['requires'] ?? null) === 'gas_test')
                                            <div x-show="ans === 'yes'" style="{{ $answer === 'yes' ? '' : 'display: none;' }}"
                                                 class="mt-3 rounded-lg border border-blue-200 bg-blue-50/50 p-3">
                                                <p class="text-xs font-semibold text-blue-900">Hasil Pengukuran Gas</p>
                                                <div class="mt-2 grid grid-cols-2 sm:grid-cols-4 gap-3">
                                                    @foreach ($gasLimits as $gas => $limit)
                                                        <div>
                                                            <label class="block text-xs font-semibold text-gray-700">{{ $limit['label'] }} ({{ $limit['unit'] }})</label>
                                                            <input type="number" step="0.01" min="0" name="gas[{{ $gas }}]" value="{{ $gasOld[$gas] ?? '' }}" class="{{ $input }} mt-1">
                                                            <p class="mt-0.5 text-[11px] text-gray-500">
                                                                Aman: {{ $limit['min'] !== null ? $limit['min'] . '–' : '≤ ' }}{{ $limit['max'] }} {{ $limit['unit'] }}
                                                            </p>
                                                        </div>
                                                    @endforeach
                                                </div>
                                                <div class="mt-3 grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                    <div>
                                                        <label class="block text-xs font-semibold text-gray-700">Diukur oleh</label>
                                                        <input type="text" name="gas[tested_by_name]" maxlength="150" value="{{ $gasOld['tested_by_name'] ?? '' }}" class="{{ $input }} mt-1">
                                                    </div>
                                                    <div>
                                                        <label class="block text-xs font-semibold text-gray-700">Waktu pengukuran</label>
                                                        <input type="datetime-local" name="gas[tested_at]" value="{{ $gasOld['tested_at'] ?? '' }}" class="{{ $input }} mt-1">
                                                    </div>
                                                </div>
                                                @error('gas') <p class="{{ $errorCls }}">{{ $message }}</p> @enderror
                                            </div>
                                        @endif

                                        <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-2">
                                            <input type="text" name="check_notes[{{ $key }}]" maxlength="500" value="{{ $notes[$key] ?? '' }}"
                                                   class="{{ $input }}" placeholder="{{ $item['note'] ?? 'Catatan (opsional)' }}">
                                            @if (($item['requires'] ?? null) === 'document')
                                                {{-- Lampiran wajib kalau "Ya" (mis. denah utilitas) --}}
                                                <div>
                                                    <input type="file" name="check_docs[{{ $key }}]" accept="application/pdf,image/jpeg,image/png,image/webp"
                                                           :required="ans === 'yes' && {{ $docFiles->has($key) ? 'false' : 'true' }}"
                                                           class="block w-full text-xs text-gray-600 file:mr-2 file:rounded-md file:border-0 file:bg-amber-50 file:px-2.5 file:py-1.5 file:text-xs file:font-semibold file:text-amber-800 hover:file:bg-amber-100">
                                                    <p class="mt-1 text-[11px] text-gray-500">Lampiran PDF / foto, maks. {{ round($maxJsaKb / 1024) }} MB <span x-show="ans === 'yes'" class="font-semibold text-amber-700">· wajib</span></p>
                                                    @if ($docFiles->has($key))
                                                        <p class="mt-1 text-xs text-gray-600">File saat ini:
                                                            @foreach ($docFiles[$key] as $doc)
                                                                <a href="{{ $doc->url() }}" target="_blank" class="font-semibold text-emerald-700 hover:underline">{{ $doc->original_name }}</a>
                                                            @endforeach
                                                            (upload baru untuk mengganti)
                                                        </p>
                                                    @endif
                                                    @error("check_docs.{$key}") <p class="{{ $errorCls }}">{{ $message }}</p> @enderror
                                                </div>
                                            @else
                                            <div>
                                                <input type="file" name="check_photos[{{ $key }}][]" multiple accept="image/jpeg,image/png,image/webp" @change="pick($event)"
                                                       class="block w-full text-xs text-gray-600 file:mr-2 file:rounded-md file:border-0 file:bg-gray-100 file:px-2.5 file:py-1.5 file:text-xs file:font-semibold file:text-gray-700 hover:file:bg-gray-200">
                                                <p x-show="compressing" class="mt-1 text-xs font-medium text-blue-600" style="display: none;">Mengompres foto...</p>
                                                <p x-show="info" x-text="info" class="mt-1 text-xs text-emerald-700" style="display: none;"></p>
                                                <p x-show="error" x-text="error" class="mt-1 text-xs font-medium text-red-600" style="display: none;"></p>
                                            </div>
                                            @endif
                                        </div>
                                        <div x-show="previews.length" class="mt-2 flex gap-2" style="display: none;">
                                            <template x-for="p in previews" :key="p.url">
                                                <img :src="p.url" :alt="p.name" class="h-14 w-14 rounded-md border border-gray-200 object-cover">
                                            </template>
                                        </div>
                                        @if ($isEdit && ($item['requires'] ?? null) !== 'document' && $permit->files->where('kind', 'check')->where('item_key', $key)->isNotEmpty())
                                            <p class="mt-1 text-xs text-gray-500">Sudah ada {{ $permit->files->where('kind', 'check')->where('item_key', $key)->count() }} foto sebelumnya (tetap disimpan).</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Keterangan lain yang diperlukan (form kertas: baris kosong di bawah checklist) --}}
                    @if ($extraMax > 0)
                        <div class="border-t border-gray-100 px-6 py-4">
                            <p class="text-sm font-semibold text-gray-700">Keterangan lain yang diperlukan <span class="font-normal text-gray-400">(opsional)</span></p>
                            <p class="text-xs text-gray-500">Pengaman tambahan yang sudah disiapkan di luar daftar di atas.</p>
                            <div class="mt-2 space-y-2">
                                @for ($i = 0; $i < $extraMax; $i++)
                                    <input type="text" name="extra_notes[]" maxlength="255" class="{{ $input }}"
                                           value="{{ $extraOld[$i] ?? '' }}" placeholder="{{ $i === 0 ? 'Contoh: lifeline horizontal dipasang di sepanjang atap' : 'Keterangan ' . ($i + 1) }}">
                                @endfor
                            </div>
                        </div>
                    @endif
                </section>

                {{-- ============ 4. JSA ============ --}}
                <section class="bg-white rounded-xl border border-gray-100 shadow-sm">
                    <div class="border-b border-gray-100 px-6 py-4">
                        <h3 class="text-sm font-bold text-gray-900">4. Analisa Keselamatan Pekerjaan (JSA)
                            @if ($jsaRequired) <span class="text-red-500">*</span> @else <span class="font-normal text-gray-400">(opsional)</span> @endif
                        </h3>
                    </div>
                    <div class="px-6 py-5">
                        @if ($isEdit && $permit->jsaFile)
                            <p class="mb-2 text-sm text-gray-700">File saat ini:
                                <a href="{{ $permit->jsaFile->url() }}" target="_blank" class="font-semibold text-emerald-700 hover:underline">{{ $permit->jsaFile->original_name }}</a>.
                                Upload file baru hanya kalau ingin mengganti.</p>
                        @endif
                        <input type="file" name="jsa_file" accept="application/pdf,image/jpeg,image/png,image/webp" @if ($jsaRequired && ! ($isEdit && $permit->jsaFile)) required @endif
                               class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-emerald-50 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-emerald-700 hover:file:bg-emerald-100">
                        <p class="{{ $hint }}">PDF atau foto, maksimal {{ round($maxJsaKb / 1024) }} MB.</p>
                    </div>
                </section>

                {{-- ============ 5. PERNYATAAN ============ --}}
                <section class="bg-white rounded-xl border border-gray-100 shadow-sm px-6 py-5">
                    <label class="flex cursor-pointer items-start gap-3 text-sm text-gray-800">
                        <input type="checkbox" name="declaration" value="1" required @checked(old('declaration'))
                               class="mt-0.5 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                        <span>Saya menyatakan data di atas benar, {{ $texts['declaration'] ?? 'persyaratan keselamatan sudah dipenuhi sesuai Daftar Pemeriksaan' }}, dan pekerjaan <b>tidak akan dimulai</b> sebelum permit berstatus <b>Aktif</b> dan diaktifkan pada hari kerja yang bersangkutan.</span>
                    </label>
                </section>

                <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                    <a href="{{ $isEdit ? route('my-permit.show', $permit) : route('my-permit.index') }}"
                       class="inline-flex justify-center rounded-lg px-5 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-100">Batal</a>
                    <button type="submit" :disabled="submitting"
                            class="inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 disabled:cursor-wait disabled:opacity-60">
                        <span x-text="submitting ? 'Mengirim...' : @js($submitLabel)">{{ $submitLabel }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    <x-photo-picker-script />
</x-app-layout>