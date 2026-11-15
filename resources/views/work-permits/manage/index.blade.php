@php
    $input = 'rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500';
    $tabs = [
        'todo'   => 'Perlu Tindakan Saya',
        'active' => 'Aktif / Dihentikan',
        'all'    => 'Semua',
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Tasklist Permit to Work</h2>
            <p class="text-xs text-gray-500">{{ $isHse ? 'Semua permit dari seluruh departemen.' : 'Permit yang diajukan bawahanmu di departemen ' . (auth()->user()->department?->name ?? '-') . '.' }}</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Ringkasan -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="{{ route('permit.index', ['tab' => 'todo']) }}" class="rounded-xl border border-amber-100 bg-amber-50 p-4 shadow-sm transition hover:shadow-md">
                    <p class="text-xs font-semibold uppercase tracking-wider text-amber-700">Perlu Tindakan</p>
                    <p class="mt-1 text-3xl font-bold text-amber-800">{{ $counts['todo'] }}</p>
                    <p class="text-xs text-amber-700">menunggu kamu</p>
                </a>
                <a href="{{ route('permit.index', ['tab' => 'active']) }}" class="rounded-xl border border-emerald-100 bg-emerald-50 p-4 shadow-sm transition hover:shadow-md">
                    <p class="text-xs font-semibold uppercase tracking-wider text-emerald-700">Aktif Hari Ini</p>
                    <p class="mt-1 text-3xl font-bold text-emerald-800">{{ $counts['active_today'] }}</p>
                    <p class="text-xs text-emerald-700">dari {{ $counts['active'] }} permit aktif</p>
                </a>
                <a href="{{ route('permit.index', ['tab' => 'active']) }}" class="rounded-xl border border-red-100 bg-red-50 p-4 shadow-sm transition hover:shadow-md">
                    <p class="text-xs font-semibold uppercase tracking-wider text-red-700">Dihentikan</p>
                    <p class="mt-1 text-3xl font-bold text-red-700">{{ $counts['stopped'] }}</p>
                    <p class="text-xs text-red-700">stop work</p>
                </a>
                <a href="{{ route('permit.index', ['tab' => 'all']) }}" class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm transition hover:shadow-md">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Total Permit</p>
                    <p class="mt-1 text-3xl font-bold text-gray-900">{{ $counts['all'] }}</p>
                    <p class="text-xs text-gray-500">semua status</p>
                </a>
            </div>

            <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
                <!-- Tab -->
                <div class="flex gap-1 overflow-x-auto border-b border-gray-100 px-4 pt-3">
                    @foreach ($tabs as $key => $label)
                        <a href="{{ route('permit.index', array_filter(['tab' => $key, 'q' => $search])) }}"
                           class="whitespace-nowrap border-b-2 px-3 pb-3 text-sm font-semibold transition {{ $tab === $key ? 'border-emerald-600 text-emerald-700' : 'border-transparent text-gray-500 hover:text-gray-800' }}">
                            {{ $label }}
                            @if ($key === 'todo' && $counts['todo'] > 0)
                                <span class="ml-1 rounded-full bg-red-500 px-1.5 py-0.5 text-[11px] text-white">{{ $counts['todo'] }}</span>
                            @endif
                        </a>
                    @endforeach
                </div>

                <!-- Cari -->
                <form method="GET" action="{{ route('permit.index') }}" class="flex flex-col md:flex-row gap-3 px-4 py-4 border-b border-gray-100">
                    <input type="hidden" name="tab" value="{{ $tab }}">
                    <input type="text" name="q" value="{{ $search }}" placeholder="Cari no. permit, project, lokasi, atau pengaju..." class="{{ $input }} md:flex-1">
                    <button type="submit" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">Cari</button>
                </form>

                @if ($permits->isEmpty())
                    <div class="px-6 py-16 text-center">
                        <h4 class="text-sm font-semibold text-gray-900">{{ $tab === 'todo' ? 'Tidak ada permit yang menunggu tindakanmu' : 'Belum ada permit' }}</h4>
                        <p class="mt-1 text-sm text-gray-500">Coba ganti tab atau kata pencarian.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100 text-sm">
                            <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wider text-gray-500">
                                <tr>
                                    <th class="px-5 py-3 text-left">No. Permit</th>
                                    <th class="px-5 py-3 text-left">Pekerjaan</th>
                                    <th class="px-5 py-3 text-left">Pengaju</th>
                                    <th class="px-5 py-3 text-left">Berlaku</th>
                                    <th class="px-5 py-3 text-left">Status</th>
                                    <th class="px-5 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($permits as $permit)
                                    <tr class="hover:bg-gray-50 align-top">
                                        <td class="px-5 py-3 whitespace-nowrap">
                                            <p class="font-semibold text-gray-900">{{ $permit->permit_number }}</p>
                                            <p class="text-xs text-red-600">{{ $permit->typeLabel() }}</p>
                                        </td>
                                        <td class="px-5 py-3 max-w-xs">
                                            <p class="font-medium text-gray-800 truncate" title="{{ $permit->project_name }}">{{ $permit->project_name }}</p>
                                            <p class="text-xs text-gray-500 truncate">{{ $permit->location }}</p>
                                        </td>
                                        <td class="px-5 py-3 whitespace-nowrap">
                                            <p class="font-medium text-gray-800">{{ $permit->applicant_name }}</p>
                                            <p class="text-xs text-gray-500">{{ $permit->applicantPositionLabel() }} · {{ $permit->applicantDepartment?->name }}</p>
                                        </td>
                                        <td class="px-5 py-3 whitespace-nowrap text-xs text-gray-600">
                                            {{ $permit->start_date->translatedFormat('d M') }} – {{ $permit->end_date->translatedFormat('d M Y') }}
                                            <span class="block text-gray-400">{{ substr((string) $permit->start_time, 0, 5) }}–{{ substr((string) $permit->end_time, 0, 5) }}</span>
                                        </td>
                                        <td class="px-5 py-3 whitespace-nowrap">
                                            <span class="inline-flex rounded-full border px-2 py-0.5 text-xs font-semibold {{ $permit->statusClass() }}">{{ $permit->statusLabel() }}</span>
                                            @if ($permit->submitted_at && $tab === 'todo')
                                                <span class="block mt-1 text-[11px] {{ $permit->updated_at->lt(now()->subHours(4)) ? 'font-semibold text-red-600' : 'text-gray-400' }}">sejak {{ $permit->updated_at->diffForHumans() }}</span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-3 text-right whitespace-nowrap">
                                            <a href="{{ route('permit.show', $permit) }}"
                                               class="inline-flex items-center rounded-lg px-3 py-1.5 text-sm font-semibold {{ $tab === 'todo' ? 'bg-emerald-600 text-white hover:bg-emerald-700' : 'text-emerald-700 hover:bg-emerald-50' }}">
                                                {{ $tab === 'todo' ? 'Proses' : 'Lihat' }}
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if ($permits->hasPages())
                        <div class="border-t border-gray-100 px-5 py-3">{{ $permits->links() }}</div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</x-app-layout>