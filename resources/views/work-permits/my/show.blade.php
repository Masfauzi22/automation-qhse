@php
    use App\Models\WorkPermit;
    $input = 'block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500';
    $label = 'block text-xs font-semibold text-gray-700 mb-1';
    $todayDay = $permit->dayNumberFor(today());
    $closeErrors = $errors->hasAny(['work_finished_at', 'post_watch_until', 'final_condition', 'closeout_photos', 'closeout_photos.*', 'closeout_confirm']);
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <a href="{{ route('my-permit.index') }}" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700" title="Kembali">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Detail Permit to Work</h2>
            </div>
            @if ($permit->isEditable())
                <a href="{{ route('my-permit.edit', $permit) }}"
                   class="inline-flex items-center justify-center gap-2 rounded-lg bg-orange-500 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-orange-600">
                    Revisi & Ajukan Ulang
                </a>
            @endif
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
                @if ($isApplicant && in_array($permit->status, [WorkPermit::STATUS_ACTIVE, WorkPermit::STATUS_EXPIRED], true))
                    <x-slot:actions>

                        {{-- ============ AKTIFKAN HARI INI ============ --}}
                        @if ($permit->status === WorkPermit::STATUS_ACTIVE)
                            @if (! $activationBlocker)
                                <section class="bg-white rounded-xl border-2 border-emerald-300 shadow-sm" x-data="photoPicker({{ $maxPhotos }}, {{ $maxPhotoKb }})">
                                    <div class="border-b border-gray-100 px-5 py-4">
                                        <h4 class="text-sm font-bold text-gray-900">Aktifkan Hari Ini · Day {{ $todayDay }}</h4>
                                        <p class="text-xs text-gray-500">{{ today()->translatedFormat('l, d F Y') }} · jam {{ substr((string) $permit->start_time, 0, 5) }}–{{ substr((string) $permit->end_time, 0, 5) }}</p>
                                    </div>
                                    <form method="POST" action="{{ route('my-permit.activate', $permit) }}" enctype="multipart/form-data"
                                          x-data="{ busy: false }" @submit="busy = true" class="space-y-3 px-5 py-4">
                                        @csrf
                                        @method('PUT')
                                        <p class="text-xs font-semibold text-gray-700">Konfirmasi sebelum mulai bekerja:</p>
                                        @foreach ($dailyConfirmations as $key => $text)
                                            <label class="flex cursor-pointer items-start gap-2 text-sm text-gray-800">
                                                <input type="checkbox" name="daily_confirm[{{ $key }}]" value="1" required
                                                       class="mt-0.5 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                                <span>{{ $text }}</span>
                                            </label>
                                        @endforeach

                                        {{-- Ruang Terbatas: ukur gas ulang tepat sebelum pekerja masuk --}}
                                        @if ($dailyGasTest)
                                            <div class="rounded-lg border border-violet-200 bg-violet-50/50 p-3">
                                                <p class="text-xs font-bold text-violet-900">Hasil ukur gas sekarang <span class="text-red-500">*</span></p>
                                                <p class="text-[11px] text-violet-800">Ukur tepat sebelum pekerja masuk. Kalau ada yang di luar batas, sistem menolak aktivasi.</p>
                                                <div class="mt-2 grid grid-cols-2 gap-2">
                                                    @foreach ($gasLimits as $gas => $limit)
                                                        <div>
                                                            <label class="block text-[11px] font-semibold text-gray-700">{{ $limit['label'] }} ({{ $limit['unit'] }})</label>
                                                            <input type="number" step="0.01" min="0" name="daily_gas[{{ $gas }}]" required value="{{ old("daily_gas.{$gas}") }}" class="{{ $input }} mt-0.5">
                                                            <p class="text-[10px] text-gray-500">Aman: {{ $limit['min'] !== null ? $limit['min'] . '–' : '≤ ' }}{{ $limit['max'] }} {{ $limit['unit'] }}</p>
                                                        </div>
                                                    @endforeach
                                                </div>
                                                <div class="mt-2">
                                                    <label class="block text-[11px] font-semibold text-gray-700">Diukur oleh</label>
                                                    <input type="text" name="daily_gas[tested_by_name]" required maxlength="150" value="{{ old('daily_gas.tested_by_name') }}" class="{{ $input }} mt-0.5">
                                                </div>
                                                @error('daily_gas') <p class="mt-1 text-xs font-bold text-red-600">{{ $message }}</p> @enderror
                                            </div>
                                        @endif

                                        <div>
                                            <label class="{{ $label }}">Foto kondisi area hari ini <span class="font-normal text-gray-400">(disarankan)</span></label>
                                            <input type="file" name="daily_photos[]" multiple accept="image/jpeg,image/png,image/webp" @change="pick($event)"
                                                   class="block w-full text-xs text-gray-600 file:mr-2 file:rounded-md file:border-0 file:bg-emerald-50 file:px-2.5 file:py-1.5 file:text-xs file:font-semibold file:text-emerald-700">
                                            <p x-show="compressing" class="mt-1 text-xs font-medium text-blue-600" style="display: none;">Mengompres foto...</p>
                                            <p x-show="info" x-text="info" class="mt-1 text-xs text-emerald-700" style="display: none;"></p>
                                            <p x-show="error" x-text="error" class="mt-1 text-xs font-medium text-red-600" style="display: none;"></p>
                                            <div x-show="previews.length" class="mt-2 flex gap-2" style="display: none;">
                                                <template x-for="p in previews" :key="p.url">
                                                    <img :src="p.url" class="h-14 w-14 rounded-md border border-gray-200 object-cover">
                                                </template>
                                            </div>
                                        </div>

                                        <div>
                                            <label class="{{ $label }}">Catatan <span class="font-normal text-gray-400">(opsional)</span></label>
                                            <input type="text" name="daily_note" maxlength="500" class="{{ $input }}" placeholder="{{ $permit->text('daily_note') }}">
                                        </div>

                                        <button type="submit" :disabled="busy"
                                                class="w-full rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 disabled:opacity-60">
                                            Aktifkan Day {{ $todayDay }}
                                        </button>
                                    </form>
                                </section>
                            @else
                                <section class="rounded-xl border px-5 py-4 text-sm {{ $permit->dailyLogFor() ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-gray-200 bg-gray-50 text-gray-600' }}">
                                    @if ($log = $permit->dailyLogFor())
                                        <p class="font-bold">✓ Day {{ $log->day_number }} sudah aktif</p>
                                        <p class="text-xs">Diaktifkan {{ $log->user_name }} jam {{ $log->created_at->format('H:i') }}. Pekerjaan boleh dilakukan sampai jam {{ substr((string) $permit->end_time, 0, 5) }}.</p>
                                    @else
                                        <p>{{ $activationBlocker }}</p>
                                    @endif
                                </section>
                            @endif
                        @endif

                        {{-- ============ PEKERJAAN SELESAI / CLOSE-OUT ============ --}}
                        @if ($canFinish)
                            <section class="bg-white rounded-xl border border-indigo-200 shadow-sm"
                                     x-data="{ open: {{ $closeErrors || $permit->status === WorkPermit::STATUS_EXPIRED ? 'true' : 'false' }} }">
                                <div class="px-5 py-4">
                                    <h4 class="text-sm font-bold text-gray-900">Pekerjaan Selesai (Close-out)</h4>
                                    <p class="text-xs text-gray-500">
                                        @if ($postWatchMinutes > 0)
                                            Isi setelah pekerjaan selesai <b>dan</b> pengawasan {{ $postWatchMinutes }} menit setelahnya berakhir.
                                        @else
                                            Isi setelah pekerjaan selesai dan area sudah dirapikan.
                                        @endif
                                    </p>
                                    <button type="button" x-show="! open" @click="open = true"
                                            class="mt-3 w-full rounded-lg border border-indigo-300 px-4 py-2 text-sm font-semibold text-indigo-700 hover:bg-indigo-50">Isi Close-out</button>
                                </div>
                                <form x-show="open" method="POST" action="{{ route('my-permit.finish', $permit) }}" enctype="multipart/form-data"
                                      x-data="{ busy: false, ...photoPicker({{ $maxPhotos }}, {{ $maxPhotoKb }}) }" @submit="busy = true"
                                      class="space-y-3 border-t border-gray-100 px-5 py-4" style="{{ $closeErrors || $permit->status === WorkPermit::STATUS_EXPIRED ? '' : 'display: none;' }}">
                                    @csrf
                                    @method('PUT')
                                    <div class="grid grid-cols-1 gap-3">
                                        <div>
                                            <label class="{{ $label }}">Jam pekerjaan selesai <span class="text-red-500">*</span></label>
                                            <input type="datetime-local" name="work_finished_at" required class="{{ $input }}" value="{{ old('work_finished_at') }}" max="{{ now()->format('Y-m-d\TH:i') }}">
                                        </div>
                                        @if ($postWatchMinutes > 0)
                                            <div>
                                                <label class="{{ $label }}">Jam pengawasan berakhir <span class="text-red-500">*</span></label>
                                                <input type="datetime-local" name="post_watch_until" required class="{{ $input }}" value="{{ old('post_watch_until') }}" max="{{ now()->format('Y-m-d\TH:i') }}">
                                                <p class="mt-1 text-xs text-gray-500">Minimal {{ $postWatchMinutes }} menit setelah pekerjaan selesai.</p>
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <label class="{{ $label }}">Kondisi akhir area <span class="text-red-500">*</span></label>
                                        <textarea name="final_condition" rows="2" required maxlength="1000" class="{{ $input }}"
                                                  placeholder="{{ $permit->text('final_condition') }}">{{ old('final_condition') }}</textarea>
                                    </div>
                                    <div>
                                        <label class="{{ $label }}">Foto kondisi akhir <span class="text-red-500">*</span></label>
                                        <input type="file" name="closeout_photos[]" multiple required accept="image/jpeg,image/png,image/webp" @change="pick($event)"
                                               class="block w-full text-xs text-gray-600 file:mr-2 file:rounded-md file:border-0 file:bg-indigo-50 file:px-2.5 file:py-1.5 file:text-xs file:font-semibold file:text-indigo-700">
                                        <p x-show="compressing" class="mt-1 text-xs font-medium text-blue-600" style="display: none;">Mengompres foto...</p>
                                        <p x-show="info" x-text="info" class="mt-1 text-xs text-emerald-700" style="display: none;"></p>
                                        <p x-show="error" x-text="error" class="mt-1 text-xs font-medium text-red-600" style="display: none;"></p>
                                    </div>
                                    <label class="flex cursor-pointer items-start gap-2 text-sm text-gray-800">
                                        <input type="checkbox" name="closeout_confirm" value="1" required class="mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                        <span>{{ $permit->text('closeout_confirm', 'Area kerja sudah diperiksa dan dinyatakan aman.') }}</span>
                                    </label>
                                    <button type="submit" :disabled="busy"
                                            class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 disabled:opacity-60">
                                        Kirim Close-out
                                    </button>
                                </form>
                            </section>
                        @endif

                        {{-- ============ STOP WORK ============ --}}
                        @if ($canStop)
                            <x-work-permit.stop-form :permit="$permit" />
                        @endif
                    </x-slot:actions>
                @endif
            </x-work-permit.detail>
        </div>
    </div>
    <x-photo-picker-script />
</x-app-layout>