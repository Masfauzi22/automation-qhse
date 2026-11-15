{{--
    Ringkasan jumlah laporan seorang pelapor.
    Data dari HazardReport::reporterStats($userId).

    - variant "self" → untuk pelapor sendiri (form Lapor Hazard & Hazard Report Saya)
    - variant "hse"  → rekam jejak pelapor, ditampilkan ke HSE saat validasi
--}}
@props(['stats', 'variant' => 'self', 'reporterName' => null])

@php
    $target  = $stats['target'];
    $counted = $stats['this_month_counted'];
    $percent = $target > 0 ? min(100, (int) round($counted / $target * 100)) : 0;
    $rate    = $stats['acceptance_rate'];
    $rateCls = $rate === null ? 'text-gray-400' : ($rate >= 80 ? 'text-emerald-700' : ($rate >= 50 ? 'text-amber-700' : 'text-red-600'));
@endphp

@if ($variant === 'hse')
    <div {{ $attributes->merge(['class' => 'border-t border-gray-100 px-6 py-4']) }}>
        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Rekam Jejak Pelapor</p>
        <div class="mt-2 grid grid-cols-3 gap-2 text-center">
            <div class="rounded-lg bg-gray-50 px-2 py-2">
                <p class="text-lg font-bold text-gray-900">{{ $stats['total'] }}</p>
                <p class="text-[11px] text-gray-500">Total laporan</p>
            </div>
            <div class="rounded-lg bg-gray-50 px-2 py-2">
                <p class="text-lg font-bold text-gray-900">{{ $stats['this_month'] }}</p>
                <p class="text-[11px] text-gray-500">Bulan ini</p>
            </div>
            <div class="rounded-lg bg-gray-50 px-2 py-2">
                <p class="text-lg font-bold {{ $rateCls }}">{{ $rate === null ? '-' : $rate . '%' }}</p>
                <p class="text-[11px] text-gray-500">Diterima</p>
            </div>
        </div>
        <p class="mt-2 text-xs text-gray-500">
            {{ $stats['valid'] }} diterima · {{ $stats['rejected'] }} ditolak · {{ $stats['pending'] }} menunggu validasi
        </p>
        @if ($stats['total'] <= 1)
            <p class="mt-1 text-xs font-medium text-indigo-600">Ini laporan pertama dari pelapor ini.</p>
        @endif
        @if ($reporterName && $stats['total'] > 1)
            <a href="{{ route('hazard-report.index', ['status' => 'all', 'q' => $reporterName]) }}"
               class="mt-2 inline-block text-xs font-semibold text-emerald-700 hover:text-emerald-900">Lihat laporan lain dari pelapor ini →</a>
        @endif
    </div>
@else
    <div {{ $attributes->merge(['class' => 'grid grid-cols-2 sm:grid-cols-4 gap-3']) }}>
        {{-- Bulan ini (+ target kalau ada) --}}
        <div class="rounded-xl border p-4 shadow-sm {{ $stats['target_met'] ? 'border-emerald-200 bg-emerald-50' : 'border-gray-100 bg-white' }}">
            <p class="text-xs font-semibold uppercase tracking-wider {{ $stats['target_met'] ? 'text-emerald-700' : 'text-gray-500' }}">Bulan Ini</p>
            @if ($target > 0)
                <p class="mt-1 text-2xl font-bold text-gray-900">{{ $counted }}<span class="text-base font-semibold text-gray-400"> / {{ $target }}</span></p>
                <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-gray-200">
                    <div class="h-full rounded-full {{ $stats['target_met'] ? 'bg-emerald-500' : 'bg-amber-400' }}" style="width: {{ $percent }}%"></div>
                </div>
                <p class="mt-1 text-xs {{ $stats['target_met'] ? 'font-semibold text-emerald-700' : 'text-gray-500' }}">
                    {{ $stats['target_met'] ? 'Target tercapai' : 'Kurang ' . ($target - $counted) . ' laporan' }}
                </p>
            @else
                <p class="mt-1 text-2xl font-bold text-gray-900">{{ $stats['this_month'] }}</p>
                <p class="text-xs text-gray-500">laporan dikirim</p>
            @endif
        </div>

        <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Total</p>
            <p class="mt-1 text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
            <p class="text-xs text-gray-500">sejak awal</p>
        </div>

        <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Diterima HSE</p>
            <p class="mt-1 text-2xl font-bold text-gray-900">{{ $stats['valid'] }}</p>
            <p class="text-xs {{ $rateCls }}">{{ $rate === null ? 'belum ada keputusan' : $rate . '% dari yang dicek' }}</p>
        </div>

        <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Closed</p>
            <p class="mt-1 text-2xl font-bold text-emerald-700">{{ $stats['closed'] }}</p>
            <p class="text-xs text-gray-500">sudah diperbaiki</p>
        </div>
    </div>
@endif