{{--
    Detail Permit to Work, dipakai bersama oleh:
    - Request QHSE (pengaju)  → <x-work-permit.detail :permit="$permit" />
    - Tasklist (pemeriksa)    → <x-work-permit.detail :permit="$permit"> <x-slot:actions>...</x-slot:actions> </x-work-permit.detail>
--}}
@props(['permit'])

@php
    use App\Models\WorkPermit;

    $config    = $permit->config();
    $checks    = $permit->checks->keyBy('item_key');
    $photos    = $permit->files->where('kind', 'check')->groupBy('item_key');
    $gasTest   = $permit->openGasTest();
    $gasLimits = $permit->gasLimits();
    $dailyGas  = $permit->gasTests->whereNotNull('work_permit_daily_log_id');
    $dayOfLog  = $permit->dailyLogs->pluck('day_number', 'id');
    $fmtGas    = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, ',', ''), '0'), ',');
    $dailyLogs = $permit->dailyLogs->keyBy(fn ($d) => $d->day_number);
    $fmtTime   = fn ($t) => $t ? substr((string) $t, 0, 5) : '-';
    $todayDay  = $permit->dayNumberFor(today());
    $color     = $config['icon_color'] ?? '#dc2626';
    $watcher   = $permit->watcher();
    $watchMin  = $permit->postWatchMinutes();
    $linked    = $permit->linkedPermit();

    // Butir pengawasan: dianggap terpenuhi kalau tahapnya sudah lewat
    $stageDone = [
        'check'    => (bool) $permit->signature('checked'),
        'daily'    => $permit->dailyLogs->isNotEmpty(),
        'closeout' => (bool) $permit->post_watch_until,
    ];

    $signatureBoxes = [
        'open'  => ['title' => 'Sebelum Pekerjaan Dilaksanakan'],
        'close' => ['title' => 'Setelah Pekerjaan Dilaksanakan'],
    ];
    $stepLabels = [
        'checked'      => ['Diperiksa', 'Atasan pengaju (Supervisor / Section Head)'],
        'approved'     => ['Disetujui', 'QHSE'],
        'acknowledged' => ['Diketahui', 'QHSE'],
    ];
@endphp

<div class="space-y-6">

    {{-- Kepala --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">{{ $config['title'] }}</p>
            <h3 class="text-2xl font-bold text-gray-900">{{ $permit->permit_number }}</h3>
            <p class="mt-1 text-sm text-gray-500">Diajukan {{ $permit->submitted_at?->translatedFormat('d F Y, H:i') }} oleh {{ $permit->applicant_name }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <span class="inline-flex rounded-full border px-3 py-1 text-sm font-semibold"
                  style="color: {{ $color }}; border-color: {{ $color }}40; background-color: {{ $color }}10">{{ $config['label'] }}</span>
            <span class="inline-flex rounded-full border px-3 py-1 text-sm font-semibold {{ $permit->statusClass() }}">{{ $permit->statusLabel() }}</span>
            @if ($permit->status === WorkPermit::STATUS_ACTIVE && $todayDay)
                <span class="inline-flex rounded-full px-3 py-1 text-sm font-bold {{ $dailyLogs->has($todayDay) ? 'bg-emerald-600 text-white' : 'bg-amber-500 text-white' }}">
                    {{ $dailyLogs->has($todayDay) ? "Day {$todayDay} sudah diaktifkan" : "Day {$todayDay} belum diaktifkan" }}
                </span>
            @endif
        </div>
    </div>

    {{-- Sertifikat Penyelesaian (permit Closed) --}}
    @if ($permit->status === WorkPermit::STATUS_CLOSED && $permit->certificate)
        @php $cert = $permit->certificate; @endphp
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 rounded-xl border-2 {{ $cert->incident_free ? 'border-emerald-300 bg-emerald-50' : 'border-amber-300 bg-amber-50' }} px-6 py-5">
            <div class="flex items-center gap-4">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full {{ $cert->incident_free ? 'bg-emerald-600' : 'bg-amber-500' }} text-white">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z"/></svg>
                </span>
                <div>
                    <p class="text-sm font-bold {{ $cert->incident_free ? 'text-emerald-900' : 'text-amber-900' }}">Sertifikat Penyelesaian {{ $cert->incident_free ? '' : '(dengan catatan insiden)' }}</p>
                    <p class="text-sm {{ $cert->incident_free ? 'text-emerald-800' : 'text-amber-800' }}">{{ $cert->certificate_number }} · diterbitkan {{ $cert->issued_at->translatedFormat('d M Y, H:i') }}</p>
                </div>
            </div>
            <a href="{{ route('permit-certificate.show', $permit) }}" target="_blank"
               class="inline-flex items-center justify-center gap-2 rounded-lg {{ $cert->incident_free ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-amber-600 hover:bg-amber-700' }} px-4 py-2.5 text-sm font-semibold text-white shadow-sm">
                Lihat / Cetak Sertifikat
            </a>
        </div>
    @endif

    @if ($permit->last_note && in_array($permit->status, [WorkPermit::STATUS_RETURNED, WorkPermit::STATUS_STOPPED], true))
        <div class="rounded-xl border border-orange-200 bg-orange-50 px-5 py-4">
            <p class="text-sm font-bold text-orange-800">{{ $permit->status === WorkPermit::STATUS_STOPPED ? 'Alasan pekerjaan dihentikan' : 'Catatan pemeriksa' }}</p>
            <p class="mt-1 text-sm text-orange-800 whitespace-pre-line">{{ $permit->last_note }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ================= Kolom kiri ================= --}}
        <div class="lg:col-span-2 space-y-6">

            <section class="bg-white rounded-xl border border-gray-100 shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4"><h4 class="text-sm font-bold text-gray-900">Pelaksanaan Pekerjaan</h4></div>
                <dl class="px-6 py-5 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">Pengaju</dt>
                        <dd class="mt-0.5 font-medium text-gray-900">{{ $permit->applicant_name }}</dd>
                        <dd class="text-xs text-gray-500">{{ $permit->applicantPositionLabel() }} · {{ $permit->applicantDepartment?->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">Departemen Pemilik Area</dt>
                        <dd class="mt-0.5 font-medium text-gray-900">{{ $permit->ownerDepartment?->name }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">Nama Pekerjaan</dt>
                        <dd class="mt-0.5 font-medium text-gray-900">{{ $permit->project_name }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">Lokasi Kerja</dt>
                        <dd class="mt-0.5 font-medium text-gray-900">{{ $permit->location }}</dd>
                        @if ($permit->hasCoordinates())
                            <x-hazard-report.map class="mt-2" :lat="$permit->latitude" :lng="$permit->longitude"
                                :color="$color" speed="1.2s"
                                :pulse="in_array($permit->status, [WorkPermit::STATUS_ACTIVE], true)"
                                :label="$config['label']" />
                        @endif
                    </div>
                    <div><dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">Nama Atasan</dt><dd class="mt-0.5 font-medium text-gray-900">{{ $permit->supervisor_name }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">Jumlah Pekerja</dt><dd class="mt-0.5 font-medium text-gray-900">{{ $permit->worker_count }} orang</dd></div>
                    <div><dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">{{ $watcher['short'] }}</dt><dd class="mt-0.5 font-medium text-gray-900">{{ $permit->fire_watch_name }}</dd></div>
                    @if (! empty($config['work_types']))
                        <div><dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">Jenis Pekerjaan</dt><dd class="mt-0.5 font-medium text-gray-900">{{ implode(', ', $permit->workTypeLabels()) }}</dd></div>
                    @endif
                    <div class="sm:col-span-2"><dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">Alat yang Digunakan</dt><dd class="mt-0.5 text-gray-800 whitespace-pre-line">{{ $permit->tools }}</dd></div>
                </dl>
            </section>

            {{-- Waktu & Day 1–7 --}}
            <section class="bg-white rounded-xl border border-gray-100 shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h4 class="text-sm font-bold text-gray-900">Waktu Pemberlakuan</h4>
                    <p class="text-xs text-gray-500">
                        @if ($permit->start_date->equalTo($permit->end_date))
                            {{ $permit->start_date->translatedFormat('l, d M Y') }}
                        @else
                            {{ $permit->start_date->translatedFormat('l, d M Y') }} s/d {{ $permit->end_date->translatedFormat('l, d M Y') }}
                        @endif
                        · Jam {{ $fmtTime($permit->start_time) }}–{{ $fmtTime($permit->end_time) }} · {{ $permit->durationDays() }} hari
                    </p>
                </div>
                <div class="px-6 py-5 grid grid-cols-4 sm:grid-cols-7 gap-2">
                    @for ($day = 1; $day <= $config['max_days']; $day++)
                        @php
                            $log    = $dailyLogs->get($day);
                            $inside = $day <= $permit->durationDays();
                            $date   = $permit->start_date->copy()->addDays($day - 1);
                        @endphp
                        <div class="rounded-lg border p-2 text-center {{ ! $inside ? 'border-dashed border-gray-200 bg-gray-50 text-gray-300' : ($log ? 'border-emerald-200 bg-emerald-50' : ($day === $todayDay ? 'border-amber-300 bg-amber-50' : 'border-gray-200')) }}">
                            <p class="text-xs font-bold">Day {{ $day }}</p>
                            @if ($inside)
                                <p class="text-[11px] text-gray-500">{{ $date->translatedFormat('d M') }}</p>
                                @if ($log)
                                    <p class="mt-1 text-[11px] font-semibold text-emerald-700">{{ $log->created_at->format('H:i') }}</p>
                                    <p class="truncate text-[10px] text-gray-600" title="{{ $log->user_name }}">{{ $log->user_name }}</p>
                                @else
                                    <p class="mt-1 text-[11px] text-gray-400">{{ $date->isPast() && ! $date->isToday() ? 'tidak aktif' : 'belum' }}</p>
                                @endif
                            @endif
                        </div>
                    @endfor
                </div>
            </section>

            {{-- Catatan & foto aktivasi harian --}}
            @php $dailyWithInfo = $permit->dailyLogs->filter(fn ($d) => $d->note || ($d->relationLoaded('photos') && $d->photos->isNotEmpty())); @endphp
            @if ($dailyWithInfo->isNotEmpty())
                <section class="bg-white rounded-xl border border-gray-100 shadow-sm">
                    <div class="border-b border-gray-100 px-6 py-4"><h4 class="text-sm font-bold text-gray-900">Catatan Harian</h4></div>
                    <ul class="divide-y divide-gray-100">
                        @foreach ($dailyWithInfo as $d)
                            <li class="px-6 py-3">
                                <p class="text-sm font-semibold text-gray-800">Day {{ $d->day_number }} · {{ $d->work_date->translatedFormat('d M Y') }} <span class="font-normal text-gray-500">· {{ $d->user_name }}, {{ $d->created_at->format('H:i') }}</span></p>
                                @if ($d->note)<p class="text-sm text-gray-700">{{ $d->note }}</p>@endif
                                @if ($d->relationLoaded('photos') && $d->photos->isNotEmpty())
                                    <div class="mt-2 flex gap-2">
                                        @foreach ($d->photos as $photo)
                                            <a href="{{ $photo->url() }}" target="_blank" class="block h-14 w-14 overflow-hidden rounded-md border border-gray-200 bg-gray-100">
                                                <img loading="lazy" src="{{ $photo->url() }}" alt="" class="h-full w-full object-cover">
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            {{-- Close-out --}}
            @if ($permit->work_finished_at)
                @php $closeoutPhotos = $permit->files->where('kind', 'closeout'); @endphp
                <section class="bg-white rounded-xl border border-indigo-100 shadow-sm">
                    <div class="border-b border-gray-100 px-6 py-4"><h4 class="text-sm font-bold text-gray-900">Close-out (Setelah Pekerjaan)</h4></div>
                    <div class="px-6 py-5 space-y-3 text-sm">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div><p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Pekerjaan selesai</p><p class="font-medium text-gray-900">{{ $permit->work_finished_at->translatedFormat('d M Y H:i') }}</p></div>
                            @if ($watchMin > 0)
                                <div><p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Pengawasan berakhir</p>
                                    <p class="font-medium text-gray-900">{{ $permit->post_watch_until?->translatedFormat('d M Y H:i') }}
                                        <span class="text-xs text-emerald-700">({{ (int) $permit->work_finished_at->diffInMinutes($permit->post_watch_until) }} menit)</span></p></div>
                            @endif
                        </div>
                        <p class="text-gray-800 whitespace-pre-line">{{ $permit->final_condition }}</p>
                        @if ($closeoutPhotos->isNotEmpty())
                            <div class="grid grid-cols-3 sm:grid-cols-4 gap-2">
                                @foreach ($closeoutPhotos as $photo)
                                    <a href="{{ $photo->url() }}" target="_blank" class="block aspect-square overflow-hidden rounded-lg border border-gray-200 bg-gray-100">
                                        <img loading="lazy" src="{{ $photo->url() }}" alt="" class="h-full w-full object-cover">
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </section>
            @endif

            {{-- Checklist --}}
            <section class="bg-white rounded-xl border border-gray-100 shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4"><h4 class="text-sm font-bold text-gray-900">{{ $config['texts']['checklist_title'] ?? 'Daftar Pemeriksaan Keselamatan' }}</h4></div>
                <ul class="divide-y divide-gray-100">
                    @foreach ($config['checklist'] as $key => $item)
                        @php $check = $checks->get($key); @endphp
                        <li class="flex gap-3 px-6 py-3">
                            @if ($check?->isYes())
                                <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded bg-emerald-100 text-emerald-700">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                </span>
                            @else
                                <span class="mt-0.5 shrink-0 rounded border border-gray-300 px-1 text-[10px] font-bold leading-5 text-gray-500">N/A</span>
                            @endif
                            <div class="min-w-0 flex-1">
                                <p class="text-sm text-gray-800">{{ $check?->item_text ?? $item['text'] }}</p>
                                @if ($check?->note)
                                    <p class="text-xs italic text-blue-700">{{ $check->note }}</p>
                                @endif
                                @if (($item['requires'] ?? null) === 'jsa' && $permit->jsaFile)
                                    <a href="{{ $permit->jsaFile->url() }}" target="_blank" class="mt-1 inline-flex items-center gap-1 text-xs font-semibold text-emerald-700 hover:underline">📎 {{ $permit->jsaFile->original_name }}</a>
                                @endif
                                @if (($item['requires'] ?? null) === 'linked_permit' && $check?->isYes())
                                    @php $linkOk = $linked && ! $permit->linkedPermitBlocker(); @endphp
                                    <p class="mt-1 inline-flex flex-wrap items-center gap-1.5 text-xs">
                                        <span class="font-semibold text-gray-700">🔗 {{ $linked?->permit_number ?? ($permit->details['linked_permit_number'] ?? '-') }}</span>
                                        @if ($linked)
                                            <span class="rounded-full border px-2 py-0.5 font-semibold {{ $linked->statusClass() }}">{{ $linked->trashed() ? 'Dihapus' : $linked->statusLabel() }}</span>
                                            <span class="text-gray-500">{{ \Illuminate\Support\Str::limit($linked->project_name, 50) }}</span>
                                        @endif
                                    </p>
                                    @if (! $linkOk && in_array($permit->status, [WorkPermit::STATUS_ACTIVE, WorkPermit::STATUS_PENDING_APPROVAL, WorkPermit::STATUS_PENDING_CHECK], true))
                                        <p class="mt-1 text-xs font-medium text-amber-700">Permit {{ $linked?->typeLabel() ?? 'terkait' }} belum aktif / tidak berlaku hari ini, aktivasi harian akan tertahan.</p>
                                    @endif
                                @endif
                                @if (($item['requires'] ?? null) === 'gas_test' && $gasTest)
                                    <div class="mt-2 flex flex-wrap gap-2 text-xs">
                                        @foreach ($gasLimits as $gas => $limit)
                                            @continue($gasTest->$gas === null)
                                            @php $ok = \App\Models\WorkPermitGasTest::isWithinLimit($gas, $gasTest->$gas, $gasLimits); @endphp
                                            <span class="rounded-md border px-2 py-0.5 font-semibold {{ $ok ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-red-200 bg-red-50 text-red-700' }}">
                                                {{ $limit['label'] }} {{ $fmtGas($gasTest->$gas) }} {{ $limit['unit'] }}
                                            </span>
                                        @endforeach
                                        <span class="text-gray-500">diukur {{ $gasTest->tested_by_name }}, {{ $gasTest->tested_at->translatedFormat('d M H:i') }}</span>
                                    </div>
                                @endif
                                @if ($photos->has($key))
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        @foreach ($photos[$key] as $photo)
                                            @if ($photo->isImage())
                                                <a href="{{ $photo->url() }}" target="_blank" class="block h-14 w-14 overflow-hidden rounded-md border border-gray-200 bg-gray-100">
                                                    <img loading="lazy" src="{{ $photo->url() }}" alt="" class="h-full w-full object-cover">
                                                </a>
                                            @else
                                                <a href="{{ $photo->url() }}" target="_blank" class="inline-flex items-center gap-1 rounded-md border border-gray-200 px-2 py-1 text-xs font-semibold text-emerald-700 hover:bg-gray-50">📎 {{ $photo->original_name }}</a>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
                @if (! \App\Models\WorkPermit::jsaRequiredFor($config) && $permit->jsaFile)
                    <div class="border-t border-gray-100 px-6 py-3 text-sm">
                        <span class="text-xs font-semibold uppercase tracking-wider text-gray-400">Lampiran JSA</span>
                        <a href="{{ $permit->jsaFile->url() }}" target="_blank" class="ml-2 text-xs font-semibold text-emerald-700 hover:underline">📎 {{ $permit->jsaFile->original_name }}</a>
                    </div>
                @endif
                @if ($permit->extraNotes())
                    <div class="border-t border-gray-100 px-6 py-4">
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Keterangan lain yang diperlukan</p>
                        <ul class="mt-2 space-y-1.5">
                            @foreach ($permit->extraNotes() as $note)
                                <li class="flex gap-3 text-sm text-gray-800">
                                    <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded bg-emerald-100 text-emerald-700">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    </span>
                                    {{ $note }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </section>

            {{-- Pengukuran gas ulang setiap aktivasi (Ruang Terbatas) --}}
            @if ($dailyGas->isNotEmpty())
                <section class="bg-white rounded-xl border border-gray-100 shadow-sm">
                    <div class="border-b border-gray-100 px-6 py-4"><h4 class="text-sm font-bold text-gray-900">Pengukuran Gas Sebelum Masuk</h4></div>
                    <ul class="divide-y divide-gray-100">
                        @foreach ($dailyGas as $test)
                            <li class="px-6 py-3">
                                <p class="text-xs text-gray-500">Day {{ $dayOfLog[$test->work_permit_daily_log_id] ?? '-' }} · {{ $test->tested_at->translatedFormat('d M Y H:i') }} · diukur {{ $test->tested_by_name }}</p>
                                <div class="mt-1 flex flex-wrap gap-2 text-xs">
                                    @foreach ($gasLimits as $gas => $limit)
                                        @continue($test->$gas === null)
                                        @php $ok = \App\Models\WorkPermitGasTest::isWithinLimit($gas, $test->$gas, $gasLimits); @endphp
                                        <span class="rounded-md border px-2 py-0.5 font-semibold {{ $ok ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-red-200 bg-red-50 text-red-700' }}">
                                            {{ $limit['label'] }} {{ $fmtGas($test->$gas) }} {{ $limit['unit'] }}
                                        </span>
                                    @endforeach
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            {{-- Pengawasan oleh supervisi --}}
            <section class="bg-white rounded-xl border border-gray-100 shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4"><h4 class="text-sm font-bold text-gray-900">{{ $config['texts']['supervision_title'] ?? 'Pengawasan oleh Supervisi Pelaksana' }}</h4></div>
                <ul class="px-6 py-4 space-y-2">
                    @foreach ($config['supervision'] as $key => $item)
                        @php $done = $stageDone[$item['stage']] ?? false; @endphp
                        <li class="flex items-start gap-3 text-sm {{ $done ? 'text-gray-800' : 'text-gray-400' }}">
                            <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded border {{ $done ? 'border-emerald-300 bg-emerald-100 text-emerald-700' : 'border-gray-300' }}">
                                @if ($done)<svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>@endif
                            </span>
                            <span>{{ $item['text'] }}
                                <span class="block text-[11px] text-gray-400">{{ ['check' => 'Tercentang saat permit diperiksa', 'daily' => 'Tercentang saat aktivasi harian', 'closeout' => 'Tercentang saat close-out'][$item['stage']] }}</span>
                            </span>
                        </li>
                    @endforeach
                </ul>
            </section>

            {{-- Tanda tangan digital --}}
            <section class="bg-white rounded-xl border border-gray-100 shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h4 class="text-sm font-bold text-gray-900">Pemeriksaan & Persetujuan</h4>
                    <p class="text-xs text-gray-500">Telah dilakukan pemeriksaan pada kondisi di atas, memenuhi persyaratan & dalam kondisi aman.</p>
                </div>
                <div class="px-6 py-5 space-y-5">
                    @foreach (WorkPermit::SIGNATURES as $phase => $steps)
                        <div>
                            <p class="mb-2 text-xs font-bold uppercase tracking-wider text-gray-500">{{ $signatureBoxes[$phase]['title'] }}</p>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                @foreach ($steps as $step => $action)
                                    @php $sig = $permit->signature($action); @endphp
                                    <div class="rounded-lg border p-3 text-center {{ $sig ? 'border-emerald-200 bg-emerald-50/50' : 'border-dashed border-gray-200' }}">
                                        <p class="text-xs font-bold text-gray-700">{{ $stepLabels[$step][0] }}</p>
                                        <p class="text-[11px] text-gray-500">{{ $stepLabels[$step][1] }}</p>
                                        @if ($sig)
                                            <span class="mt-2 inline-block rounded border border-emerald-600 px-1.5 text-[10px] font-bold text-emerald-700">✓ DISETUJUI DIGITAL</span>
                                            <p class="mt-1 text-sm font-semibold text-gray-900">{{ $sig->user_name }}</p>
                                            <p class="text-[11px] text-gray-500">{{ $sig->user_title }}</p>
                                            <p class="text-[11px] text-gray-400">{{ $sig->created_at->translatedFormat('d M Y H:i') }}</p>
                                        @else
                                            <p class="mt-3 text-xs italic text-gray-400">menunggu</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        </div>

        {{-- ================= Kolom kanan ================= --}}
        <div class="space-y-6">
            @isset($actions)
                {{ $actions }}
            @endisset

            <section class="bg-white rounded-xl border border-gray-100 shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4"><h4 class="text-sm font-bold text-gray-900">Riwayat</h4></div>
                <ol class="px-6 py-5 space-y-4">
                    @foreach ($permit->logs as $log)
                        <li class="flex gap-3">
                            <span class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full {{ $log->isNegative() ? 'bg-red-500' : 'bg-emerald-500' }}"></span>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold {{ $log->isNegative() ? 'text-red-700' : 'text-gray-900' }}">{{ $log->label() }}</p>
                                <p class="text-xs text-gray-500">{{ $log->user_name }}{{ $log->user_title ? ' · ' . $log->user_title : '' }}</p>
                                <p class="text-xs text-gray-400">{{ $log->created_at->translatedFormat('d M Y, H:i') }}</p>
                                @if ($log->note)
                                    <p class="mt-1 text-xs text-gray-600 whitespace-pre-line">{{ \Illuminate\Support\Str::limit($log->note, 200) }}</p>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ol>
            </section>

            <section class="rounded-xl border border-gray-100 bg-gray-50 px-5 py-4 text-xs text-gray-500">
                <p class="font-semibold text-gray-700">Daftar Distribusi</p>
                <p>{{ implode(' · ', $config['distribution']) }}</p>
                <p class="mt-2">{{ $config['form_number'] }} · Rev. {{ $config['form_rev'] }}</p>
            </section>
        </div>
    </div>
</div>