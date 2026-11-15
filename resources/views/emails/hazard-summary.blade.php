{{--
    Email ringkasan Hazard Report (format tabel).
    Semua style ditulis inline karena banyak aplikasi email (Outlook, Gmail) mengabaikan <style>.
--}}
@php
    $th = 'border:1px solid #9ca3af;background:#f3f4f6;padding:8px 10px;font-size:13px;font-weight:bold;color:#111827;text-align:center;vertical-align:middle;';
    $td = 'border:1px solid #9ca3af;padding:8px 10px;font-size:13px;color:#1f2937;vertical-align:middle;';
    $link = 'color:#059669;text-decoration:none;font-weight:600;';

    $linkText = [
        'validation'   => 'Validasi Temuan',
        'verification' => 'Verifikasi Temuan',
        'follow_up'    => 'Tindak Lanjuti',
    ];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
</head>
<body style="margin:0;padding:0;background:#f9fafb;font-family:Arial,Helvetica,sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f9fafb;">
    <tr>
        <td align="center" style="padding:24px 12px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:1100px;background:#ffffff;border:1px solid #e5e7eb;">
                {{-- Kepala --}}
                <tr>
                    <td style="background:#059669;padding:14px 24px;color:#ffffff;font-size:13px;font-weight:bold;letter-spacing:.5px;">
                        {{ config('app.name') }} · Hazard Report
                    </td>
                </tr>
                <tr>
                    <td style="padding:24px 24px 8px;text-align:center;">
                        <div style="font-size:26px;font-weight:bold;color:#111827;">Hazard Report</div>
                        <div style="margin-top:8px;font-size:19px;font-weight:bold;color:#374151;">{{ $title }}</div>
                    </td>
                </tr>
                <tr>
                    <td style="padding:16px 24px 0;font-size:14px;color:#111827;">
                        <b>Dear Bapak/Ibu{{ $name ? ' ' . $name : '' }},</b>
                    </td>
                </tr>

                {{-- Tabel per bagian --}}
                @foreach ($sections as $section)
                    <tr>
                        <td style="padding:20px 24px 0;">
                            @if (! empty($section['heading']))
                                <div style="font-size:15px;font-weight:bold;color:#111827;margin-bottom:6px;">{{ $section['heading'] }}</div>
                            @endif
                            @if (! empty($section['intro']))
                                <p style="margin:0 0 12px;font-size:14px;line-height:1.5;color:#374151;">{{ $section['intro'] }}</p>
                            @endif

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                                <tr>
                                    <th style="{{ $th }}">No</th>
                                    <th style="{{ $th }}">No Hazard Report</th>
                                    <th style="{{ $th }}">Tanggal Temuan</th>
                                    <th style="{{ $th }}">Perusahaan Pelapor</th>
                                    <th style="{{ $th }}">Kategori</th>
                                    <th style="{{ $th }}">Prioritas</th>
                                    <th style="{{ $th }}">Lokasi</th>
                                    <th style="{{ $th }}">Deskripsi</th>
                                    @if ($section['mode'] === 'validation')
                                        <th style="{{ $th }}">Masuk</th>
                                    @else
                                        <th style="{{ $th }}">PIC / Batas Waktu</th>
                                    @endif
                                    <th style="{{ $th }}">Tautan</th>
                                </tr>

                                @foreach ($section['reports'] as $i => $r)
                                    @php $url = route('hazard-report.show', $r); @endphp
                                    <tr>
                                        <td style="{{ $td }}text-align:center;">{{ $i + 1 }}</td>
                                        <td style="{{ $td }}white-space:nowrap;"><a href="{{ $url }}" style="{{ $link }}">{{ $r->report_number }}</a></td>
                                        <td style="{{ $td }}white-space:nowrap;">{{ $r->finding_date->translatedFormat('d M Y') }}</td>
                                        <td style="{{ $td }}">{{ $r->reporterCompany?->name ?? '-' }}</td>
                                        <td style="{{ $td }}">{{ $r->category?->name ?? '-' }}</td>
                                        <td style="{{ $td }}white-space:nowrap;font-weight:bold;color:{{ $r->priority?->markerColor() ?? '#374151' }};">{{ $r->priority?->name ?? '-' }}</td>
                                        <td style="{{ $td }}">
                                            {{ $r->location }}
                                            @if ($r->hasCoordinates())
                                                <br><a href="https://www.google.com/maps?q={{ $r->latitude }},{{ $r->longitude }}" style="{{ $link }}font-size:12px;">Lihat peta</a>
                                            @endif
                                        </td>
                                        <td style="{{ $td }}">{{ \Illuminate\Support\Str::limit($r->description, 220) }}</td>
                                        @if ($section['mode'] === 'validation')
                                            <td style="{{ $td }}white-space:nowrap;{{ $r->created_at->lt(now()->subDay()) ? 'color:#dc2626;font-weight:bold;' : '' }}">
                                                {{ $r->created_at->translatedFormat('d M Y H:i') }}<br>
                                                <span style="font-size:12px;">{{ $r->created_at->diffForHumans() }}</span>
                                            </td>
                                        @else
                                            <td style="{{ $td }}">
                                                {{ $r->picDepartment?->name ?? '-' }}<br>
                                                @if ($r->due_date)
                                                    <span style="white-space:nowrap;font-size:12px;{{ $r->isOverdue() ? 'color:#dc2626;font-weight:bold;' : 'color:#6b7280;' }}">
                                                        {{ $r->due_date->translatedFormat('d M Y') }}
                                                        @if ($r->isOverdue()) (terlambat {{ (int) $r->due_date->diffInDays(today()) }} hari) @endif
                                                    </span>
                                                @endif
                                                @if ($r->extension_count > 0)
                                                    <br><span style="font-size:12px;color:#4f46e5;">Diperpanjang {{ $r->extension_count }}x</span>
                                                @endif
                                            </td>
                                        @endif
                                        <td style="{{ $td }}text-align:center;"><a href="{{ $url }}" style="{{ $link }}">{{ $linkText[$section['mode']] ?? 'Lihat' }}</a></td>
                                    </tr>
                                @endforeach
                            </table>

                            @if (($section['more'] ?? 0) > 0)
                                <p style="margin:8px 0 0;font-size:13px;color:#6b7280;">… dan {{ $section['more'] }} laporan lainnya. Buka Tasklist untuk melihat semuanya.</p>
                            @endif
                        </td>
                    </tr>
                @endforeach

                {{-- Tombol & penutup --}}
                @if ($actionUrl)
                    <tr>
                        <td align="center" style="padding:24px;">
                            <a href="{{ $actionUrl }}" style="display:inline-block;background:#059669;color:#ffffff;text-decoration:none;font-size:14px;font-weight:bold;padding:12px 24px;border-radius:6px;">Buka Tasklist Hazard Report</a>
                        </td>
                    </tr>
                @endif

                <tr>
                    <td style="padding:0 24px 24px;font-size:14px;line-height:1.6;color:#374151;">
                        @foreach ($footer as $line)
                            <p style="margin:0 0 8px;">{!! nl2br(e($line)) !!}</p>
                        @endforeach
                        <p style="margin:16px 0 0;">Terima kasih,<br><b>Tim QHSE · {{ config('app.name') }}</b></p>
                    </td>
                </tr>
                <tr>
                    <td style="border-top:1px solid #e5e7eb;padding:12px 24px;font-size:11px;color:#9ca3af;text-align:center;">
                        Email ini dikirim otomatis oleh sistem {{ config('app.name') }}. Mohon tidak membalas email ini.
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>