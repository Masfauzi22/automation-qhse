@php
    /*
    | Daftar menu diambil dari config/menus.php (dipakai bersama halaman Akses Menu).
    | Menambah / mengubah menu cukup di file config itu.
    */
    $currentUser  = auth()->user();
    $isSuperadmin = $currentUser?->hasRole('superadmin');

    // Apakah user boleh melihat sebuah child / link tunggal
    $canSee = fn (?string $key) => $key === null || $currentUser?->can($key);

    // Susun menu yang boleh dilihat user ini
    $sections = collect(config('menus', []))
        ->map(function ($section) use ($canSee, $isSuperadmin) {
            $section['items'] = collect($section['items'])
                ->map(function ($item) use ($canSee, $isSuperadmin) {
                    if (! empty($item['superadmin_only'])) {
                        return $isSuperadmin ? $item : null;
                    }
                    if (! empty($item['children'])) {
                        $item['children'] = collect($item['children'])->filter(fn ($c) => $canSee($c['key'] ?? null))->values()->all();
                        return $item['children'] ? $item : null; // grup tampil kalau ada minimal 1 child
                    }
                    return $canSee($item['key'] ?? null) ? $item : null;
                })
                ->filter()
                ->values()
                ->all();
            return $section;
        })
        ->filter(fn ($section) => ! empty($section['items']));

    // Angka badge di submenu: [nama route => jumlah]
    $badges = [];
    if ($currentUser?->can('menu_personal_record.safety_campaign')) {
        $pendingCampaigns = \App\Models\SafetyCampaign::pendingFor($currentUser)->count();
        if ($pendingCampaigns > 0) {
            $badges['my-safety-campaign.index'] = $pendingCampaigns;
        }
    }
@endphp

<nav class="w-64 shrink-0 h-screen bg-white border-r border-gray-200/70 flex flex-col">

    <!-- Brand -->
    <div class="shrink-0 h-16 flex items-center gap-3 px-5 border-b border-gray-100">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group">
            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-700 text-white shadow-sm shadow-emerald-600/30">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
                </svg>
            </span>
            <span class="leading-tight">
                <span class="block text-base font-bold tracking-tight text-gray-900">AQHSE</span>
                <span class="block text-[11px] font-medium text-gray-400">Quality, Health, Safety & Env.</span>
            </span>
        </a>
    </div>

    <!-- Menu (scrollable) -->
    <div class="flex-1 overflow-y-auto px-3 pb-4 [scrollbar-width:thin]">
        @foreach ($sections as $section)
            <p class="px-3 pt-5 pb-2 text-[11px] font-semibold uppercase tracking-wider text-gray-400">{{ $section['section'] }}</p>

            <div class="space-y-0.5">
                @foreach ($section['items'] as $item)
                    @php
                        $patterns = ! empty($item['children'])
                            ? collect($item['children'])->pluck('active')->flatten()->all()
                            : ($item['active'] ?? []);
                        $isActive = $patterns && request()->routeIs(...$patterns);
                        $groupHasBadge = collect($item['children'] ?? [])->contains(fn ($c) => isset($badges[$c['route'] ?? '']));
                    @endphp

                    @if (empty($item['children']))
                        {{-- Menu tunggal --}}
                        <a href="{{ route($item['route']) }}"
                           class="relative flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition
                                  {{ $isActive ? 'bg-emerald-50 text-emerald-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            @if ($isActive)
                                <span class="absolute left-0 top-1/2 h-5 w-1 -translate-y-1/2 rounded-r-full bg-emerald-600"></span>
                            @endif
                            <svg class="h-5 w-5 shrink-0 {{ $isActive ? 'text-emerald-600' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $item['icon'] !!}</svg>
                            <span>{{ $item['label'] }}</span>
                        </a>
                    @else
                        {{-- Menu dengan submenu --}}
                        <div x-data="{ open: {{ $isActive ? 'true' : 'false' }} }">
                            <button type="button" @click="open = !open"
                                    class="relative flex w-full items-center justify-between rounded-lg px-3 py-2 text-sm font-medium transition focus:outline-none
                                           {{ $isActive ? 'text-gray-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                                @if ($isActive)
                                    <span class="absolute left-0 top-1/2 h-5 w-1 -translate-y-1/2 rounded-r-full bg-emerald-600"></span>
                                @endif
                                <span class="flex items-center gap-3">
                                    <svg class="h-5 w-5 shrink-0 {{ $isActive ? 'text-emerald-600' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $item['icon'] !!}</svg>
                                    <span>{{ $item['label'] }}</span>
                                    @if ($groupHasBadge)
                                        {{-- Titik merah: ada submenu yang perlu perhatian --}}
                                        <span class="h-2 w-2 rounded-full bg-red-500" x-show="! open"></span>
                                    @endif
                                </span>
                                <svg class="h-4 w-4 shrink-0 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            <div x-show="open" x-transition.opacity.duration.150ms @if (! $isActive) style="display: none;" @endif
                                 class="ml-[1.375rem] mt-0.5 mb-1 space-y-0.5 border-l border-gray-200 pl-3">
                                @foreach ($item['children'] as $child)
                                    @php $childActive = request()->routeIs(...$child['active']); @endphp

                                    @if ($child['route'])
                                        <a href="{{ route($child['route']) }}"
                                           class="relative flex items-center justify-between rounded-md px-3 py-1.5 text-sm transition
                                                  {{ $childActive ? 'bg-emerald-50 font-semibold text-emerald-700' : 'text-gray-500 hover:bg-gray-50 hover:text-gray-900' }}">
                                            @if ($childActive)
                                                <span class="absolute -left-[0.9rem] top-1/2 h-1.5 w-1.5 -translate-y-1/2 rounded-full bg-emerald-600 ring-4 ring-white"></span>
                                            @endif
                                            <span>{{ $child['label'] }}</span>
                                            @if (isset($badges[$child['route']]))
                                                <span class="min-w-[1.25rem] rounded-full bg-red-500 px-1.5 py-0.5 text-center text-[10px] font-bold leading-none text-white">
                                                    {{ $badges[$child['route']] > 9 ? '9+' : $badges[$child['route']] }}
                                                </span>
                                            @endif
                                        </a>
                                    @else
                                        <span class="flex cursor-not-allowed items-center justify-between rounded-md px-3 py-1.5 text-sm text-gray-400" title="Fitur sedang dikembangkan">
                                            {{ $child['label'] }}
                                            <span class="rounded-full bg-gray-100 px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-gray-400">Soon</span>
                                        </span>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        @endforeach
    </div>

    <!-- Kartu user di bawah -->
    @auth
        <div class="shrink-0 border-t border-gray-100 p-3">
            <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 rounded-lg px-2 py-2 transition hover:bg-gray-50">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-sm font-bold text-emerald-700 ring-2 ring-white">
                    {{ strtoupper(substr($currentUser->name, 0, 1)) }}
                </span>
                <span class="min-w-0 leading-tight">
                    <span class="block truncate text-sm font-semibold text-gray-800">{{ $currentUser->name }}</span>
                    <span class="block truncate text-xs text-gray-500">
                        {{ strtoupper($currentUser->getRoleNames()->first() ?? 'User') }}@if ($currentUser->department) · {{ $currentUser->department->name }}@endif
                    </span>
                </span>
            </a>
        </div>
    @endauth
</nav>