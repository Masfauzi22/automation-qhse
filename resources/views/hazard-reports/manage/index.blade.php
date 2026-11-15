@php
    $total = $counts->sum();
    $followUpCount = ($counts['open'] ?? 0) + ($counts['in_progress'] ?? 0);
    $inProgressCount = $counts['in_progress'] ?? 0;
    $input = 'rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500';

    // Kartu ringkasan: HSE dan PIC melihat angka yang relevan untuk masing-masing
    $cards = $isHse
        ? [
            ['status' => 'pending_validation',   'label' => 'Menunggu Validasi',    'value' => $counts['pending_validation'] ?? 0,   'sub' => 'perlu dicek HSE',            'box' => 'border-gray-100 bg-white',       'text' => 'text-gray-500',    'num' => 'text-gray-900'],
            ['status' => 'follow_up',            'label' => 'Follow Up PIC',        'value' => $followUpCount,                       'sub' => $inProgressCount . ' diperpanjang · ' . $overdueCount . ' lewat batas', 'box' => 'border-amber-100 bg-amber-50', 'text' => 'text-amber-700', 'num' => 'text-amber-800', 'alert' => $overdueCount > 0],
            ['status' => 'pending_verification', 'label' => 'Menunggu Verifikasi',  'value' => $counts['pending_verification'] ?? 0, 'sub' => 'bukti closing dari PIC',     'box' => 'border-blue-100 bg-blue-50',     'text' => 'text-blue-700',    'num' => 'text-blue-800'],
            ['status' => 'closed',               'label' => 'Closed',               'value' => $counts['closed'] ?? 0,               'sub' => 'dari ' . $total . ' laporan', 'box' => 'border-emerald-100 bg-emerald-50', 'text' => 'text-emerald-700', 'num' => 'text-emerald-800'],
          ]
        : [
            ['status' => 'follow_up',            'label' => 'Perlu Follow Up',      'value' => $followUpCount,                       'sub' => $inProgressCount . ' sedang diperpanjang',     'box' => 'border-amber-100 bg-amber-50',   'text' => 'text-amber-700',   'num' => 'text-amber-800'],
            ['status' => 'follow_up',            'label' => 'Lewat Batas Waktu',    'value' => $overdueCount,                        'sub' => 'segera ditindaklanjuti',     'box' => 'border-red-100 bg-red-50',       'text' => 'text-red-700',     'num' => 'text-red-700'],
            ['status' => 'pending_verification', 'label' => 'Menunggu Verifikasi',  'value' => $counts['pending_verification'] ?? 0, 'sub' => 'sedang dicek HSE',           'box' => 'border-blue-100 bg-blue-50',     'text' => 'text-blue-700',    'num' => 'text-blue-800'],
            ['status' => 'closed',               'label' => 'Closed',               'value' => $counts['closed'] ?? 0,               'sub' => 'dari ' . $total . ' laporan', 'box' => 'border-emerald-100 bg-emerald-50', 'text' => 'text-emerald-700', 'num' => 'text-emerald-800'],
          ];

    // Teks tombol aksi per baris
    $actionFor = function ($report) use ($isHse) {
        return match (true) {
            $isHse && $report->status === 'pending_validation'   => ['Validasi', true],
            $isHse && $report->status === 'pending_verification' => ['Verifikasi', true],
            ! $isHse && in_array($report->status, ['open', 'in_progress'], true) => ['Follow Up', true],
            default                                              => ['Lihat', false],
        };
    };
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tasklist Hazard Report</h2>
                <p class="text-xs text-gray-500">
                    {{ $isHse ? 'Semua laporan hazard dari seluruh departemen.' : 'Laporan hazard untuk departemen ' . ($userDept->name ?? '-') . '.' }}
                </p>
            </div>
            @if ($isHse)
                <a href="{{ route('hazard-report.reporters') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50">
                    <svg class="h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-5-3.87M9 20H2v-2a4 4 0 015-3.87m6-4.13a4 4 0 11-8 0 4 4 0 018 0zm6 2a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Rekap Pelapor
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (! $isHse && ! $userDept)
                <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    Akun kamu belum punya departemen, jadi belum ada laporan yang bisa ditampilkan. Minta admin mengisi departemen di akunmu.
                </div>
            @endif

            <!-- Ringkasan -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach ($cards as $card)
                    <a href="{{ route('hazard-report.index', ['status' => $card['status']]) }}" class="rounded-xl border p-4 shadow-sm transition hover:shadow-md {{ $card['box'] }}">
                        <p class="text-xs font-semibold uppercase tracking-wider {{ $card['text'] }}">{{ $card['label'] }}</p>
                        <p class="mt-1 text-3xl font-bold {{ $card['num'] }}">{{ $card['value'] }}</p>
                        <p class="text-xs {{ ($card['alert'] ?? false) ? 'font-semibold text-red-600' : $card['text'] }}">{{ $card['sub'] }}</p>
                    </a>
                @endforeach
            </div>

            <div class="bg-white rounded-xl border border-gray-100 shadow-sm">

                <!-- Tab status -->
                <div class="flex gap-1 overflow-x-auto border-b border-gray-100 px-4 pt-3">
                    @foreach ($tabs as $key => $label)
                        @php $count = $key === 'all' ? $total : ($counts[$key] ?? 0); @endphp
                        <a href="{{ route('hazard-report.index', array_filter(['status' => $key, 'q' => $search, 'department' => $deptId, 'priority' => $priorityId])) }}"
                           class="whitespace-nowrap border-b-2 px-3 pb-3 text-sm font-semibold transition {{ $status === $key ? 'border-emerald-600 text-emerald-700' : 'border-transparent text-gray-500 hover:text-gray-800' }}">
                            {{ $label }}
                            <span class="ml-1 rounded-full px-1.5 py-0.5 text-[11px] {{ $status === $key ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500' }}">{{ $count }}</span>
                        </a>
                    @endforeach
                </div>

                <!-- Filter -->
                <form method="GET" action="{{ route('hazard-report.index') }}" class="flex flex-col md:flex-row gap-3 px-4 py-4 border-b border-gray-100">
                    <input type="hidden" name="status" value="{{ $status }}">
                    <input type="text" name="q" value="{{ $search }}" placeholder="Cari no. laporan, lokasi, atau pelapor..." class="{{ $input }} md:flex-1">
                    @if ($isHse)
                        <select name="department" class="{{ $input }} md:w-52">
                            <option value="">Semua departemen PIC</option>
                            @foreach ($departments as $dept)
                                <option value="{{ $dept->id }}" @selected($deptId == $dept->id)>{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    @endif
                    <select name="priority" class="{{ $input }} md:w-44">
                        <option value="">Semua prioritas</option>
                        @foreach ($priorities as $priority)
                            <option value="{{ $priority->id }}" @selected($priorityId == $priority->id)>{{ $priority->name }}</option>
                        @endforeach
                    </select>
                    <div class="flex gap-2">
                        <button type="submit" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">Filter</button>
                        @if ($search || $deptId || $priorityId)
                            <a href="{{ route('hazard-report.index', ['status' => $status]) }}" class="rounded-lg px-3 py-2 text-sm font-medium text-gray-500 hover:bg-gray-100">Reset</a>
                        @endif
                    </div>
                </form>

                <!-- Tabel -->
                @if ($reports->isEmpty())
                    <div class="px-6 py-16 text-center">
                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-emerald-50">
                            <svg class="h-7 w-7 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <h4 class="mt-4 text-sm font-semibold text-gray-900">Tidak ada laporan di sini</h4>
                        <p class="mt-1 text-sm text-gray-500">
                            @if ($status === 'pending_validation') Semua laporan sudah divalidasi.
                            @elseif (in_array($status, ['open', 'in_progress', 'follow_up'], true) && ! $isHse) Tidak ada temuan yang perlu di-follow up departemenmu.
                            @else Coba ganti tab atau filter.
                            @endif
                        </p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100 text-sm">
                            <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wider text-gray-500">
                                <tr>
                                    <th class="px-5 py-3 text-left">No. Laporan</th>
                                    <th class="px-5 py-3 text-left">Pelapor</th>
                                    <th class="px-5 py-3 text-left">Temuan</th>
                                    @if ($isHse) <th class="px-5 py-3 text-left">PIC</th> @endif
                                    <th class="px-5 py-3 text-left">Prioritas</th>
                                    <th class="px-5 py-3 text-left">Status</th>
                                    <th class="px-5 py-3 text-left">{{ $status === 'pending_validation' ? 'Menunggu' : 'Batas Waktu' }}</th>
                                    <th class="px-5 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($reports as $report)
                                    @php [$actionLabel, $actionPrimary] = $actionFor($report); @endphp
                                    <tr class="hover:bg-gray-50 align-top">
                                        <td class="px-5 py-3 whitespace-nowrap">
                                            <p class="font-semibold text-gray-900">{{ $report->report_number }}</p>
                                            <p class="text-xs text-gray-500">Temuan {{ $report->finding_date->translatedFormat('d M Y') }}</p>
                                        </td>
                                        <td class="px-5 py-3 whitespace-nowrap">
                                            <p class="font-medium text-gray-800">{{ $report->reporter_name }}</p>
                                            <p class="text-xs text-gray-500">{{ $report->reporterDepartment?->name ?? '-' }}</p>
                                        </td>
                                        <td class="px-5 py-3 max-w-xs">
                                            <p class="font-medium text-gray-800">{{ $report->category->name }}</p>
                                            <p class="text-xs text-gray-500 truncate" title="{{ $report->location }}">{{ $report->location }}</p>
                                        </td>
                                        @if ($isHse)
                                            <td class="px-5 py-3 whitespace-nowrap text-gray-700">{{ $report->picDepartment->name }}</td>
                                        @endif
                                        <td class="px-5 py-3 whitespace-nowrap">
                                            <span class="inline-flex rounded-full border px-2 py-0.5 text-xs font-semibold {{ $report->priority->badgeClass() }}">{{ $report->priority->name }}</span>
                                        </td>
                                        <td class="px-5 py-3 whitespace-nowrap">
                                            <span class="inline-flex rounded-full border px-2 py-0.5 text-xs font-semibold {{ $report->statusClass() }}">{{ $report->statusLabel() }}</span>
                                        </td>
                                        <td class="px-5 py-3 whitespace-nowrap text-xs">
                                            @if ($report->status === 'pending_validation')
                                                <span class="{{ $report->created_at->lt(now()->subDay()) ? 'font-semibold text-red-600' : 'text-gray-600' }}">{{ $report->created_at->diffForHumans() }}</span>
                                            @elseif ($report->due_date)
                                                <span class="{{ $report->isOverdue() ? 'font-semibold text-red-600' : 'text-gray-600' }}">
                                                    {{ $report->due_date->translatedFormat('d M Y') }}
                                                    @if ($report->isOverdue()) <span class="block">Terlambat</span> @endif
                                                    @if ($report->extension_count > 0)
                                                        <span class="mt-0.5 block font-semibold text-indigo-600">Diperpanjang {{ $report->extension_count }}x</span>
                                                    @endif
                                                </span>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-3 text-right whitespace-nowrap">
                                            <a href="{{ route('hazard-report.show', $report) }}"
                                               class="inline-flex items-center rounded-lg px-3 py-1.5 text-sm font-semibold {{ $actionPrimary ? 'bg-emerald-600 text-white hover:bg-emerald-700' : 'text-emerald-700 hover:bg-emerald-50' }}">
                                                {{ $actionLabel }}
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if ($reports->hasPages())
                        <div class="border-t border-gray-100 px-5 py-3">{{ $reports->links() }}</div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</x-app-layout>