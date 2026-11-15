{{--
    Komponen detail Hazard Report, dipakai bersama oleh:
    - Personal Record (pelapor)  → <x-hazard-report.detail :report="$report" />
    - Tasklist (HSE / PIC)       → <x-hazard-report.detail :report="$report"> <x-slot:actions>...</x-slot:actions> </x-hazard-report.detail>
    Slot "actions" tampil paling atas di kolom kanan (untuk form validasi, closing, dll).
    Prop opsional :reporter-stats → rekam jejak pelapor (khusus HSE).
--}}
@props(['report', 'reporterStats' => null])

@php
    $isRejected = $report->status === \App\Models\HazardReport::STATUS_REJECTED;
    // HSE boleh membuka laporan lain (link ke laporan asli / laporan duplikat)
    $canOpenOthers = auth()->user()?->hasAnyRole(['superadmin', 'qhse']) ?? false;

    $timeline = [
        [
            'title' => 'Laporan dikirim',
            'by'    => $report->reporter_name,
            'at'    => $report->created_at,
            'done'  => true,
        ],
        [
            'title' => $isRejected ? 'Ditolak HSE' : 'Divalidasi HSE',
            'by'    => $report->validator?->name,
            'at'    => $report->validated_at,
            'done'  => (bool) $report->validated_at,
            'note'  => $isRejected ? $report->rejectionReasonLabel() : null,
            'red'   => $isRejected,
        ],
    ];

    // Update progres / perpanjangan dari PIC (dan penolakannya oleh HSE)
    $updates = $report->updates ?? collect();
    foreach ($updates as $u) {
        $timeline[] = $u->isExtension()
            ? [
                'title' => 'Perpanjangan s.d. ' . $u->new_due_date->translatedFormat('d M Y'),
                'by'    => $u->user?->name,
                'at'    => $u->created_at,
                'done'  => true,
                'note'  => null,
                'blue'  => true,
            ]
            : [
                'title' => 'Perpanjangan ditolak HSE',
                'by'    => $u->user?->name,
                'at'    => $u->created_at,
                'done'  => true,
                'note'  => $u->note,
                'red'   => true,
            ];
    }

    if (! $isRejected) {
        $timeline[] = [
            'title' => 'Bukti perbaikan dikirim',
            'by'    => $report->closingSubmitter?->name,
            'at'    => $report->closing_submitted_at,
            'done'  => (bool) $report->closing_submitted_at,
        ];
        $timeline[] = [
            'title' => 'Closed',
            'by'    => $report->closer?->name,
            'at'    => $report->closed_at,
            'done'  => (bool) $report->closed_at,
        ];
    }
@endphp

<div class="space-y-6">

    <!-- Kepala laporan -->
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">No. Laporan</p>
            <h3 class="text-2xl font-bold text-gray-900">{{ $report->report_number }}</h3>
            <p class="mt-1 text-sm text-gray-500">Dikirim {{ $report->created_at->translatedFormat('d F Y, H:i') }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <span class="inline-flex rounded-full border px-3 py-1 text-sm font-semibold {{ $report->priority->badgeClass() }}">Prioritas {{ $report->priority->name }}</span>
            <span class="inline-flex rounded-full border px-3 py-1 text-sm font-semibold {{ $report->statusClass() }}">{{ $report->statusLabel() }}</span>
            @if ($report->isOverdue())
                <span class="inline-flex rounded-full bg-red-600 px-3 py-1 text-sm font-bold text-white">Terlambat</span>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Kolom kiri: isi laporan -->
        <div class="lg:col-span-2 space-y-6">

            <section class="bg-white rounded-xl border border-gray-100 shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4"><h4 class="text-sm font-bold text-gray-900">Detail Temuan</h4></div>
                <dl class="px-6 py-5 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">Tanggal Temuan</dt>
                        <dd class="mt-0.5 font-medium text-gray-900">{{ $report->finding_date->translatedFormat('l, d F Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">Kategori Bahaya</dt>
                        <dd class="mt-0.5 font-medium text-gray-900">{{ $report->category->name }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">Lokasi</dt>
                        <dd class="mt-0.5 font-medium text-gray-900">{{ $report->location }}</dd>
                        @if ($report->hasCoordinates())
                            <x-hazard-report.map class="mt-2" :lat="$report->latitude" :lng="$report->longitude" :accuracy="$report->location_accuracy"
                                :color="$report->priority->markerColor()" :speed="$report->priority->pulseSpeed()"
                                :pulse="! in_array($report->status, [\App\Models\HazardReport::STATUS_CLOSED, \App\Models\HazardReport::STATUS_REJECTED], true)"
                                :label="'Prioritas ' . $report->priority->name" />
                        @else
                            <p class="mt-0.5 text-xs italic text-gray-400">Titik lokasi tidak ditandai di peta.</p>
                        @endif
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">Deskripsi Temuan</dt>
                        <dd class="mt-0.5 text-gray-800 whitespace-pre-line">{{ $report->description }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">Potensi Bahaya</dt>
                        <dd class="mt-0.5 text-gray-800 whitespace-pre-line">{{ $report->potential_hazard }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">Rekomendasi Perbaikan</dt>
                        <dd class="mt-0.5 text-gray-800 whitespace-pre-line">{{ $report->recommendation }}</dd>
                    </div>
                </dl>
            </section>

            <!-- Foto temuan -->
            <section class="bg-white rounded-xl border border-gray-100 shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4"><h4 class="text-sm font-bold text-gray-900">Foto Bukti Temuan</h4></div>
                <div class="px-6 py-5 grid grid-cols-2 sm:grid-cols-3 gap-3">
                    @forelse ($report->findingPhotos as $photo)
                        <a href="{{ $photo->url() }}" target="_blank" class="group block aspect-square overflow-hidden rounded-lg border border-gray-200 bg-gray-100">
                            <img loading="lazy" src="{{ $photo->url() }}" alt="{{ $photo->original_name }}" class="h-full w-full object-cover transition group-hover:scale-105">
                        </a>
                    @empty
                        <p class="col-span-full text-sm text-gray-400">Tidak ada foto.</p>
                    @endforelse
                </div>
            </section>

            <!-- Progres tindak lanjut (perpanjangan) -->
            @if ($updates->isNotEmpty())
                <section class="bg-white rounded-xl border border-indigo-100 shadow-sm">
                    <div class="border-b border-indigo-100 bg-indigo-50/50 px-6 py-4 rounded-t-xl">
                        <h4 class="text-sm font-bold text-indigo-900">Progres Tindak Lanjut</h4>
                        <p class="text-xs text-indigo-700">Kendala dan tindakan sementara yang dilaporkan PIC.</p>
                    </div>
                    <ol class="divide-y divide-gray-100">
                        @foreach ($updates as $u)
                            <li class="px-6 py-4 space-y-2">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <p class="text-sm font-semibold {{ $u->isExtension() ? 'text-gray-900' : 'text-red-700' }}">
                                        @if ($u->isExtension())
                                            Perpanjangan: {{ $u->old_due_date?->translatedFormat('d M Y') ?? '-' }} &rarr; {{ $u->new_due_date->translatedFormat('d M Y') }}
                                        @else
                                            Perpanjangan ditolak HSE, batas kembali ke {{ $u->new_due_date->translatedFormat('d M Y') }}
                                        @endif
                                    </p>
                                    <p class="text-xs text-gray-500">{{ $u->created_at->translatedFormat('d M Y, H:i') }} · {{ $u->user?->name ?? '-' }}</p>
                                </div>

                                @if ($u->isExtension())
                                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                                        <div>
                                            <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">Kendala</dt>
                                            <dd class="mt-0.5 text-gray-800 whitespace-pre-line">{{ $u->obstacle }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">Tindakan Sementara</dt>
                                            <dd class="mt-0.5 text-gray-800 whitespace-pre-line">{{ $u->temporary_action }}</dd>
                                        </div>
                                    </dl>
                                    @if ($u->photos->isNotEmpty())
                                        <div class="grid grid-cols-3 sm:grid-cols-4 gap-2 pt-1">
                                            @foreach ($u->photos as $photo)
                                                <a href="{{ $photo->url() }}" target="_blank" class="group block aspect-square overflow-hidden rounded-lg border border-gray-200 bg-gray-100">
                                                    <img loading="lazy" src="{{ $photo->url() }}" alt="{{ $photo->original_name }}" class="h-full w-full object-cover transition group-hover:scale-105">
                                                </a>
                                            @endforeach
                                        </div>
                                    @endif
                                @else
                                    <p class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700 whitespace-pre-line">{{ $u->note }}</p>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                </section>
            @endif

            <!-- Bukti closing (kalau ada) -->
            @if ($report->closing_submitted_at)
                <section class="bg-white rounded-xl border border-emerald-100 shadow-sm">
                    <div class="border-b border-emerald-100 bg-emerald-50/50 px-6 py-4 rounded-t-xl">
                        <h4 class="text-sm font-bold text-emerald-900">Bukti Perbaikan / Closing</h4>
                        <p class="text-xs text-emerald-700">Dikirim oleh {{ $report->closingSubmitter?->name ?? '-' }} pada {{ $report->closing_submitted_at->translatedFormat('d F Y, H:i') }}</p>
                    </div>
                    <div class="px-6 py-5 space-y-4">
                        @if ($report->closing_note)
                            <p class="text-sm text-gray-800 whitespace-pre-line">{{ $report->closing_note }}</p>
                        @endif
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            @foreach ($report->closingPhotos as $photo)
                                <a href="{{ $photo->url() }}" target="_blank" class="group block aspect-square overflow-hidden rounded-lg border border-gray-200 bg-gray-100">
                                    <img loading="lazy" src="{{ $photo->url() }}" alt="{{ $photo->original_name }}" class="h-full w-full object-cover transition group-hover:scale-105">
                                </a>
                            @endforeach
                        </div>
                    </div>
                </section>
            @endif
        </div>

        <!-- Kolom kanan -->
        <div class="space-y-6">

            {{-- Bukti perbaikan pernah dikembalikan HSE --}}
            @if ($report->verification_note && in_array($report->status, \App\Models\HazardReport::FOLLOW_UP_STATUSES, true))
                <div class="rounded-xl border border-red-200 bg-red-50 px-5 py-4">
                    <p class="text-sm font-bold text-red-800">Bukti perbaikan dikembalikan HSE</p>
                    <p class="mt-1 text-sm text-red-700 whitespace-pre-line">{{ $report->verification_note }}</p>
                </div>
            @endif

            {{-- Laporan ditolak HSE --}}
            @if ($isRejected)
                <div class="rounded-xl border border-red-200 bg-red-50 px-5 py-4">
                    <p class="text-sm font-bold text-red-800">Ditolak: {{ $report->rejectionReasonLabel() }}</p>
                    @if ($report->duplicateOf)
                        <p class="mt-1 text-sm text-red-700">
                            Duplikat dari
                            @if ($canOpenOthers)
                                <a href="{{ route('hazard-report.show', $report->duplicateOf) }}" class="font-semibold underline hover:text-red-900">{{ $report->duplicateOf->report_number }}</a>
                            @else
                                <span class="font-semibold">{{ $report->duplicateOf->report_number }}</span>
                            @endif
                            <span class="text-red-600">({{ $report->duplicateOf->statusLabel() }})</span>
                        </p>
                        @unless ($canOpenOthers)
                            <p class="mt-1 text-xs text-red-600">Bahaya ini sudah dilaporkan sebelumnya dan sedang/sudah ditangani. Terima kasih sudah peduli!</p>
                        @endunless
                    @endif
                </div>
            @endif

            {{-- Panel aksi (validasi / closing), kalau diisi oleh halaman pemanggil --}}
            @isset($actions)
                {{ $actions }}
            @endisset

            @if ($canOpenOthers && $report->relationLoaded('duplicates') && $report->duplicates->isNotEmpty())
                <div class="rounded-xl border border-gray-200 bg-white px-5 py-4 shadow-sm">
                    <p class="text-sm font-bold text-gray-900">Dilaporkan juga oleh {{ $report->duplicates->count() }} orang lain</p>
                    <ul class="mt-2 space-y-1 text-sm">
                        @foreach ($report->duplicates as $dupe)
                            <li class="flex justify-between gap-2">
                                <a href="{{ route('hazard-report.show', $dupe) }}" class="font-medium text-emerald-700 hover:underline">{{ $dupe->report_number }}</a>
                                <span class="truncate text-gray-500">{{ $dupe->reporter_name }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <section class="bg-white rounded-xl border border-gray-100 shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4"><h4 class="text-sm font-bold text-gray-900">Pelapor</h4></div>
                <dl class="px-6 py-5 space-y-3 text-sm">
                    <div class="flex justify-between gap-4"><dt class="text-gray-500">Nama</dt><dd class="font-medium text-gray-900 text-right">{{ $report->reporter_name }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-gray-500">ID Karyawan</dt><dd class="font-medium text-gray-900 text-right">{{ $report->reporter_employee_number ?? '-' }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-gray-500">Perusahaan</dt><dd class="font-medium text-gray-900 text-right">{{ $report->reporterCompany?->name ?? '-' }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-gray-500">Departemen</dt><dd class="font-medium text-gray-900 text-right">{{ $report->reporterDepartment?->name ?? '-' }}</dd></div>
                </dl>
                {{-- Rekam jejak pelapor: hanya dikirim untuk HSE --}}
                @if ($reporterStats)
                    <x-hazard-report.reporter-stats :stats="$reporterStats" variant="hse" :reporter-name="$report->reporter_name" />
                @endif
            </section>

            <section class="bg-white rounded-xl border border-gray-100 shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4"><h4 class="text-sm font-bold text-gray-900">Follow Up</h4></div>
                <dl class="px-6 py-5 space-y-3 text-sm">
                    <div class="flex justify-between gap-4"><dt class="text-gray-500">Departemen PIC</dt><dd class="font-medium text-gray-900 text-right">{{ $report->picDepartment->name }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-gray-500">Batas Waktu</dt>
                        <dd class="font-medium text-right {{ $report->isOverdue() ? 'text-red-600' : 'text-gray-900' }}">
                            {{ $report->due_date ? $report->due_date->translatedFormat('d M Y') : ($isRejected ? '-' : 'Setelah divalidasi') }}
                        </dd>
                    </div>
                    @if ($report->isExtended())
                        <div class="flex justify-between gap-4"><dt class="text-gray-500">Batas Awal</dt><dd class="font-medium text-gray-500 text-right line-through">{{ $report->original_due_date?->translatedFormat('d M Y') ?? '-' }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-gray-500">Perpanjangan</dt><dd class="font-medium text-indigo-700 text-right">{{ $report->extension_count }} / {{ config('hazard.max_extensions', 2) }} kali</dd></div>
                    @endif
                </dl>
            </section>

            <!-- Riwayat -->
            <section class="bg-white rounded-xl border border-gray-100 shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4"><h4 class="text-sm font-bold text-gray-900">Riwayat</h4></div>
                <ol class="px-6 py-5">
                    @foreach ($timeline as $step)
                        @php
                            $dot = ! $step['done'] ? 'bg-white border-2 border-gray-300'
                                : (($step['red'] ?? false) ? 'bg-red-500' : (($step['blue'] ?? false) ? 'bg-indigo-500' : 'bg-emerald-500'));
                        @endphp
                        <li class="relative flex gap-3 {{ $loop->last ? '' : 'pb-5' }}">
                            @unless ($loop->last)
                                <span class="absolute left-[7px] top-4 h-full w-0.5 {{ $step['done'] ? 'bg-emerald-200' : 'bg-gray-200' }}"></span>
                            @endunless
                            <span class="relative mt-1 h-4 w-4 shrink-0 rounded-full {{ $dot }}"></span>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold {{ $step['done'] ? 'text-gray-900' : 'text-gray-400' }}">{{ $step['title'] }}</p>
                                @if ($step['done'])
                                    <p class="text-xs text-gray-500">{{ $step['at']?->translatedFormat('d M Y, H:i') }}@if ($step['by']) · {{ $step['by'] }}@endif</p>
                                    @if (! empty($step['note']))
                                        <p class="mt-1 text-xs font-medium text-red-600">Alasan: {{ \Illuminate\Support\Str::limit($step['note'], 120) }}</p>
                                    @endif
                                @else
                                    <p class="text-xs text-gray-400">Menunggu</p>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ol>
                @if ($report->validation_note)
                    <div class="mx-6 mb-3 rounded-lg bg-gray-50 px-4 py-3 text-xs text-gray-700">
                        <span class="font-semibold">Catatan validasi HSE:</span> {{ $report->validation_note }}
                    </div>
                @endif
                @if ($report->verification_note && $report->status === \App\Models\HazardReport::STATUS_CLOSED)
                    <div class="mx-6 mb-5 rounded-lg bg-gray-50 px-4 py-3 text-xs text-gray-700">
                        <span class="font-semibold">Catatan verifikasi HSE:</span> {{ $report->verification_note }}
                    </div>
                @endif
            </section>
        </div>
    </div>
</div>