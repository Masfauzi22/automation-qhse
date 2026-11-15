{{--
    Kartu pengingat Safety Campaign di Dashboard.
    Pemakaian: <x-safety-campaign-reminder />
    Hanya tampil kalau user punya akses menu Safety Campaign dan masih ada campaign aktif yang belum diselesaikan.
--}}
@php
    $reminderUser = auth()->user();
    $canAccess    = $reminderUser?->can('menu_personal_record.safety_campaign');

    $pendingQuery = $canAccess ? \App\Models\SafetyCampaign::pendingFor($reminderUser) : null;
    $pendingTotal = $pendingQuery ? (clone $pendingQuery)->count() : 0;
    $pendingList  = $pendingTotal
        ? (clone $pendingQuery)->with('images')->withCount('questions')
            ->orderByRaw('end_date IS NULL')->orderBy('end_date')->latest('start_date') // yang paling dekat batasnya di atas
            ->limit(3)->get()
        : collect();
@endphp

@if ($pendingTotal > 0)
    <div class="overflow-hidden rounded-xl border border-amber-200 bg-gradient-to-r from-amber-50 to-white shadow-sm">
        <div class="flex flex-col gap-2 border-b border-amber-100 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-amber-400 text-gray-900">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84L4.5 13.5v-3l5.84-2.34a13.5 13.5 0 007.66-3.66v14A13.5 13.5 0 0010.34 15.84z"/></svg>
                </span>
                <div>
                    <p class="font-bold text-gray-900">Ada {{ $pendingTotal }} Safety Campaign yang belum kamu selesaikan</p>
                    <p class="text-sm text-gray-600">Luangkan 1–2 menit untuk membaca pesan keselamatan dari tim QHSE.</p>
                </div>
            </div>
            @if ($pendingTotal > $pendingList->count())
                <a href="{{ route('my-safety-campaign.index') }}" class="text-sm font-semibold text-amber-700 hover:text-amber-900">Lihat semua &rarr;</a>
            @endif
        </div>

        <div class="divide-y divide-amber-100">
            @foreach ($pendingList as $campaign)
                @php $cover = $campaign->images->first(); @endphp
                <div class="flex items-center gap-4 px-6 py-3">
                    <div class="h-14 w-14 shrink-0 overflow-hidden rounded-lg bg-gray-100">
                        @if ($cover)
                            <img src="{{ $cover->url() }}" alt="" class="h-full w-full object-cover">
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate font-semibold text-gray-900">{{ $campaign->title }}</p>
                        <p class="text-xs text-gray-500">
                            {{ $campaign->questions_count ? $campaign->questions_count . ' soal' : 'Baca & konfirmasi' }}
                            @if ($campaign->end_date)
                                · <span class="font-semibold text-red-600">batas {{ $campaign->end_date->translatedFormat('d M Y') }}</span>
                            @endif
                        </p>
                    </div>
                    <a href="{{ route('my-safety-campaign.show', $campaign) }}"
                       class="shrink-0 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700">
                        Baca sekarang
                    </a>
                </div>
            @endforeach
        </div>
    </div>
@endif