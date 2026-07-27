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
                    <h2 class="font-bold text-xl text-slate-900 tracking-tight">
                        {{ __('Pengaturan Akses Menu') }}
                    </h2>
                    <p class="text-xs text-slate-500 font-medium">Matriks hak akses menu dan otorisasi role pengguna sistem</p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-slate-50/50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            
            @if (session('success'))
                <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" x-transition.duration.500ms 
                     class="flex items-center gap-3 bg-emerald-50 border border-emerald-200/80 p-4 rounded-xl shadow-xs text-emerald-800">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm font-semibold">{{ session('success') }}</p>
                </div>
            @endif

            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <div class="p-6 md:p-8 space-y-6">
                    <div class="space-y-1">
                        <h3 class="text-base font-bold text-slate-900">Matriks Hak Akses Menu</h3>
                        <p class="text-xs text-slate-500">Centang menu apa saja yang boleh dilihat oleh masing-masing Role. <span class="text-emerald-600 font-semibold">Superadmin otomatis memiliki akses penuh ke semua menu.</span></p>
                    </div>

                    <form action="{{ route('menu-access.update') }}" method="POST" class="space-y-6">
                        @csrf

                        <div class="overflow-x-auto overflow-y-auto max-h-[65vh] rounded-xl border border-slate-200/80 custom-scrollbar">
                            <table class="min-w-full divide-y divide-slate-100 relative text-left">
                                <thead class="bg-slate-50 sticky top-0 z-20 border-b border-slate-200/80">
                                    <tr>
                                        <th scope="col" class="px-6 py-4 text-xs font-bold text-slate-700 uppercase tracking-wider bg-slate-50 border-r border-slate-200/80 sticky left-0 z-30 shadow-[1px_0_0_0_rgba(226,232,240,1)]">
                                            Nama Menu
                                        </th>
                                        @foreach($roles as $role)
                                            <th scope="col" class="px-6 py-4 text-center text-xs font-bold text-slate-700 uppercase tracking-wider border-r border-slate-200/80 whitespace-nowrap">
                                                {{ strtoupper($role->name) }}
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 bg-white">
                                    @foreach($menus as $permissionCode => $menuName)
                                        <tr class="hover:bg-slate-50/80 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-slate-800 bg-slate-50/90 border-r border-slate-200/80 sticky left-0 z-10 shadow-[1px_0_0_0_rgba(226,232,240,1)]">
                                                {{ $menuName }}
                                            </td>
                                            
                                            @foreach($roles as $role)
                                                <td class="px-6 py-4 whitespace-nowrap text-center border-r border-slate-100">
                                                    <label class="inline-flex items-center cursor-pointer">
                                                        <input type="checkbox" 
                                                               name="permissions[{{ $role->id }}][{{ $permissionCode }}]" 
                                                               value="1" 
                                                               {{ $role->hasPermissionTo($permissionCode) ? 'checked' : '' }}
                                                               class="w-5 h-5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 cursor-pointer transition-colors shadow-2xs">
                                                    </label>
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
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
</x-app-layout>