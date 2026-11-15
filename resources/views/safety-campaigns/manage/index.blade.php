<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Safety Campaign</h2>
            <p class="text-xs text-gray-500">Kelola kampanye keselamatan dan pantau siapa saja yang sudah membaca.</p>
        </div>
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

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h3 class="text-xl font-bold text-gray-900">Daftar Campaign</h3>
                    <p class="text-sm text-gray-500">Campaign berstatus Aktif tampil untuk karyawan yang ditargetkan.</p>
                </div>
                <a href="{{ route('safety-campaigns.create') }}"
                   class="inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6"/></svg>
                    Buat Campaign
                </a>
            </div>

            @if ($campaigns->isEmpty())
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-6 py-16 text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-emerald-50">
                        <svg class="h-7 w-7 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84L4.5 13.5v-3l5.84-2.34a13.5 13.5 0 007.66-3.66v14A13.5 13.5 0 0010.34 15.84z"/></svg>
                    </div>
                    <h4 class="mt-4 text-sm font-semibold text-gray-900">Belum ada campaign</h4>
                    <p class="mt-1 text-sm text-gray-500">Buat campaign pertama untuk mulai membagikan pesan keselamatan.</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach ($campaigns as $campaign)
                        @php
                            $target = $campaign->target_all
                                ? $totalUsers
                                : $campaign->departments->sum(fn ($d) => $usersPerDept[$d->id] ?? 0);
                            $pct = $target > 0 ? min(100, round($campaign->completed_count / $target * 100)) : 0;
                            $cover = $campaign->images->first();
                        @endphp

                        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex flex-col md:flex-row gap-4">
                            <!-- Sampul -->
                            <div class="h-28 w-full md:w-40 shrink-0 overflow-hidden rounded-lg bg-gray-100">
                                @if ($cover)
                                    <img src="{{ $cover->url() }}" alt="" class="h-full w-full object-cover">
                                @endif
                            </div>

                            <!-- Info -->
                            <div class="min-w-0 flex-1 space-y-2">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="inline-flex rounded-full border px-2 py-0.5 text-xs font-semibold {{ $campaign->statusClass() }}">{{ $campaign->statusLabel() }}</span>
                                    <span class="inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600">
                                        {{ $campaign->questions_count ? $campaign->questions_count . ' soal' : 'Tanpa soal' }}
                                    </span>
                                </div>
                                <h4 class="text-base font-bold text-gray-900 truncate">{{ $campaign->title }}</h4>
                                <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-500">
                                    <span>
                                        {{ $campaign->start_date->translatedFormat('d M Y') }}
                                        – {{ $campaign->end_date ? $campaign->end_date->translatedFormat('d M Y') : 'tanpa batas' }}
                                    </span>
                                    <span>
                                        Target: {{ $campaign->target_all ? 'Semua karyawan' : $campaign->departments->pluck('name')->join(', ') }}
                                    </span>
                                </div>

                                <!-- Progres -->
                                @if ($campaign->is_published)
                                    <div class="flex items-center gap-3 pt-1">
                                        <div class="h-2 flex-1 max-w-xs overflow-hidden rounded-full bg-gray-100">
                                            <div class="h-full rounded-full bg-emerald-500" style="width: {{ $pct }}%"></div>
                                        </div>
                                        <span class="text-xs font-medium text-gray-600">{{ $campaign->completed_count }} / {{ $target }} selesai ({{ $pct }}%)</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Aksi -->
                            <div class="flex md:flex-col items-center md:items-end justify-end gap-2 shrink-0">
                                <a href="{{ route('my-safety-campaign.show', $campaign) }}" target="_blank"
                                   class="rounded-lg px-3 py-1.5 text-sm font-semibold text-gray-600 hover:bg-gray-50" title="Lihat tampilan yang diterima karyawan">Pratinjau</a>
                                <a href="{{ route('safety-campaigns.edit', $campaign) }}"
                                   class="rounded-lg border border-gray-200 px-3 py-1.5 text-sm font-semibold text-gray-700 hover:bg-gray-50">Edit</a>

                                <form method="POST" action="{{ route('safety-campaigns.publish', $campaign) }}">
                                    @csrf
                                    @method('PUT')
                                    @if ($campaign->is_published)
                                        <button type="submit" onclick="return confirm('Tarik campaign ini? Karyawan tidak akan melihatnya lagi sampai diterbitkan ulang.')"
                                                class="rounded-lg px-3 py-1.5 text-sm font-semibold text-amber-700 hover:bg-amber-50">Tarik</button>
                                    @else
                                        <button type="submit" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-emerald-700">Terbitkan</button>
                                    @endif
                                </form>

                                <form method="POST" action="{{ route('safety-campaigns.destroy', $campaign) }}"
                                      onsubmit="return confirm('Hapus campaign {{ addslashes($campaign->title) }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg px-3 py-1.5 text-sm font-semibold text-red-600 hover:bg-red-50">Hapus</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if ($campaigns->hasPages())
                    <div>{{ $campaigns->links() }}</div>
                @endif
            @endif
        </div>
    </div>
</x-app-layout>