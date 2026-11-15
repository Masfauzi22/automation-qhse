@php
    $roleCount = $roles->count();
    $stickyCell = 'sticky left-0 z-10 border-r border-slate-200/80 shadow-[1px_0_0_0_rgba(226,232,240,1)]';
    $checkbox   = 'w-5 h-5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 cursor-pointer transition-colors';
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-emerald-500/10 border border-emerald-500/20 rounded-xl text-emerald-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="font-bold text-xl text-slate-900 tracking-tight">{{ __('Pengaturan Akses Menu') }}</h2>
                    <p class="text-xs text-slate-500 font-medium">Matriks hak akses menu dan submenu untuk setiap role</p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-slate-50/50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" x-transition.duration.500ms
                     class="flex items-center gap-3 bg-emerald-50 border border-emerald-200/80 p-4 rounded-xl text-emerald-800">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm font-semibold">{{ session('success') }}</p>
                </div>
            @endif

            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
                <div class="p-6 md:p-8 space-y-6">
                    <div class="flex flex-col md:flex-row md:items-end justify-between gap-3">
                        <div class="space-y-1">
                            <h3 class="text-base font-bold text-slate-900">Matriks Hak Akses Menu</h3>
                            <p class="text-xs text-slate-500">
                                Centang menu induk untuk memberi akses ke semua submenunya, atau centang submenu satu per satu.
                                <span class="text-emerald-600 font-semibold">Superadmin otomatis memiliki akses penuh ke semua menu.</span>
                            </p>
                        </div>
                        <div class="flex items-center gap-4 text-[11px] text-slate-500">
                            <span class="flex items-center gap-1.5"><span class="inline-block h-3.5 w-3.5 rounded bg-emerald-600"></span> Semua submenu</span>
                            <span class="flex items-center gap-1.5"><span class="inline-flex h-3.5 w-3.5 items-center justify-center rounded border border-slate-300"><span class="h-0.5 w-2 bg-slate-500"></span></span> Sebagian</span>
                            <span class="flex items-center gap-1.5"><span class="rounded-full bg-slate-100 px-1.5 py-0.5 text-[9px] font-bold uppercase text-slate-400">Soon</span> Halaman belum dibuat</span>
                        </div>
                    </div>

                    <form action="{{ route('menu-access.update') }}" method="POST" class="space-y-6">
                        @csrf

                        <div class="overflow-x-auto overflow-y-auto max-h-[65vh] rounded-xl border border-slate-200/80 custom-scrollbar">
                            <table class="min-w-full text-left">
                                <thead class="bg-slate-50 sticky top-0 z-20 border-b border-slate-200/80">
                                    <tr>
                                        <th scope="col" class="px-6 py-4 text-xs font-bold text-slate-700 uppercase tracking-wider bg-slate-50 {{ $stickyCell }} z-30 min-w-[280px]">
                                            Nama Menu
                                        </th>
                                        @foreach ($roles as $role)
                                            <th scope="col" class="px-4 py-4 text-center text-xs font-bold text-slate-700 uppercase tracking-wider border-r border-slate-200/80 whitespace-nowrap">
                                                {{ strtoupper($role->name) }}
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>

                                @foreach ($sections as $section)
                                    {{-- Judul section --}}
                                    <tbody>
                                        <tr class="bg-slate-100/70">
                                            <td class="px-6 py-2 text-[11px] font-bold uppercase tracking-wider text-slate-500 bg-slate-100 {{ $stickyCell }}">{{ $section['section'] }}</td>
                                            <td colspan="{{ $roleCount }}" class="bg-slate-100/70"></td>
                                        </tr>
                                    </tbody>

                                    @foreach ($section['items'] as $item)

                                        {{-- ===== Menu khusus superadmin ===== --}}
                                        @if (! empty($item['superadmin_only']))
                                            <tbody class="border-b border-slate-100">
                                                <tr>
                                                    <td class="px-6 py-3.5 bg-white {{ $stickyCell }}">
                                                        <span class="flex items-center gap-3 text-sm font-semibold text-slate-800">
                                                            <svg class="h-5 w-5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $item['icon'] ?? '' !!}</svg>
                                                            {{ $item['label'] }}
                                                        </span>
                                                        <span class="ml-8 text-xs text-slate-400">{{ collect($item['children'] ?? [])->pluck('label')->join(', ') }}</span>
                                                    </td>
                                                    <td colspan="{{ $roleCount }}" class="px-4 py-3.5 text-center">
                                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-500">
                                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                                            Khusus Superadmin
                                                        </span>
                                                    </td>
                                                </tr>
                                            </tbody>

                                        {{-- ===== Menu dengan submenu ===== --}}
                                        @elseif (! empty($item['children']))
                                            @php $groupKey = $item['key']; @endphp
                                            <tbody x-data="{ open: true }" class="border-b border-slate-100">
                                                <tr class="hover:bg-slate-50/80 transition-colors">
                                                    <td class="px-6 py-3.5 bg-white {{ $stickyCell }}">
                                                        <button type="button" @click="open = !open" class="flex w-full items-center gap-3 text-left text-sm font-bold text-slate-900">
                                                            <svg class="h-5 w-5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $item['icon'] ?? '' !!}</svg>
                                                            <span class="flex-1">{{ $item['label'] }}</span>
                                                            <span class="text-[11px] font-medium text-slate-400">{{ count($item['children']) }} submenu</span>
                                                            <svg class="h-4 w-4 shrink-0 text-slate-400 transition-transform" :class="{ '-rotate-90': !open }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                                        </button>
                                                    </td>
                                                    @foreach ($roles as $role)
                                                        <td class="px-4 py-3.5 text-center border-r border-slate-100">
                                                            {{-- Checkbox induk: hanya alat bantu, tidak dikirim ke server --}}
                                                            <input type="checkbox" class="{{ $checkbox }}"
                                                                   data-parent="{{ $groupKey }}" data-role="{{ $role->id }}"
                                                                   title="Centang semua submenu {{ $item['label'] }} untuk {{ strtoupper($role->name) }}">
                                                        </td>
                                                    @endforeach
                                                </tr>

                                                @foreach ($item['children'] as $child)
                                                    <tr x-show="open" class="bg-slate-50/40 hover:bg-slate-50 transition-colors">
                                                        <td class="py-2.5 pl-14 pr-6 bg-slate-50 {{ $stickyCell }}">
                                                            <span class="flex items-center gap-2 text-sm text-slate-600">
                                                                <span class="h-1.5 w-1.5 rounded-full bg-slate-300"></span>
                                                                {{ $child['label'] }}
                                                                @if (empty($child['route']))
                                                                    <span class="rounded-full bg-slate-100 px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wide text-slate-400">Soon</span>
                                                                @endif
                                                            </span>
                                                        </td>
                                                        @foreach ($roles as $role)
                                                            <td class="px-4 py-2.5 text-center border-r border-slate-100">
                                                                <input type="checkbox" class="{{ $checkbox }} h-4 w-4"
                                                                       name="permissions[{{ $role->id }}][{{ $child['key'] }}]" value="1"
                                                                       data-child-of="{{ $groupKey }}" data-role="{{ $role->id }}"
                                                                       @checked(in_array($child['key'], $rolePermissions[$role->id] ?? []))>
                                                            </td>
                                                        @endforeach
                                                    </tr>
                                                @endforeach
                                            </tbody>

                                        {{-- ===== Menu tunggal ===== --}}
                                        @else
                                            <tbody class="border-b border-slate-100">
                                                <tr class="hover:bg-slate-50/80 transition-colors">
                                                    <td class="px-6 py-3.5 bg-white {{ $stickyCell }}">
                                                        <span class="flex items-center gap-3 text-sm font-bold text-slate-900">
                                                            <svg class="h-5 w-5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $item['icon'] ?? '' !!}</svg>
                                                            {{ $item['label'] }}
                                                        </span>
                                                    </td>
                                                    @if (empty($item['key']))
                                                        <td colspan="{{ $roleCount }}" class="px-4 py-3.5 text-center">
                                                            <span class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">Selalu tampil untuk semua user</span>
                                                        </td>
                                                    @else
                                                        @foreach ($roles as $role)
                                                            <td class="px-4 py-3.5 text-center border-r border-slate-100">
                                                                <input type="checkbox" class="{{ $checkbox }}"
                                                                       name="permissions[{{ $role->id }}][{{ $item['key'] }}]" value="1"
                                                                       @checked(in_array($item['key'], $rolePermissions[$role->id] ?? []))>
                                                            </td>
                                                        @endforeach
                                                    @endif
                                                </tr>
                                            </tbody>
                                        @endif

                                    @endforeach
                                @endforeach
                            </table>
                        </div>

                        <div class="flex justify-end pt-2">
                            <button type="submit"
                                    class="inline-flex justify-center items-center gap-2 px-6 py-3 bg-slate-900 hover:bg-slate-800 active:bg-slate-950 text-white text-sm font-bold rounded-xl shadow-sm transition-all">
                                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>Simpan Hak Akses</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Hubungan checkbox induk <-> submenu
        document.addEventListener('DOMContentLoaded', () => {
            const children = (group, role) =>
                document.querySelectorAll(`input[data-child-of="${group}"][data-role="${role}"]`);

            // Induk tercentang penuh kalau semua submenu tercentang, setengah (–) kalau sebagian
            const refreshParent = (group, role) => {
                const parent = document.querySelector(`input[data-parent="${group}"][data-role="${role}"]`);
                if (! parent) return;
                const kids    = Array.from(children(group, role));
                const checked = kids.filter(k => k.checked).length;
                parent.checked       = kids.length > 0 && checked === kids.length;
                parent.indeterminate = checked > 0 && checked < kids.length;
            };

            document.querySelectorAll('input[data-parent]').forEach(parent => {
                const { parent: group, role } = parent.dataset;
                refreshParent(group, role);
                parent.addEventListener('change', () => {
                    children(group, role).forEach(k => k.checked = parent.checked);
                    parent.indeterminate = false;
                });
            });

            document.querySelectorAll('input[data-child-of]').forEach(child => {
                child.addEventListener('change', () => refreshParent(child.dataset.childOf, child.dataset.role));
            });
        });
    </script>
</x-app-layout>