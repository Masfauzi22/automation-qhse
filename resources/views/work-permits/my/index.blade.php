@php
    use App\Models\WorkPermit;
    $total = $counts->sum();
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Permit to Work</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="flex items-center gap-3 rounded-r-lg border-l-4 border-emerald-500 bg-emerald-50 p-4">
                    <p class="text-sm font-medium text-emerald-800">{{ session('success') }}</p>
                </div>
            @endif

            <!-- Header + tombol ajukan -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h3 class="text-xl font-bold text-gray-900">Permit yang kamu ajukan</h3>
                    <p class="text-sm text-gray-500">Pekerjaan berisiko baru boleh dimulai setelah permit berstatus <b>Aktif</b>.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    @if ($canApply)
                    @foreach ($types as $key => $type)
                        <a href="{{ route('my-permit.create', ['type' => $key]) }}"
                           class="inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6"/></svg>
                            Ajukan {{ $type['label'] }}
                        </a>
                    @endforeach
                    @else
                        <p class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-2 text-sm text-amber-800">
                            Permit to Work hanya bisa diajukan oleh <b>Foreman</b> atau <b>Supervisor</b>.
                        </p>
                    @endif
                </div>
            </div>

            <!-- Filter status -->
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('my-permit.index') }}"
                   class="rounded-full border px-3 py-1.5 text-xs font-semibold transition {{ ! $status ? 'border-gray-900 bg-gray-900 text-white' : 'border-gray-200 bg-white text-gray-600 hover:border-gray-300' }}">
                    Semua <span class="ml-1 opacity-70">{{ $total }}</span>
                </a>
                @foreach (WorkPermit::STATUSES as $key => $meta)
                    @continue(! ($counts[$key] ?? 0) && $status !== $key)
                    <a href="{{ route('my-permit.index', ['status' => $key]) }}"
                       class="rounded-full border px-3 py-1.5 text-xs font-semibold transition {{ $status === $key ? 'border-gray-900 bg-gray-900 text-white' : 'border-gray-200 bg-white text-gray-600 hover:border-gray-300' }}">
                        {{ $meta['label'] }} <span class="ml-1 opacity-70">{{ $counts[$key] ?? 0 }}</span>
                    </a>
                @endforeach
            </div>

            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                @if ($permits->isEmpty())
                    <div class="px-6 py-16 text-center">
                        <h4 class="text-sm font-semibold text-gray-900">{{ $status ? 'Tidak ada permit dengan status ini' : 'Belum ada permit' }}</h4>
                        <p class="mt-1 text-sm text-gray-500">Akan melakukan pekerjaan panas (las, potong, gerinda)? Ajukan permit terlebih dahulu.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100 text-sm">
                            <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wider text-gray-500">
                                <tr>
                                    <th class="px-5 py-3 text-left">No. Permit</th>
                                    <th class="px-5 py-3 text-left">Pekerjaan</th>
                                    <th class="px-5 py-3 text-left">Lokasi</th>
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
                                            <p class="font-medium text-gray-800 truncate">{{ $permit->project_name }}</p>
                                            <p class="text-xs text-gray-500">Area {{ $permit->ownerDepartment?->name }}</p>
                                        </td>
                                        <td class="px-5 py-3 max-w-xs"><p class="truncate text-gray-700" title="{{ $permit->location }}">{{ $permit->location }}</p></td>
                                        <td class="px-5 py-3 whitespace-nowrap text-xs text-gray-600">
                                            {{ $permit->start_date->translatedFormat('d M') }} – {{ $permit->end_date->translatedFormat('d M Y') }}
                                            <span class="block text-gray-400">{{ $permit->durationDays() }} hari</span>
                                        </td>
                                        <td class="px-5 py-3 whitespace-nowrap">
                                            <span class="inline-flex rounded-full border px-2 py-0.5 text-xs font-semibold {{ $permit->statusClass() }}">{{ $permit->statusLabel() }}</span>
                                        </td>
                                        <td class="px-5 py-3 text-right whitespace-nowrap">
                                            @if ($permit->status === WorkPermit::STATUS_RETURNED)
                                                {{-- Dikembalikan atasan / QHSE: wajib direvisi --}}
                                                <a href="{{ route('my-permit.edit', $permit) }}"
                                                   class="inline-flex items-center rounded-lg bg-orange-500 px-3 py-1.5 text-sm font-semibold text-white hover:bg-orange-600">
                                                    Revisi
                                                </a>
                                            @else
                                                <div class="inline-flex items-center gap-1">
                                                    @if ($permit->isEditable())
                                                        {{-- Belum diperiksa atasan: masih boleh diubah --}}
                                                        <a href="{{ route('my-permit.edit', $permit) }}"
                                                           class="inline-flex items-center rounded-lg border border-gray-200 px-3 py-1.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                                                            Edit
                                                        </a>
                                                    @endif
                                                    <a href="{{ route('my-permit.show', $permit) }}"
                                                       class="inline-flex items-center rounded-lg px-3 py-1.5 text-sm font-semibold text-emerald-700 hover:bg-emerald-50">
                                                        Lihat
                                                    </a>
                                                </div>
                                            @endif
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