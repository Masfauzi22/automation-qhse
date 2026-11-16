{{--
    Sertifikat Penyelesaian Permit to Work (A4 landscape, siap cetak).
    Tombol "Cetak / Simpan PDF" memakai dialog cetak browser → pilih "Save as PDF".
--}}
@php
    $cert    = $certificate;
    $fmtTime = fn ($t) => $t ? substr((string) $t, 0, 5) : '-';
    $issuer  = config('permits.certificate.issuer', 'HSE Department');
    $site    = config('permits.certificate.site', '');
    $works   = implode(', ', $permit->workTypeLabels()) ?: $config['label'];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sertifikat {{ $cert->certificate_number }}</title>
    <style>
        @page { size: A4 landscape; margin: 0; }
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; }
        body { background: #e5e7eb; font-family: "Segoe UI", Arial, Helvetica, sans-serif; color: #111827; -webkit-print-color-adjust: exact; print-color-adjust: exact; }

        .toolbar { position: sticky; top: 0; z-index: 10; display: flex; gap: 8px; justify-content: center; align-items: center; padding: 12px; background: #111827; }
        .toolbar a, .toolbar button { font: 600 14px/1 inherit; font-family: inherit; border: 0; border-radius: 8px; padding: 10px 16px; cursor: pointer; text-decoration: none; }
        .btn-print { background: #059669; color: #fff; }
        .btn-back { background: #374151; color: #f9fafb; }
        .toolbar span { color: #9ca3af; font-size: 12px; margin-left: 8px; }

        .sheet { width: 297mm; height: 210mm; margin: 16px auto; background: #fff; position: relative; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,.15); }
        .frame { position: absolute; inset: 8mm; border: 2.5px solid #065f46; }
        .frame::after { content: ""; position: absolute; inset: 2.2mm; border: 1px solid #a7f3d0; }
        .content { position: absolute; inset: 14mm 16mm 12mm; display: flex; flex-direction: column; }

        .head { display: flex; justify-content: space-between; align-items: flex-start; }
        .brand { display: flex; align-items: center; gap: 10px; }
        .brand .logo { width: 40px; height: 40px; border-radius: 10px; background: #059669; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 13px; letter-spacing: .5px; }
        .brand .name { font-weight: 800; font-size: 15px; letter-spacing: .5px; }
        .brand .sub { font-size: 10.5px; color: #6b7280; }
        .numbers { text-align: right; font-size: 11px; color: #374151; line-height: 1.6; }
        .numbers b { font-size: 12.5px; color: #065f46; }

        .title { text-align: center; margin-top: 6mm; }
        .title .kicker { font-size: 11px; letter-spacing: 4px; color: #059669; font-weight: 700; text-transform: uppercase; }
        .title h1 { margin: 2px 0 0; font-family: Georgia, "Times New Roman", serif; font-size: 30px; letter-spacing: 1px; color: #064e3b; }
        .title .en { font-family: Georgia, serif; font-style: italic; color: #6b7280; font-size: 13px; margin-top: 2px; }
        .statement { text-align: center; font-size: 12.5px; color: #374151; margin: 5mm auto 0; max-width: 210mm; line-height: 1.55; }
        .project { text-align: center; font-family: Georgia, serif; font-size: 20px; font-weight: 700; margin-top: 3mm; color: #111827; }
        .location { text-align: center; font-size: 12px; color: #4b5563; margin-top: 1mm; }

        .grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 0; margin-top: 5mm; border: 1px solid #d1fae5; border-radius: 6px; overflow: hidden; }
        .cell { padding: 7px 10px; border-right: 1px solid #d1fae5; }
        .cell:last-child { border-right: 0; }
        .cell .k { font-size: 9px; letter-spacing: 1px; text-transform: uppercase; color: #6b7280; font-weight: 700; }
        .cell .v { font-size: 12px; font-weight: 600; margin-top: 2px; }

        .chips { display: flex; justify-content: center; gap: 8px; margin-top: 4mm; flex-wrap: wrap; }
        .chip { border-radius: 999px; padding: 4px 11px; font-size: 11px; font-weight: 700; border: 1px solid #a7f3d0; background: #ecfdf5; color: #065f46; }
        .chip.warn { border-color: #fecaca; background: #fef2f2; color: #b91c1c; }

        .boxes { display: grid; grid-template-columns: 1fr 1fr; gap: 6mm; margin-top: 6mm; }
        .box { border: 1px solid #e5e7eb; border-radius: 6px; padding: 8px 12px; }
        .box h3 { margin: 0 0 4px; font-size: 9.5px; letter-spacing: 1px; text-transform: uppercase; color: #059669; }
        .box table { width: 100%; border-collapse: collapse; font-size: 11px; }
        .box td { padding: 2px 0; vertical-align: top; }
        .box td:first-child { color: #6b7280; width: 34%; }
        .box p { margin: 0; font-size: 11.5px; line-height: 1.5; color: #374151; }
        .foot { margin-top: auto; display: flex; align-items: flex-end; gap: 10mm; }
        .signs { flex: 1; display: grid; grid-template-columns: repeat(3, 1fr); gap: 8mm; }
        .sign { text-align: center; font-size: 11px; }
        .sign .role { color: #6b7280; font-weight: 600; }
        .sign .stamp { display: inline-block; margin: 6px 0 4px; border: 1.5px solid #059669; color: #059669; border-radius: 4px; padding: 2px 6px; font-size: 9px; font-weight: 800; letter-spacing: .5px; transform: rotate(-3deg); }
        .sign .who { border-top: 1px solid #9ca3af; padding-top: 4px; font-weight: 700; font-size: 12px; }
        .sign .meta { color: #6b7280; font-size: 10px; }

        .issued { text-align: center; font-size: 10px; color: #9ca3af; margin-top: 4mm; }
        .ribbon { position: absolute; top: 18mm; right: -14mm; transform: rotate(35deg); background: #b91c1c; color: #fff; font-size: 10px; font-weight: 800; letter-spacing: 1px; padding: 4px 18mm; }

        @media print {
            body { background: #fff; }
            .toolbar { display: none; }
            .sheet { margin: 0; box-shadow: none; }
        }
        @media screen and (max-width: 1150px) {
            .sheet { transform-origin: top center; transform: scale(calc(100vw / 1180px)); }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button type="button" class="btn-print" onclick="window.print()">Cetak / Simpan PDF</button>
        <a class="btn-back" href="{{ url()->previous() !== url()->current() ? url()->previous() : route('my-permit.index') }}">Kembali</a>
        <span>Di dialog cetak pilih tujuan "Save as PDF", layout Landscape.</span>
    </div>

    <div class="sheet">
        <div class="frame"></div>
        @unless ($cert->incident_free)
            <div class="ribbon">DENGAN CATATAN</div>
        @endunless

        <div class="content">
            <div class="head">
                <div class="brand">
                    <div class="logo">HSE</div>
                    <div>
                        <div class="name">{{ config('app.name') }}</div>
                        <div class="sub">{{ $issuer }}{{ $site ? ' · ' . $site : '' }}</div>
                    </div>
                </div>
                <div class="numbers">
                    No. Sertifikat <b>{{ $cert->certificate_number }}</b><br>
                    No. Permit {{ $permit->permit_number }}
                </div>
            </div>

            <div class="title">
                <div class="kicker">Permit to Work · {{ $config['label'] }}</div>
                <h1>SERTIFIKAT PENYELESAIAN PEKERJAAN</h1>
                <div class="en">Certificate of Completion</div>
            </div>

            <p class="statement">
                Dengan ini menyatakan bahwa pekerjaan berikut telah <b>selesai dilaksanakan</b>, area kerja telah diperiksa
                setelah pekerjaan, dan {{ $config['title'] }} telah <b>ditutup dalam kondisi aman</b>.
            </p>

            <div class="project">{{ $permit->project_name }}</div>
            <div class="location">{{ $permit->location }} · Area {{ $permit->ownerDepartment?->name }}</div>

            <div class="grid">
                <div class="cell"><div class="k">Dibuat oleh</div><div class="v">{{ $permit->applicant_name }}</div><div class="k" style="text-transform:none;letter-spacing:0;font-weight:500">{{ $permit->applicantPositionLabel() }} {{ $permit->applicantDepartment?->name }}</div></div>
                <div class="cell"><div class="k">Jenis Pekerjaan</div><div class="v">{{ $works }}</div></div>
                <div class="cell"><div class="k">Periode Izin</div><div class="v">{{ $permit->start_date->translatedFormat('d M') }} – {{ $permit->end_date->translatedFormat('d M Y') }}</div><div class="k" style="text-transform:none;letter-spacing:0;font-weight:500">Jam {{ $fmtTime($permit->start_time) }}–{{ $fmtTime($permit->end_time) }}</div></div>
                <div class="cell"><div class="k">Pekerjaan Selesai</div><div class="v">{{ $permit->work_finished_at?->translatedFormat('d M Y, H:i') ?? '-' }}</div></div>
            </div>

            <div class="chips">
                <span class="chip">✓ {{ $activeDays }} hari kerja diaktifkan</span>
                @if ($watchMinutes)
                    <span class="chip">✓ Pengawasan pasca pekerjaan {{ $watchMinutes }} menit</span>
                @endif
                <span class="chip {{ $stopCount ? 'warn' : '' }}">{{ $stopCount ? '⚠ ' . $stopCount . 'x Stop Work' : '✓ Tanpa Stop Work' }}</span>
                <span class="chip {{ $cert->incident_free ? '' : 'warn' }}">{{ $cert->incident_free ? '✓ Tanpa insiden / near miss' : '⚠ Ada insiden / near miss tercatat' }}</span>
            </div>
            @if (! $cert->incident_free && $cert->incident_note)
                <p class="statement" style="margin-top:2mm;font-size:11px;color:#b91c1c">Catatan: {{ $cert->incident_note }}</p>
            @endif

            <div class="boxes">
                <div class="box">
                    <h3>Riwayat Izin</h3>
                    <table>
                        <tr><td>Diajukan</td><td>{{ $permit->submitted_at?->translatedFormat('d M Y, H:i') }}</td></tr>
                        <tr><td>Diperiksa atasan</td><td>{{ $openChecked?->user_name ?? '-' }} · {{ $openChecked?->created_at->translatedFormat('d M Y, H:i') }}</td></tr>
                        <tr><td>Disetujui QHSE</td><td>{{ $openApproved?->user_name ?? '-' }} · {{ $openApproved?->created_at->translatedFormat('d M Y, H:i') }}</td></tr>
                        <tr><td>{{ $permit->watcher()['short'] }}</td><td>{{ $permit->fire_watch_name }} · {{ $permit->worker_count }} pekerja</td></tr>
                    </table>
                </div>
                <div class="box">
                    <h3>Kondisi Akhir Area</h3>
                    <p>{{ $permit->final_condition }}</p>
                    <p style="margin-top:4px;color:#6b7280;font-size:10.5px">
                        Pekerjaan selesai {{ $permit->work_finished_at?->translatedFormat('H:i') }}@if ($watchMinutes),
                        pengawasan sampai {{ $permit->post_watch_until?->translatedFormat('H:i') }}@endif.
                    </p>
                </div>
            </div>

            <div class="foot">
                <div class="signs">
                    <div class="sign">
                        <div class="role">Dibuat oleh</div>
                        <div class="stamp">✓ DIAJUKAN</div>
                        <div class="who">{{ $permit->applicant_name }}</div>
                        <div class="meta">{{ $permit->applicantPositionLabel() }} {{ $permit->applicantDepartment?->name }}</div>
                    </div>
                    <div class="sign">
                        <div class="role">Diperiksa oleh</div>
                        <div class="stamp">✓ DISETUJUI DIGITAL</div>
                        <div class="who">{{ $closeChecked?->user_name ?? '-' }}</div>
                        <div class="meta">{{ $closeChecked?->user_title }} · {{ $closeChecked?->created_at->translatedFormat('d M Y') }}</div>
                    </div>
                    <div class="sign">
                        <div class="role">Disetujui oleh</div>
                        <div class="stamp">✓ DISETUJUI DIGITAL</div>
                        <div class="who">{{ $closeApproved?->user_name ?? $cert->issued_by_name }}</div>
                        <div class="meta">QHSE · {{ $cert->issued_at->translatedFormat('d M Y') }}</div>
                    </div>
                </div>
            </div>

            <div class="issued">
                Diterbitkan {{ $cert->issued_at->translatedFormat('d F Y, H:i') }} oleh sistem {{ config('app.name') }} ·
                {{ $config['form_number'] }} Rev. {{ $config['form_rev'] }}
            </div>
        </div>
    </div>
</body>
</html>