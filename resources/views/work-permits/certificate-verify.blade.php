{{-- Halaman publik hasil scan QR sertifikat: menampilkan keaslian & ringkasan --}}
@php
    $cert = $certificate;
    $rows = [
        'No. Sertifikat'    => $cert->certificate_number,
        'No. Permit'        => $permit->permit_number,
        'Jenis'             => implode(' · ', array_filter([$config['label'], implode(', ', $permit->workTypeLabels())])),
        'Pekerjaan'         => $permit->project_name,
        'Lokasi'            => $permit->location,
        'Pengaju'           => $permit->applicant_name . ' (' . $permit->applicantPositionLabel() . ' ' . ($permit->applicantDepartment?->name ?? '') . ')',
        'Periode Izin'      => $permit->start_date->translatedFormat('d M Y') . ' – ' . $permit->end_date->translatedFormat('d M Y'),
        'Pekerjaan Selesai' => $permit->work_finished_at?->translatedFormat('d M Y, H:i') ?? '-',
        'Diperiksa'         => $closeChecked ? $closeChecked->user_name . ' · ' . $closeChecked->user_title : '-',
        'Disetujui (QHSE)'  => ($closeApproved?->user_name ?? $cert->issued_by_name),
        'Diterbitkan'       => $cert->issued_at->translatedFormat('d F Y, H:i'),
    ];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>Verifikasi {{ $cert->certificate_number }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #f3f4f6; font-family: "Segoe UI", Arial, Helvetica, sans-serif; color: #111827; }
        .wrap { max-width: 560px; margin: 0 auto; padding: 24px 16px; }
        .card { background: #fff; border-radius: 14px; box-shadow: 0 4px 16px rgba(0,0,0,.06); overflow: hidden; }
        .top { padding: 22px 20px; text-align: center; color: #fff; background: #059669; }
        .top.warn { background: #b45309; }
        .icon { width: 52px; height: 52px; border-radius: 999px; background: rgba(255,255,255,.2); display: flex; align-items: center; justify-content: center; margin: 0 auto 10px; font-size: 28px; font-weight: 800; }
        .top h1 { margin: 0; font-size: 20px; }
        .top p { margin: 4px 0 0; font-size: 13px; opacity: .9; }
        dl { margin: 0; padding: 8px 20px 16px; }
        .row { display: flex; gap: 12px; padding: 10px 0; border-bottom: 1px solid #f3f4f6; font-size: 14px; }
        .row:last-child { border-bottom: 0; }
        dt { width: 38%; flex-shrink: 0; color: #6b7280; }
        dd { margin: 0; font-weight: 600; word-break: break-word; }
        .note { margin: 0 20px 16px; padding: 10px 12px; border-radius: 8px; background: #fef3c7; color: #92400e; font-size: 13px; }
        .foot { text-align: center; font-size: 12px; color: #9ca3af; margin-top: 16px; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="card">
            <div class="top {{ $cert->incident_free ? '' : 'warn' }}">
                <div class="icon">✓</div>
                <h1>Sertifikat Valid</h1>
                <p>{{ $cert->incident_free ? 'Pekerjaan selesai & permit ditutup dalam kondisi aman.' : 'Permit ditutup, dengan catatan insiden / near miss.' }}</p>
            </div>
            <dl>
                @foreach ($rows as $label => $value)
                    <div class="row"><dt>{{ $label }}</dt><dd>{{ $value }}</dd></div>
                @endforeach
            </dl>
            @if (! $cert->incident_free && $cert->incident_note)
                <div class="note"><b>Catatan insiden:</b> {{ $cert->incident_note }}</div>
            @endif
        </div>
        <p class="foot">Diverifikasi oleh {{ config('app.name') }} · {{ now()->translatedFormat('d M Y, H:i') }}</p>
    </div>
</body>
</html>