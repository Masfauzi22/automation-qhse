@php
    $input = 'block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500';
    $label = 'block text-xs font-semibold text-gray-700 mb-1';
    $whoNames = ['superior' => 'Atasan (Pemeriksa)', 'qhse' => 'QHSE'];
    $isClose = $step && str_starts_with($step['action'], 'close_');
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('permit.index') }}" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700" title="Kembali ke Tasklist">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $canAct ? ($isClose ? 'Close-out Permit to Work' : 'Pemeriksaan Permit to Work') : 'Detail Permit to Work' }}</h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="flex items-center gap-3 rounded-r-lg border-l-4 border-emerald-500 bg-emerald-50 p-4">
                    <svg class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    <p class="text-sm font-medium text-emerald-800">{{ session('success') }}</p>
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded-lg border-l-4 border-red-500 bg-red-50 p-4">
                    <ul class="list-disc list-inside text-sm text-red-700 space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <x-work-permit.detail :permit="$permit">
                @if ($step || $canStop || $canResume)
                    <x-slot:actions>
                        {{-- Stop Work (permit aktif) --}}
                        @if ($canStop)
                            <x-work-permit.stop-form :permit="$permit" />
                        @endif

                        {{-- Lanjutkan pekerjaan (khusus QHSE) --}}
                        @if ($canResume)
                            <section class="bg-white rounded-xl border-2 border-emerald-200 shadow-sm" x-data="{ busy: false }">
                                <div class="border-b border-gray-100 px-5 py-4">
                                    <h4 class="text-sm font-bold text-gray-900">Izinkan Pekerjaan Dilanjutkan</h4>
                                    <p class="text-xs text-gray-500">Pastikan penyebab Stop Work sudah diperbaiki.</p>
                                </div>
                                <form method="POST" action="{{ route('permit.resume', $permit) }}" @submit="busy = true" class="space-y-3 px-5 py-4">
                                    @csrf
                                    @method('PUT')
                                    <div>
                                        <label class="{{ $label }}">Kondisi yang sudah diperbaiki <span class="text-red-500">*</span></label>
                                        <textarea name="resume_note" rows="2" required maxlength="1000" class="{{ $input }}"
                                                  placeholder="{{ $permit->text('resume_example') }}">{{ old('resume_note') }}</textarea>
                                    </div>
                                    <button type="submit" :disabled="busy"
                                            class="w-full rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700 disabled:opacity-60">Lanjutkan Pekerjaan</button>
                                </form>
                            </section>
                        @endif

                        @if ($step)
                        @if ($canAct)
                            <section class="bg-white rounded-xl border-2 border-emerald-200 shadow-sm"
                                     x-data="{ tab: '{{ $errors->has('return_note') ? 'return' : 'approve' }}', busy: false }">
                                <div class="border-b border-gray-100 px-5 pt-4">
                                    <h4 class="text-sm font-bold text-gray-900">Tindakan {{ $whoNames[$step['who']] ?? '' }}</h4>
                                    <p class="text-xs text-gray-500">{{ $isClose ? 'Tahap setelah pekerjaan (close-out)' : 'Tahap sebelum pekerjaan' }}</p>
                                    <div class="mt-3 flex gap-1">
                                        <button type="button" @click="tab = 'approve'"
                                                :class="tab === 'approve' ? 'border-emerald-600 text-emerald-700' : 'border-transparent text-gray-500 hover:text-gray-800'"
                                                class="border-b-2 px-3 pb-2 text-sm font-semibold transition">Teruskan</button>
                                        <button type="button" @click="tab = 'return'"
                                                :class="tab === 'return' ? 'border-orange-500 text-orange-700' : 'border-transparent text-gray-500 hover:text-gray-800'"
                                                class="border-b-2 px-3 pb-2 text-sm font-semibold transition">Kembalikan</button>
                                    </div>
                                </div>

                                {{-- Teruskan --}}
                                <form x-show="tab === 'approve'" method="POST" action="{{ route('permit.approve', $permit) }}"
                                      @submit="busy = true" class="space-y-4 px-5 py-4" style="{{ $errors->has('return_note') ? 'display: none;' : '' }}">
                                    @csrf
                                    @method('PUT')

                                    @foreach ($confirmations as $key => $text)
                                        <label class="flex cursor-pointer items-start gap-2 text-sm text-gray-800">
                                            <input type="checkbox" name="confirm[{{ $key }}]" value="1" required
                                                   class="mt-0.5 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                            <span>{{ $text }}</span>
                                        </label>
                                    @endforeach

                                    @if ($step['next'] === \App\Models\WorkPermit::STATUS_CLOSED)
                                        <div x-data="{ incident: @js(old('incident', 'none')) }" class="rounded-lg border border-gray-200 p-3">
                                            <p class="text-xs font-semibold text-gray-700">Selama pekerjaan, apakah ada insiden / near miss? <span class="text-red-500">*</span></p>
                                            <div class="mt-2 space-y-1.5">
                                                <label class="flex cursor-pointer items-center gap-2 text-sm text-gray-800">
                                                    <input type="radio" name="incident" value="none" x-model="incident" class="text-emerald-600 focus:ring-emerald-500">
                                                    Tidak ada, pekerjaan selesai dengan aman
                                                </label>
                                                <label class="flex cursor-pointer items-center gap-2 text-sm text-gray-800">
                                                    <input type="radio" name="incident" value="reported" x-model="incident" class="text-amber-600 focus:ring-amber-500">
                                                    Ada insiden / near miss
                                                </label>
                                            </div>
                                            <textarea x-show="incident === 'reported'" name="incident_note" rows="2" maxlength="1000" class="{{ $input }} mt-2"
                                                      style="{{ old('incident') === 'reported' ? '' : 'display: none;' }}"
                                                      placeholder="Jelaskan singkat kejadiannya">{{ old('incident_note') }}</textarea>
                                            <p class="mt-2 text-xs text-gray-500">Sertifikat Penyelesaian terbit otomatis setelah permit ditutup.{{ '' }} Kalau ada insiden, sertifikat diberi tanda "dengan catatan".</p>
                                        </div>
                                    @endif

                                    @if ($step['next'] === \App\Models\WorkPermit::STATUS_ACTIVE)
                                        <p class="rounded-lg bg-emerald-50 px-3 py-2 text-xs text-emerald-800">
                                            Setelah di-approve QHSE, permit <b>berlaku</b>. Pengaju tetap wajib menekan "Aktifkan Hari Ini" setiap hari kerja sebelum mulai.
                                        </p>
                                    @endif

                                    <div>
                                        <label class="{{ $label }}">Catatan / syarat tambahan <span class="font-normal text-gray-400">(opsional)</span></label>
                                        <textarea name="note" rows="2" maxlength="1000" class="{{ $input }}" placeholder="{{ $permit->text('approve_note') }}">{{ old('note') }}</textarea>
                                    </div>

                                    <button type="submit" :disabled="busy"
                                            class="w-full rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 disabled:opacity-60">
                                        {{ $step['button'] }}
                                    </button>
                                    <p class="text-center text-[11px] text-gray-400">Tercatat sebagai tanda tangan digital atas nama {{ auth()->user()->name }}.</p>
                                </form>

                                {{-- Kembalikan --}}
                                <form x-show="tab === 'return'" method="POST" action="{{ route('permit.return', $permit) }}"
                                      @submit="busy = true" class="space-y-4 px-5 py-4" style="{{ $errors->has('return_note') ? '' : 'display: none;' }}">
                                    @csrf
                                    @method('PUT')
                                    <div>
                                        <label class="{{ $label }}">Yang perlu diperbaiki <span class="text-red-500">*</span></label>
                                        <textarea name="return_note" rows="3" required maxlength="1000" class="{{ $input }}"
                                                  placeholder="{{ $isClose ? $permit->text('return_close_note') : $permit->text('return_note') }}">{{ old('return_note') }}</textarea>
                                        <p class="mt-1 text-xs text-gray-500">{{ $isClose ? 'Permit kembali Aktif dan Spv harus mengulang close-out.' : 'Permit kembali ke pengaju untuk direvisi dan diajukan ulang.' }}</p>
                                    </div>
                                    <button type="submit" :disabled="busy"
                                            class="w-full rounded-lg bg-orange-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-orange-600 disabled:opacity-60">
                                        Kembalikan ke Pengaju
                                    </button>
                                </form>
                            </section>
                        @else
                            <section class="rounded-xl border border-gray-200 bg-gray-50 px-5 py-4 text-sm text-gray-600">
                                Menunggu tindakan <b>{{ $step['who'] === 'qhse' ? 'QHSE' : $checkerHint }}</b>.
                            </section>
                        @endif

                        @endif

                        @if ($noSuperior)
                            <section class="rounded-xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800">
                                ⚠️ Belum ada akun <b>{{ $checkerHint }}</b> yang bisa memeriksa permit ini.
                                Atur jabatan di Data User, atau superadmin dapat memproses langkah ini.
                            </section>
                        @endif
                    </x-slot:actions>
                @endif
            </x-work-permit.detail>
        </div>
    </div>
</x-app-layout>