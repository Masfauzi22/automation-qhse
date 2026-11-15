@php
    $input = 'rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500';
    $filtered = $search || $deptId || $companyId || $onlyZero;
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('hazard-report.index') }}" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700" title="Kembali ke Tasklist">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Rekap Pelapor Hazard</h2>
                <p class="text-xs text-gray-500">Jumlah laporan yang dikirim tiap orang pada {{ $monthLabel }}.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Ringkasan -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Total Laporan</p>
                    <p class="mt-1 text-3xl font-bold text-gray-900">{{ $summary['reports'] }}</p>
                    <p class="text-xs text-gray-500">{{ $monthLabel }}</p>
                </div>
                <div class="rounded-xl border border-emerald-100 bg-emerald-50 p-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wider text-emerald-700">Orang Melapor</p>
                    <p class="mt-1 text-3xl font-bold text-emerald-800">{{ $summary['reporting'] }}</p>
                    <p class="text-xs text-emerald-700">dari {{ $summary['people'] }} orang</p>
                </div>
                <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Rata-rata</p>
                    <p class="mt-1 text-3xl font-bold text-gray-900">{{ $summary['average'] }}</p>
                    <p class="text-xs text-gray-500">laporan per orang</p>
                </div>
                @if ($target > 0)
                    <div class="rounded-xl border border-blue-100 bg-blue-50 p-4 shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-wider text-blue-700">Capai Target</p>
                        <p class="mt-1 text-3xl font-bold text-blue-800">{{ $summary['target_met'] }}</p>
                        <p class="text-xs text-blue-700">orang, target {{ $target }} laporan/bulan</p>
                    </div>
                @else
                    <div class="rounded-xl border border-amber-100 bg-amber-50 p-4 shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-wider text-amber-700">Belum Melapor</p>
                        <p class="mt-1 text-3xl font-bold text-amber-800">{{ $summary['people'] - $summary['reporting'] }}</p>
                        <p class="text-xs text-amber-700">orang di bulan ini</p>
                    </div>
                @endif
            </div>

            <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
                <!-- Filter -->
                <form method="GET" action="{{ route('hazard-report.reporters') }}" class="flex flex-col lg:flex-row gap-3 px-4 py-4 border-b border-gray-100">
                    <input type="month" name="month" value="{{ $month }}" max="{{ now()->format('Y-m') }}" class="{{ $input }} lg:w-44">
                    <input type="text" name="q" value="{{ $search }}" placeholder="Cari nama atau ID karyawan..." class="{{ $input }} lg:flex-1">
                    <select name="company" class="{{ $input }} lg:w-52">
                        <option value="">Semua perusahaan</option>
                        @foreach ($companies as $company)
                            <option value="{{ $company->id }}" @selected($companyId == $company->id)>{{ $company->name }}</option>
                        @endforeach
                    </select>
                    <select name="department" class="{{ $input }} lg:w-48">
                        <option value="">Semua departemen</option>
                        @foreach ($departments as $dept)
                            <option value="{{ $dept->id }}" @selected($deptId == $dept->id)>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                    <label class="inline-flex items-center gap-2 whitespace-nowrap text-sm text-gray-700">
                        <input type="checkbox" name="zero" value="1" @checked($onlyZero) class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                        Belum melapor saja
                    </label>
                    <div class="flex gap-2">
                        <button type="submit" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">Tampilkan</button>
                        @if ($filtered)
                            <a href="{{ route('hazard-report.reporters', ['month' => $month]) }}" class="rounded-lg px-3 py-2 text-sm font-medium text-gray-500 hover:bg-gray-100">Reset</a>
                        @endif
                    </div>
                </form>

                @if ($rows->isEmpty())
                    <div class="px-6 py-16 text-center text-sm text-gray-500">Tidak ada data untuk filter ini.</div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100 text-sm">
                            <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wider text-gray-500">
                                <tr>
                                    <th class="px-5 py-3 text-left">#</th>
                                    <th class="px-5 py-3 text-left">Nama</th>
                                    <th class="px-5 py-3 text-left">Perusahaan / Departemen</th>
                                    <th class="px-5 py-3 text-center">Laporan</th>
                                    <th class="px-5 py-3 text-center">Diterima</th>
                                    <th class="px-5 py-3 text-center">Ditolak</th>
                                    <th class="px-5 py-3 text-center">Menunggu</th>
                                    <th class="px-5 py-3 text-center">Closed</th>
                                    @if ($target > 0) <th class="px-5 py-3 text-center">Target</th> @endif
                                    <th class="px-5 py-3 text-center">Sepanjang Waktu</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($rows as $i => $row)
                                    <tr class="hover:bg-gray-50 {{ $row->total === 0 ? 'text-gray-400' : '' }}">
                                        <td class="px-5 py-3 whitespace-nowrap text-xs text-gray-400">{{ $i + 1 }}</td>
                                        <td class="px-5 py-3 whitespace-nowrap">
                                            <p class="font-semibold {{ $row->total === 0 ? 'text-gray-500' : 'text-gray-900' }}">{{ $row->user->name }}</p>
                                            <p class="text-xs text-gray-500">{{ $row->user->employee_number ?? '-' }}</p>
                                        </td>
                                        <td class="px-5 py-3 whitespace-nowrap">
                                            <p class="text-gray-700">{{ $row->user->company?->name ?? '-' }}</p>
                                            <p class="text-xs text-gray-500">{{ $row->user->department?->name ?? '-' }}</p>
                                        </td>
                                        <td class="px-5 py-3 text-center">
                                            @if ($row->total > 0)
                                                <a href="{{ route('hazard-report.index', ['status' => 'all', 'q' => $row->user->name]) }}"
                                                   class="inline-flex min-w-[2rem] justify-center rounded-full bg-emerald-100 px-2 py-0.5 text-sm font-bold text-emerald-800 hover:bg-emerald-200"
                                                   title="Lihat laporan {{ $row->user->name }}">{{ $row->total }}</a>
                                            @else
                                                <span class="font-semibold">0</span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-3 text-center">{{ $row->valid ?: '-' }}</td>
                                        <td class="px-5 py-3 text-center {{ $row->rejected ? 'font-semibold text-red-600' : '' }}">{{ $row->rejected ?: '-' }}</td>
                                        <td class="px-5 py-3 text-center">{{ $row->pending ?: '-' }}</td>
                                        <td class="px-5 py-3 text-center {{ $row->closed ? 'text-emerald-700' : '' }}">{{ $row->closed ?: '-' }}</td>
                                        @if ($target > 0)
                                            <td class="px-5 py-3 text-center whitespace-nowrap">
                                                <span class="inline-flex rounded-full border px-2 py-0.5 text-xs font-semibold {{ $row->target_met ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-gray-200 bg-gray-50 text-gray-500' }}">
                                                    {{ $row->counted }} / {{ $target }}
                                                </span>
                                            </td>
                                        @endif
                                        <td class="px-5 py-3 text-center text-gray-500">{{ $row->all_time }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <p class="border-t border-gray-100 px-5 py-3 text-xs text-gray-500">
                        Dihitung dari tanggal laporan dikirim. "Diterima" = sudah divalidasi HSE dan tidak ditolak.
                        @if ($target > 0) Laporan yang ditolak tidak dihitung ke target. @endif
                    </p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>