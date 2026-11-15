@php
    $tabs = [
        'pending' => 'Belum Dikerjakan',
        'done'    => 'Selesai',
        'all'     => 'Semua',
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Safety Campaign</h2>
            <p class="text-xs text-gray-500">Pesan keselamatan dari tim QHSE untuk kamu.</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Tab -->
            <div class="flex gap-1 overflow-x-auto border-b border-gray-200">
                @foreach ($tabs as $key => $label)
                    <a href="{{ route('my-safety-campaign.index', ['tab' => $key]) }}"
                       class="whitespace-nowrap border-b-2 px-4 pb-3 text-sm font-semibold transition {{ $tab === $key ? 'border-emerald-600 text-emerald-700' : 'border-transparent text-gray-500 hover:text-gray-800' }}">
                        {{ $label }}
                        <span class="ml-1 rounded-full px-1.5 py-0.5 text-[11px]
                                     {{ $key === 'pending' && $counts['pending'] > 0 ? 'bg-red-500 text-white' : ($tab === $key ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500') }}">
                            {{ $counts[$key] }}
                        </span>
                    </a>
                @endforeach
            </div>

            @if ($campaigns->isEmpty())
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-6 py-16 text-center">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-emerald-50">
                        <svg class="h-7 w-7 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <h4 class="mt-4 text-sm font-semibold text-gray-900">
                        {{ $tab === 'pending' ? 'Semua campaign sudah kamu selesaikan' : 'Belum ada campaign di sini' }}
                    </h4>
                    <p class="mt-1 text-sm text-gray-500">Campaign baru dari tim QHSE akan muncul di halaman ini.</p>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach ($campaigns as $campaign)
                        @php
                            $myResponse = $campaign->responses->first();
                            $done       = $myResponse?->isCompleted();
                            $missed     = ! $done && $campaign->isEnded();
                            $cover      = $campaign->images->first();
                        @endphp

                        <a href="{{ route('my-safety-campaign.show', $campaign) }}"
                           class="group flex flex-col overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                            <div class="relative aspect-[4/3] bg-gray-100">
                                @if ($cover)
                                    <img src="{{ $cover->url() }}" alt="" class="h-full w-full object-cover {{ $done || $missed ? 'opacity-70' : '' }}">
                                @endif

                                <!-- Status -->
                                @if ($done)
                                    <span class="absolute left-3 top-3 inline-flex items-center gap-1 rounded-full bg-emerald-600 px-2.5 py-1 text-xs font-bold text-white shadow">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        Selesai
                                    </span>
                                @elseif ($missed)
                                    <span class="absolute left-3 top-3 rounded-full bg-gray-700 px-2.5 py-1 text-xs font-bold text-white shadow">Terlewat</span>
                                @else
                                    <span class="absolute left-3 top-3 rounded-full bg-amber-400 px-2.5 py-1 text-xs font-bold text-gray-900 shadow">Belum dikerjakan</span>
                                @endif
                            </div>

                            <div class="flex flex-1 flex-col p-4">
                                <h4 class="font-bold text-gray-900 group-hover:text-emerald-700 line-clamp-2">{{ $campaign->title }}</h4>
                                <p class="mt-1 text-sm text-gray-500 line-clamp-2">{{ $campaign->message }}</p>

                                <div class="mt-auto flex items-center justify-between pt-4 text-xs">
                                    <span class="text-gray-500">
                                        @if ($done)
                                            Selesai {{ $myResponse->completed_at->translatedFormat('d M Y') }}
                                        @elseif ($campaign->end_date)
                                            <span class="{{ $missed ? '' : 'font-semibold text-red-600' }}">
                                                {{ $missed ? 'Berakhir' : 'Batas' }} {{ $campaign->end_date->translatedFormat('d M Y') }}
                                            </span>
                                        @else
                                            Mulai {{ $campaign->start_date->translatedFormat('d M Y') }}
                                        @endif
                                    </span>
                                    <span class="rounded-full bg-gray-100 px-2 py-0.5 font-medium text-gray-600">
                                        {{ $campaign->questions_count ? $campaign->questions_count . ' soal' : 'Baca saja' }}
                                    </span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>

                @if ($campaigns->hasPages())
                    <div>{{ $campaigns->links() }}</div>
                @endif
            @endif
        </div>
    </div>
</x-app-layout>