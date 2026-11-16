{{--
    Surat Izin Kerja (PTW) versi digital: A4 portrait, siap cetak / "Save as PDF".
    Isi mengikuti form kertas sesuai jenis permit (config/permits.php).
--}}
@php
    use App\Models\WorkPermit;

    $fmtTime   = fn ($t) => $t ? substr((string) $t, 0, 5) : '-';
    $fmtGas    = fn ($v) => rtrim(rtrim(number_format((float) $v, 2, ',', ''), '0'), ',');
    $issuer    = config('permits.certificate.issuer', 'HSE Department');
    $site      = config('permits.certificate.site', '');
    $color     = $config['icon_color'] ?? '#dc2626';
    [$stampText, $stampColor, $stampNote] = $stamp;

    $checks    = $permit->checks->keyBy('item_key');
    $gasTest   = $permit->openGasTest();
    $gasLimits = $permit->gasLimits();
    $dailyGas  = $permit->gasTests->whereNotNull('work_permit_daily_log_id')->keyBy('work_permit_daily_log_id');
    $dailyLogs = $permit->dailyLogs->keyBy(fn ($d) => $d->day_number);
    $watcher   = $permit->watcher();
    $linked    = $permit->linkedPermit();
    $works     = $permit->workTypeLabels();

    $checked   = $permit->signature('checked');
    $approved  = $permit->signature('approved');
    $lastStop  = $permit->status === WorkPermit::STATUS_STOPPED ? $permit->logs->where('action', 'stopped')->last() : null;

    $stageDone = [
        'check'    => (bool) $checked,
        'daily'    => $permit->dailyLogs->isNotEmpty(),
        'closeout' => (bool) $permit->post_watch_until,
    ];
    $singleDay = $permit->start_date->equalTo($permit->end_date);
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Izin {{ $permit->permit_number }}</title>
    <style>
        @page { size: A4 portrait; margin: 10mm; }
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; }
        body { background: #e5e7eb; font-family: "Segoe UI", Arial, Helvetica, sans-serif; color: #111827; font-size: 10.5px; -webkit-print-color-adjust: exact; print-color-adjust: exact; }

        .toolbar { position: sticky; top: 0; z-index: 10; display: flex; gap: 8px; justify-content: center; align-items: center; flex-wrap: wrap; padding: 12px; background: #111827; }
        .toolbar a, .toolbar button { font-family: inherit; font-size: 14px; font-weight: 600; line-height: 1; border: 0; border-radius: 8px; padding: 10px 16px; cursor: pointer; text-decoration: none; }
        .btn-print { background: #059669; color: #fff; }
        .btn-back { background: #374151; color: #f9fafb; }
        .toolbar span { color: #9ca3af; font-size: 12px; }

        .sheet { width: 210mm; min-height: 297mm; margin: 16px auto; padding: 12mm 13mm; background: #fff; box-shadow: 0 10px 30px rgba(0,0,0,.15); }

        /* Kepala */
        .head { display: flex; justify-content: space-between; gap: 12px; padding-bottom: 8px; border-bottom: 3px solid {{ $color }}; }
        .brand { display: flex; gap: 10px; align-items: center; }
        .logo { width: 38px; height: 38px; border-radius: 9px; background: {{ $color }}; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 12px; }
        .brand .name { font-weight: 800; font-size: 13px; }
        .brand .sub { font-size: 10px; color: #6b7280; }
        .form-no { text-align: right; font-size: 10px; color: #4b5563; line-height: 1.5; }
        .form-no b { font-size: 13px; color: #111827; }

        .title-row { display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-top: 8px; }
        .title h1 { margin: 0; font-size: 17px; letter-spacing: .3px; text-transform: uppercase; }
        .title p { margin: 2px 0 0; font-size: 10.5px; color: #6b7280; }
        .stamp { flex-shrink: 0; text-align: center; border: 3px solid {{ $stampColor }}; color: {{ $stampColor }}; border-radius: 8px; padding: 5px 12px; transform: rotate(-4deg); max-width: 62mm; }
        .stamp b { display: block; font-size: 18px; letter-spacing: 1.5px; font-weight: 900; }
        .stamp span { display: block; font-size: 9px; font-weight: 700; margin-top: 1px; }

        .alert { margin-top: 6px; padding: 5px 9px; border-radius: 6px; font-size: 10px; line-height: 1.45; }
        .alert.info { background: #fffbeb; border: 1px solid #fde68a; color: #78350f; }
        .alert.danger { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }

        /* Bagian */
        h2 { margin: 8px 0 3px; font-size: 10px; letter-spacing: .8px; text-transform: uppercase; color: {{ $color }}; }
        table { width: 100%; border-collapse: collapse; }
        .info td { padding: 2px 6px; border: 1px solid #e5e7eb; vertical-align: top; }
        .info td.k { width: 22%; color: #6b7280; background: #f9fafb; }
        .info td.v { width: 28%; font-weight: 600; }

        .days { display: grid; gap: 4px; margin-top: 4px; }
        .day { border: 1px solid #d1d5db; border-radius: 5px; padding: 3px; text-align: center; min-height: 34px; }
        .day.on { border-color: #10b981; background: #ecfdf5; }
        .day .n { font-size: 9px; font-weight: 800; color: #6b7280; }
        .day .d { font-size: 9px; color: #6b7280; }
        .day .ok { font-size: 10px; font-weight: 800; color: #047857; }
        .day .by { font-size: 8px; color: #374151; }

        .check th { text-align: left; font-size: 9px; text-transform: uppercase; letter-spacing: .5px; color: #6b7280; background: #f3f4f6; padding: 3px 6px; border: 1px solid #e5e7eb; }
        .check td { padding: 2.5px 6px; border: 1px solid #e5e7eb; vertical-align: top; }
        .check td.no { width: 6%; text-align: center; color: #6b7280; }
        .check td.ans { width: 13%; text-align: center; font-weight: 800; }
        .yes { color: #047857; }
        .na { color: #6b7280; }
        .note { display: block; margin-top: 1px; font-size: 9.5px; font-style: italic; color: #1d4ed8; }
        .gas { display: inline-block; margin: 2px 3px 0 0; padding: 1px 5px; border: 1px solid #a7f3d0; background: #ecfdf5; border-radius: 4px; font-size: 9.5px; font-weight: 700; color: #065f46; }

        .signs { display: grid; grid-template-columns: repeat(3, 1fr); gap: 6px; margin-top: 4px; }
        .sign { border: 1px solid #e5e7eb; border-radius: 6px; padding: 6px; text-align: center; }
        .sign .role { font-size: 9.5px; color: #6b7280; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; }
        .sign .seal { display: inline-block; margin: 5px 0 3px; border: 1.5px solid #059669; color: #059669; border-radius: 4px; padding: 1px 6px; font-size: 8.5px; font-weight: 800; transform: rotate(-3deg); }
        .sign .seal.pending { border-color: #9ca3af; color: #9ca3af; }
        .sign .who { border-top: 1px solid #9ca3af; padding-top: 3px; font-weight: 700; font-size: 11px; }
        .sign .meta { font-size: 9px; color: #6b7280; }

        .foot { margin-top: 8px; padding-top: 6px; border-top: 1px dashed #d1d5db; display: flex; justify-content: space-between; gap: 10px; font-size: 9px; color: #6b7280; }

        @media print {
            body { background: #fff; }
            .toolbar { display: none; }
            .sheet { width: auto; min-height: 0; margin: 0; padding: 0; box-shadow: none; }
            tr, .sign, .day, .keep { break-inside: avoid; }
        }
        @media screen and (max-width: 820px) {
            .sheet { width: auto; min-height: 0; margin: 0; padding: 16px; }
            .title-row, .head { flex-direction: column; align-items: flex-start; }
            .form-no { text-align: left; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button type="button" class="btn-print" onclick="window.print()">Cetak / Simpan PDF</button>
        <a class="btn-back" href="{{ url()->previous() !== url()->current() ? url()->previous() : route('my-permit.index') }}">Kembali</a>
        <span>Di dialog cetak pilih "Save as PDF", layout Portrait.</span>
    </div>

    <div class="sheet">
        {{-- Kepala --}}
        <div class="head">
            <div class="brand">
                <div class="logo">PTW</div>
                <div>
                    <div class="name">{{ config('app.name') }}</div>
                    <div class="sub">{{ $issuer }}{{ $site ? ' · ' . $site : '' }}</div>
                </div>
            </div>
            <div class="form-no">
                No. Permit <b>{{ $permit->permit_number }}</b><br>
                {{ $config['form_number'] }} · Rev. {{ $config['form_rev'] }}
            </div>
        </div>

        <div class="title-row">
            <div class="title">
                <h1>{{ $config['title'] }}</h1>
                <p>Permit to Work · {{ $config['label'] }} · disetujui secara digital melalui {{ config('app.name') }}</p>
            </div>
            <div class="stamp">
                <b>{{ $stampText }}</b>
                @if ($stampNote) <span>{{ $stampNote }}</span> @endif
            </div>
        </div>

        @if ($lastStop)
            <div class="alert danger"><b>STOP WORK</b> · {{ $lastStop->created_at->translatedFormat('d M Y H:i') }} oleh {{ $lastStop->user_name }}: {{ $lastStop->note }}</div>
        @endif
        @if (! empty($config['texts']['perhatian']))
            <div class="alert info"><b>PERHATIAN:</b> {{ $config['texts']['perhatian'] }}</div>
        @endif

        {{-- Pelaksanaan --}}
        <h2>Pelaksanaan Pekerjaan</h2>
        <table class="info">
            <tr>
                <td class="k">Nama Pekerjaan</td><td class="v" colspan="3">{{ $permit->project_name }}</td>
            </tr>
            <tr>
                <td class="k">Lokasi</td><td class="v">{{ $permit->location }}</td>
                <td class="k">Area / Departemen</td><td class="v">{{ $permit->ownerDepartment?->name ?? '-' }}</td>
            </tr>
            <tr>
                <td class="k">Dibuat oleh</td><td class="v">{{ $permit->applicant_name }} <span style="font-weight:400;color:#6b7280">· {{ $permit->applicantPositionLabel() }} {{ $permit->applicantDepartment?->name }}</span></td>
                <td class="k">Nama Atasan</td><td class="v">{{ $permit->supervisor_name ?: '-' }}</td>
            </tr>
            <tr>
                <td class="k">{{ $watcher['short'] }}</td><td class="v">{{ $permit->fire_watch_name ?: '-' }}</td>
                <td class="k">Jumlah Pekerja</td><td class="v">{{ $permit->worker_count }} orang</td>
            </tr>
            <tr>
                <td class="k">Alat yang Digunakan</td><td class="v" colspan="3">{{ $permit->tools ?: '-' }}</td>
            </tr>
            @if ($works)
                <tr>
                    <td class="k">Jenis Pekerjaan</td><td class="v" colspan="3">{{ implode(', ', $works) }}</td>
                </tr>
            @endif
            @if ($linked)
                <tr>
                    <td class="k">Permit Terkait</td><td class="v" colspan="3">{{ $linked->permit_number }} · {{ $linked->typeLabel() }} ({{ $linked->trashed() ? 'Dihapus' : $linked->statusLabel() }})</td>
                </tr>
            @endif
        </table>

        {{-- Waktu --}}
        <h2>Waktu Pemberlakuan</h2>
        <table class="info">
            <tr>
                <td class="k">{{ $singleDay ? 'Hari / Tanggal' : 'Tanggal' }}</td>
                <td class="v">
                    {{ $permit->start_date->translatedFormat('l, d M Y') }}
                    @unless ($singleDay) s/d {{ $permit->end_date->translatedFormat('l, d M Y') }} @endunless
                </td>
                <td class="k">Jam Kerja</td>
                <td class="v">{{ $fmtTime($permit->start_time) }} – {{ $fmtTime($permit->end_time) }}</td>
            </tr>
        </table>
        @php $maxDays = (int) $config['max_days']; @endphp
        <div class="days" style="grid-template-columns: repeat({{ max($maxDays, 1) }}, 1fr); {{ $maxDays === 1 ? 'max-width: 60mm;' : '' }}">
            @for ($d = 1; $d <= $maxDays; $d++)
                @php
                    $date = $permit->start_date->copy()->addDays($d - 1);
                    $inRange = $date->lte($permit->end_date);
                    $log = $dailyLogs->get($d);
                    $gasOfDay = $log ? $dailyGas->get($log->id) : null;
                @endphp
                <div class="day {{ $log ? 'on' : '' }}">
                    <div class="n">DAY {{ $d }}</div>
                    @if ($inRange)
                        <div class="d">{{ $date->translatedFormat('D, d/m') }}</div>
                        @if ($log)
                            <div class="ok">✓ {{ $log->created_at->format('H:i') }}</div>
                            <div class="by">{{ $log->user_name }}</div>
                            @if ($gasOfDay)
                                <div class="by">O₂ {{ $fmtGas($gasOfDay->o2) }}% · LEL {{ $fmtGas($gasOfDay->lel) }}%</div>
                            @endif
                        @else
                            <div class="d">–</div>
                        @endif
                    @else
                        <div class="d">tidak dipakai</div>
                    @endif
                </div>
            @endfor
        </div>

        {{-- Daftar pemeriksaan --}}
        <h2>{{ $config['texts']['checklist_title'] ?? 'Daftar Pemeriksaan Keselamatan' }}</h2>
        <table class="check">
            <thead>
                <tr><th>No</th><th>Butir Pemeriksaan</th><th style="text-align:center">Jawaban</th></tr>
            </thead>
            <tbody>
                @foreach ($config['checklist'] as $key => $item)
                    @php $check = $checks->get($key); @endphp
                    <tr>
                        <td class="no">{{ $loop->iteration }}</td>
                        <td>
                            {{ $check?->item_text ?? $item['text'] }}
                            @if ($check?->note) <span class="note">{{ $check->note }}</span> @endif
                            @if (($item['requires'] ?? null) === 'gas_test' && $check?->isYes() && $gasTest)
                                <div>
                                    @foreach ($gasLimits as $gas => $limit)
                                        @continue($gasTest->$gas === null)
                                        <span class="gas">{{ $limit['label'] }} {{ $fmtGas($gasTest->$gas) }} {{ $limit['unit'] }}</span>
                                    @endforeach
                                    <span class="note">Diukur {{ $gasTest->tested_by_name }} · {{ $gasTest->tested_at?->translatedFormat('d M Y H:i') }}</span>
                                </div>
                            @endif
                            @if (($item['requires'] ?? null) === 'linked_permit' && $check?->isYes())
                                <span class="note">Permit terkait: {{ $linked?->permit_number ?? ($permit->details['linked_permit_number'] ?? '-') }}</span>
                            @endif
                        </td>
                        <td class="ans">
                            @if ($check?->isYes()) <span class="yes">✓ YA</span>
                            @elseif ($check) <span class="na">TIDAK BERLAKU</span>
                            @else <span class="na">-</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                @foreach ($permit->extraNotes() as $note)
                    <tr>
                        <td class="no">+</td>
                        <td>{{ $note }}</td>
                        <td class="ans"><span class="yes">✓ YA</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Pengawasan --}}
        @if (! empty($config['supervision']))
            <h2>{{ $config['texts']['supervision_title'] ?? 'Pengawasan oleh Supervisi Pelaksana' }}</h2>
            <table class="check">
                @foreach ($config['supervision'] as $item)
                    @php $done = $stageDone[$item['stage']] ?? false; @endphp
                    <tr>
                        <td class="no">{{ $loop->iteration }}</td>
                        <td>{{ $item['text'] }}</td>
                        <td class="ans">@if ($done) <span class="yes">✓</span> @else <span class="na">–</span> @endif</td>
                    </tr>
                @endforeach
            </table>
        @endif

        {{-- Tanda tangan digital (tidak dipisah antar halaman) --}}
        <div class="keep">
        <h2>Persetujuan (Sebelum Pekerjaan Dilaksanakan)</h2>
        <div class="signs">
            <div class="sign">
                <div class="role">Dibuat oleh</div>
                <div class="seal">✓ DIAJUKAN</div>
                <div class="who">{{ $permit->applicant_name }}</div>
                <div class="meta">{{ $permit->applicantPositionLabel() }} {{ $permit->applicantDepartment?->name }} · {{ $permit->submitted_at?->translatedFormat('d M Y H:i') }}</div>
            </div>
            <div class="sign">
                <div class="role">Diperiksa oleh</div>
                <div class="seal {{ $checked ? '' : 'pending' }}">{{ $checked ? '✓ DISETUJUI DIGITAL' : 'BELUM' }}</div>
                <div class="who">{{ $checked?->user_name ?? '-' }}</div>
                <div class="meta">{{ $checked?->user_title }} · {{ $checked?->created_at->translatedFormat('d M Y H:i') }}</div>
            </div>
            <div class="sign">
                <div class="role">Disetujui oleh</div>
                <div class="seal {{ $approved ? '' : 'pending' }}">{{ $approved ? '✓ DISETUJUI DIGITAL' : 'BELUM' }}</div>
                <div class="who">{{ $approved?->user_name ?? '-' }}</div>
                <div class="meta">QHSE · {{ $approved?->created_at->translatedFormat('d M Y H:i') }}</div>
            </div>
        </div>
        @if ($approved?->note)
            <div class="alert info" style="margin-top:6px"><b>Catatan / syarat dari QHSE:</b> {{ $approved->note }}</div>
        @endif
        </div>

        <div class="foot">
            <div>
                Dicetak {{ now()->translatedFormat('d M Y, H:i') }} oleh {{ $printedBy }}.<br>
                Dokumen ini sah tanpa tanda tangan basah. Status terkini selalu mengikuti sistem {{ config('app.name') }}.
            </div>
            <div style="text-align:right">
                Distribusi: {{ implode(' · ', $config['distribution'] ?? []) }}
            </div>
        </div>
    </div>
</body>
</html>