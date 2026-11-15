@php
    use App\Models\HazardReport;
    $total = $counts->sum();
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Hazard Report Saya</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" x-transition
                     class="flex items-center gap-3 rounded-r-lg border-l-4 border-emerald-500 bg-emerald-50 p-4">
                    <svg class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    <p class="text-sm font-medium text-emerald-800">{{ session('success') }}</p>
                </div>
            @endif

            <!-- Header + tombol lapor -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h3 class="text-xl font-bold text-gray-900">Laporan yang kamu kirim</h3>
                    <p class="text-sm text-gray-500">Pantau status validasi dan tindak lanjut setiap laporan.</p>
                </div>
                <a href="{{ route('my-hazard-report.create') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6"/></svg>
                    Lapor Hazard
                </a>
            </div>

            <!-- Jumlah laporan -->
            <x-hazard-report.reporter-stats :stats="$stats" />

            <!-- Filter status -->
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('my-hazard-report.index') }}"
                   class="rounded-full border px-3 py-1.5 text-xs font-semibold transition {{ ! $status ? 'border-gray-900 bg-gray-900 text-white' : 'border-gray-200 bg-white text-gray-600 hover:border-gray-300' }}">
                    Semua <span class="ml-1 opacity-70">{{ $total }}</span>
                </a>
                @foreach (HazardReport::STATUSES as $key => $meta)
                    <a href="{{ route('my-hazard-report.index', ['status' => $key]) }}"
                       class="rounded-full border px-3 py-1.5 text-xs font-semibold transition {{ $status === $key ? 'border-gray-900 bg-gray-900 text-white' : 'border-gray-200 bg-white text-gray-600 hover:border-gray-300' }}">
                        {{ $meta['label'] }} <span class="ml-1 opacity-70">{{ $counts[$key] ?? 0 }}</span>
                    </a>
                @endforeach
            </div>

            <!-- Tabel -->
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                @if ($reports->isEmpty())
                    <div class="px-6 py-16 text-center">
                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-gray-100">
                            <svg class="h-7 w-7 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3h.008M10.29 3.86L1.82 18a1.5 1.5 0 001.29 2.25h17.78a1.5 1.5 0 001.29-2.25L13.71 3.86a1.5 1.5 0 00-2.42 0z"/></svg>
                        </div>
                        <h4 class="mt-4 text-sm font-semibold text-gray-900">{{ $status ? 'Tidak ada laporan dengan status ini' : 'Belum ada laporan' }}</h4>
                        <p class="mt-1 text-sm text-gray-500">Temukan kondisi atau tindakan tidak aman? Laporkan sekarang.</p>
                        @unless ($status)
                            <a href="{{ route('my-hazard-report.create') }}" class="mt-4 inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Lapor Hazard</a>
                        @endunless
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100 text-sm">
                            <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wider text-gray-500">
                                <tr>
                                    <th class="px-5 py-3 text-left">No. Laporan</th>
                                    <th class="px-5 py-3 text-left">Tanggal Temuan</th>
                                    <th class="px-5 py-3 text-left">Kategori</th>
                                    <th class="px-5 py-3 text-left">Lokasi</th>
                                    <th class="px-5 py-3 text-left">PIC</th>
                                    <th class="px-5 py-3 text-left">Prioritas</th>
                                    <th class="px-5 py-3 text-left">Status</th>
                                    <th class="px-5 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($reports as $report)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-5 py-3 font-semibold text-gray-900 whitespace-nowrap">{{ $report->report_number }}</td>
                                        <td class="px-5 py-3 text-gray-700 whitespace-nowrap">{{ $report->finding_date->translatedFormat('d M Y') }}</td>
                                        <td class="px-5 py-3 text-gray-700 whitespace-nowrap">{{ $report->category->name }}</td>
                                        <td class="px-5 py-3 text-gray-700 max-w-xs truncate" title="{{ $report->location }}">{{ $report->location }}</td>
                                        <td class="px-5 py-3 text-gray-700 whitespace-nowrap">{{ $report->picDepartment->name }}</td>
                                        <td class="px-5 py-3 whitespace-nowrap">
                                            <span class="inline-flex rounded-full border px-2 py-0.5 text-xs font-semibold {{ $report->priority->badgeClass() }}">{{ $report->priority->name }}</span>
                                        </td>
                                        <td class="px-5 py-3 whitespace-nowrap">
                                            <span class="inline-flex rounded-full border px-2 py-0.5 text-xs font-semibold {{ $report->statusClass() }}">{{ $report->statusLabel() }}</span>
                                            @if ($report->isOverdue())
                                                <span class="ml-1 inline-flex rounded-full bg-red-600 px-2 py-0.5 text-[10px] font-bold uppercase text-white">Terlambat</span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-3 text-right whitespace-nowrap">
                                            <a href="{{ route('my-hazard-report.show', $report) }}" class="text-sm font-semibold text-emerald-700 hover:text-emerald-900">Lihat &rarr;</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if ($reports->hasPages())
                        <div class="border-t border-gray-100 px-5 py-3">
                            {{ $reports->links() }}
                        </div>
                    @endif
                @endif
            </div>

        </div>
    </div>
</x-app-layout>