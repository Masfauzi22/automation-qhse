@php
    use App\Models\HazardReport;

    $input = 'block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500';
    $label = 'block text-xs font-semibold text-gray-700 mb-1';
    $maxMb = round($maxPhotoKb / 1024);

    $showValidate = $isHse && $report->status === HazardReport::STATUS_PENDING_VALIDATION;
    $showVerify   = $isHse && $report->status === HazardReport::STATUS_PENDING_VERIFICATION;
    $showClosing  = $canFollowUp; // PIC departemen terkait (atau superadmin), status Open
    $closedOnSite = (bool) $report->closing_submitted_at;

    $title = match (true) {
        $showValidate => 'Validasi Hazard Report',
        $showVerify   => 'Verifikasi Bukti Perbaikan',
        $showClosing  => 'Follow Up Hazard Report',
        default       => 'Detail Hazard Report',
    };
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('hazard-report.index') }}" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700" title="Kembali ke Tasklist">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $title }}</h2>
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
                <div class="rounded-r-lg border-l-4 border-red-500 bg-red-50 p-4">
                    <ul class="list-disc list-inside text-sm text-red-700 space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <x-hazard-report.detail :report="$report" :reporter-stats="$reporterStats">
                @if ($showValidate || $showVerify || $showClosing || $canRejectExtension)
                    <x-slot:actions>

                        {{-- ============ 1. VALIDASI HSE ============ --}}
                        @if ($showValidate)
                            <section class="bg-white rounded-xl border-2 border-emerald-200 shadow-sm"
                                     x-data="{
                                         tab: '{{ old('rejection_reason') ? 'reject' : 'validate' }}',
                                         reason: @js(old('rejection_reason', '')),
                                         dupOf: @js(old('duplicate_of_number', '')),
                                         busy: false,
                                         markDuplicate(number) { this.tab = 'reject'; this.reason = 'duplikat'; this.dupOf = number; },
                                     }">

                                {{-- Kemungkinan duplikat --}}
                                @if ($duplicateCandidates->isNotEmpty())
                                    <div class="border-b border-amber-200 bg-amber-50/60 px-5 py-4 rounded-t-xl">
                                        <div class="flex items-center gap-2">
                                            <svg class="h-4 w-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/></svg>
                                            <h4 class="text-sm font-bold text-amber-900">Kemungkinan Duplikat ({{ $duplicateCandidates->count() }})</h4>
                                        </div>
                                        <p class="mt-0.5 text-xs text-amber-800">Laporan ± {{ config('hazard.duplicate.window_days') }} hari yang mirip. Bandingkan dulu sebelum memvalidasi.</p>

                                        <div class="mt-3 space-y-2">
                                            @foreach ($duplicateCandidates as $dup)
                                                @php
                                                    [$levelText, $levelCls] = \App\Services\HazardDuplicateFinder::level($dup->score);
                                                    $dupReport = $dup->report;
                                                    $thumb = $dupReport->findingPhotos->first();
                                                @endphp
                                                <div class="rounded-lg border border-gray-200 bg-white p-3">
                                                    <div class="flex gap-3">
                                                        @if ($thumb)
                                                            <a href="{{ $thumb->url() }}" target="_blank" class="block h-14 w-14 shrink-0 overflow-hidden rounded-md border border-gray-200 bg-gray-100">
                                                                <img src="{{ $thumb->url() }}" alt="" loading="lazy" class="h-full w-full object-cover">
                                                            </a>
                                                        @endif
                                                        <div class="min-w-0 flex-1">
                                                            <div class="flex flex-wrap items-center gap-1.5">
                                                                <a href="{{ route('hazard-report.show', $dupReport) }}" target="_blank"
                                                                   class="text-sm font-semibold text-gray-900 hover:text-emerald-700 hover:underline">{{ $dupReport->report_number }}</a>
                                                                <span class="rounded-full border px-1.5 py-0.5 text-[10px] font-bold {{ $levelCls }}">{{ $levelText }} {{ $dup->score }}%</span>
                                                            </div>
                                                            <p class="text-[11px] text-gray-500">
                                                                {{ $dupReport->finding_date->translatedFormat('d M Y') }} · {{ $dupReport->reporter_name }} ·
                                                                <span class="font-medium">{{ $dupReport->statusLabel() }}</span>
                                                            </p>
                                                            <p class="mt-1 truncate text-xs text-gray-700" title="{{ $dupReport->location }}">📍 {{ $dupReport->location }}</p>
                                                            <p class="line-clamp-2 text-xs text-gray-600">{{ $dupReport->description }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="mt-2 flex flex-wrap gap-1">
                                                        @foreach ($dup->reasons as $why)
                                                            <span class="rounded bg-gray-100 px-1.5 py-0.5 text-[10px] font-medium text-gray-600">{{ $why }}</span>
                                                        @endforeach
                                                    </div>
                                                    <button type="button" @click="markDuplicate(@js($dupReport->report_number))"
                                                            class="mt-2 w-full rounded-md border border-red-200 px-2 py-1.5 text-xs font-semibold text-red-700 transition hover:bg-red-50">
                                                        Tolak sebagai duplikat dari laporan ini
                                                    </button>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <div class="border-b border-gray-100 px-5 pt-4">
                                    <h4 class="text-sm font-bold text-gray-900">Tindakan HSE</h4>
                                    <div class="mt-3 flex gap-1">
                                        <button type="button" @click="tab = 'validate'"
                                                :class="tab === 'validate' ? 'border-emerald-600 text-emerald-700' : 'border-transparent text-gray-500 hover:text-gray-800'"
                                                class="border-b-2 px-3 pb-2 text-sm font-semibold transition">Validasi</button>
                                        <button type="button" @click="tab = 'reject'"
                                                :class="tab === 'reject' ? 'border-red-600 text-red-700' : 'border-transparent text-gray-500 hover:text-gray-800'"
                                                class="border-b-2 px-3 pb-2 text-sm font-semibold transition">Tolak</button>
                                    </div>
                                </div>

                                <form x-show="tab === 'validate'" method="POST" action="{{ route('hazard-report.validate', $report) }}"
                                      @submit="busy = true" class="space-y-4 px-5 py-4" style="{{ old('rejection_reason') ? 'display: none;' : '' }}">
                                    @csrf
                                    @method('PUT')

                                    <p class="text-xs text-gray-500">Periksa dan koreksi data berikut bila perlu sebelum memvalidasi.</p>

                                    <div>
                                        <label class="{{ $label }}">Kategori Bahaya</label>
                                        <select name="hazard_category_id" required class="{{ $input }}">
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}" @selected(old('hazard_category_id', $report->hazard_category_id) == $category->id)>{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="{{ $label }}">Kategori Prioritas</label>
                                        <select name="hazard_priority_id" required class="{{ $input }}">
                                            @foreach ($priorities as $priority)
                                                <option value="{{ $priority->id }}" @selected(old('hazard_priority_id', $report->hazard_priority_id) == $priority->id)>
                                                    {{ $priority->name }} (follow up {{ $priority->sla_days }} hari)
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="{{ $label }}">Departemen PIC</label>
                                        <select name="pic_department_id" required class="{{ $input }}">
                                            @foreach ($departments as $dept)
                                                <option value="{{ $dept->id }}" @selected(old('pic_department_id', $report->pic_department_id) == $dept->id)>{{ $dept->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="{{ $label }}">Catatan HSE <span class="font-normal text-gray-400">(opsional)</span></label>
                                        <textarea name="validation_note" rows="2" maxlength="1000" class="{{ $input }}" placeholder="Instruksi untuk PIC, dll.">{{ old('validation_note') }}</textarea>
                                    </div>

                                    @if ($closedOnSite)
                                        <div class="rounded-lg bg-emerald-50 border border-emerald-200 px-3 py-2 text-xs text-emerald-800">
                                            Pelapor sudah memperbaiki di tempat dan melampirkan bukti closing. Laporan akan <b>langsung Closed</b> setelah divalidasi.
                                        </div>
                                    @else
                                        <div class="rounded-lg bg-gray-50 px-3 py-2 text-xs text-gray-600">
                                            Setelah divalidasi, laporan diteruskan ke PIC dengan batas waktu sesuai prioritas.
                                        </div>
                                    @endif

                                    <button type="submit" :disabled="busy"
                                            class="w-full rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 disabled:opacity-60">
                                        {{ $closedOnSite ? 'Validasi & Close' : 'Validasi & Teruskan ke PIC' }}
                                    </button>
                                </form>

                                <form x-show="tab === 'reject'" method="POST" action="{{ route('hazard-report.reject', $report) }}"
                                      @submit="if (! confirm('Tolak laporan {{ $report->report_number }}?')) { $event.preventDefault(); return; } busy = true"
                                      class="space-y-4 px-5 py-4" style="{{ old('rejection_reason') ? '' : 'display: none;' }}">
                                    @csrf
                                    @method('PUT')

                                    <div>
                                        <p class="{{ $label }}">Alasan Penolakan</p>
                                        <div class="space-y-2">
                                            @foreach ($rejectReasons as $key => $text)
                                                <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-gray-200 px-3 py-2 text-sm hover:bg-gray-50 has-[:checked]:border-red-300 has-[:checked]:bg-red-50">
                                                    <input type="radio" name="rejection_reason" value="{{ $key }}" x-model="reason"
                                                           class="border-gray-300 text-red-600 focus:ring-red-500">
                                                    {{ $text }}
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div x-show="reason === 'duplikat'" style="{{ old('rejection_reason') === 'duplikat' ? '' : 'display: none;' }}">
                                        <label class="{{ $label }}">Duplikat dari laporan <span class="text-red-500">*</span></label>
                                        <input type="text" name="duplicate_of_number" x-model="dupOf" list="duplicate-options" maxlength="50"
                                               :required="reason === 'duplikat'" class="{{ $input }}" placeholder="Contoh: HZR/2026/11/0003">
                                        <datalist id="duplicate-options">
                                            @foreach ($duplicateCandidates as $dup)
                                                <option value="{{ $dup->report->report_number }}">{{ $dup->score }}% · {{ $dup->report->location }}</option>
                                            @endforeach
                                        </datalist>
                                        <p class="mt-1 text-xs text-gray-500">Nomor laporan yang lebih dulu masuk. Pelapor akan melihat nomor ini.</p>
                                        @error('duplicate_of_number') <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label class="{{ $label }}">Catatan untuk pelapor <span class="font-normal text-gray-400">(opsional)</span></label>
                                        <textarea name="validation_note" rows="2" maxlength="1000" class="{{ $input }}" placeholder="Contoh: duplikat dengan HZR/2026/11/0003">{{ old('validation_note') }}</textarea>
                                    </div>

                                    <button type="submit" :disabled="busy"
                                            class="w-full rounded-lg bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700 disabled:opacity-60">
                                        Tolak Laporan
                                    </button>
                                </form>
                            </section>
                        @endif

                        {{-- ============ 2. FOLLOW UP PIC ============ --}}
                        @if ($showClosing)
                            @php
                                $extensionErrors = $errors->hasAny(['obstacle', 'temporary_action', 'new_due_date', 'progress_photos', 'progress_photos.*']);
                            @endphp
                            <section class="bg-white rounded-xl border-2 border-amber-200 shadow-sm"
                                     x-data="{ tab: '{{ $extensionErrors ? 'extend' : 'close' }}', busy: false }">
                                <div class="border-b border-gray-100 px-5 pt-4">
                                    <h4 class="text-sm font-bold text-gray-900">Tindak Lanjut PIC</h4>
                                    <p class="text-xs {{ $report->isOverdue() ? 'font-semibold text-red-600' : 'text-gray-500' }}">
                                        Batas waktu: {{ $report->due_date?->translatedFormat('d F Y') ?? '-' }}{{ $report->isOverdue() ? ' (terlambat)' : '' }}
                                    </p>
                                    <div class="mt-3 flex gap-1">
                                        <button type="button" @click="tab = 'close'"
                                                :class="tab === 'close' ? 'border-amber-500 text-amber-700' : 'border-transparent text-gray-500 hover:text-gray-800'"
                                                class="border-b-2 px-3 pb-2 text-sm font-semibold transition">Sudah Diperbaiki</button>
                                        <button type="button" @click="tab = 'extend'"
                                                :class="tab === 'extend' ? 'border-indigo-600 text-indigo-700' : 'border-transparent text-gray-500 hover:text-gray-800'"
                                                class="border-b-2 px-3 pb-2 text-sm font-semibold transition">Belum Bisa / Perpanjang</button>
                                    </div>
                                </div>

                                {{-- ---- Tab: perpanjangan ---- --}}
                                <div x-show="tab === 'extend'" style="{{ $extensionErrors ? '' : 'display: none;' }}">
                                    @if ($report->canExtend())
                                        <form method="POST" action="{{ route('hazard-report.extension', $report) }}" enctype="multipart/form-data"
                                              @submit="busy = true" class="space-y-4 px-5 py-4">
                                            @csrf
                                            @method('PUT')

                                            <p class="rounded-lg bg-indigo-50 px-3 py-2 text-xs text-indigo-800">
                                                Pakai ini kalau perbaikan butuh waktu (menunggu material, vendor, dll).
                                                Sisa perpanjangan: <b>{{ $maxExtensions - $report->extension_count }} dari {{ $maxExtensions }}</b>.
                                            </p>

                                            <div>
                                                <label class="{{ $label }}">Kendala <span class="text-red-500">*</span></label>
                                                <textarea name="obstacle" rows="2" required maxlength="1000" class="{{ $input }}"
                                                          placeholder="Contoh: masih menunggu pembelian obat pembasmi tawon">{{ old('obstacle') }}</textarea>
                                            </div>

                                            <div>
                                                <label class="{{ $label }}">Tindakan Sementara <span class="text-red-500">*</span></label>
                                                <textarea name="temporary_action" rows="2" required maxlength="1000" class="{{ $input }}"
                                                          placeholder="Contoh: area dipasang barikade dan rambu bahaya, akses dialihkan">{{ old('temporary_action') }}</textarea>
                                                <p class="mt-1 text-xs text-gray-500">Apa yang sudah dilakukan supaya area tetap aman sampai perbaikan selesai.</p>
                                            </div>

                                            <div x-data="photoPicker({{ $maxPhotos }}, {{ $maxPhotoKb }})">
                                                <label class="{{ $label }}">Foto Tindakan Sementara @if ($extensionPhotoRequired)<span class="text-red-500">*</span>@endif</label>
                                                <input type="file" name="progress_photos[]" multiple @if ($extensionPhotoRequired) required @endif accept="image/jpeg,image/png,image/webp" @change="pick($event)"
                                                       class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100">
                                                <p class="mt-1 text-xs text-gray-500">Contoh: foto barikade / rambu di area. Maksimal {{ $maxPhotos }} foto, masing-masing {{ $maxMb }} MB.</p>
                                                <p x-show="compressing" class="mt-1 text-xs font-medium text-blue-600" style="display: none;">Mengompres foto...</p>
                                                <p x-show="info" x-text="info" class="mt-1 text-xs text-emerald-700" style="display: none;"></p>
                                                <p x-show="error" x-text="error" class="mt-1 text-xs font-medium text-red-600" style="display: none;"></p>
                                                <div x-show="previews.length" class="mt-2 grid grid-cols-3 gap-2" style="display: none;">
                                                    <template x-for="p in previews" :key="p.url">
                                                        <div class="aspect-square overflow-hidden rounded-lg border border-gray-200 bg-gray-100">
                                                            <img :src="p.url" :alt="p.name" class="h-full w-full object-cover">
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>

                                            <div>
                                                <label class="{{ $label }}">Target Selesai Baru <span class="text-red-500">*</span></label>
                                                <input type="date" name="new_due_date" required class="{{ $input }}"
                                                       value="{{ old('new_due_date') }}"
                                                       min="{{ today()->addDay()->toDateString() }}"
                                                       max="{{ today()->addDays($maxExtensionDays)->toDateString() }}">
                                                <p class="mt-1 text-xs text-gray-500">Maksimal {{ $maxExtensionDays }} hari dari hari ini.</p>
                                            </div>

                                            <button type="submit" :disabled="busy"
                                                    class="w-full rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 disabled:opacity-60">
                                                <span x-text="busy ? 'Menyimpan...' : 'Simpan Progres & Perpanjang'">Simpan Progres & Perpanjang</span>
                                            </button>
                                        </form>
                                    @else
                                        <div class="px-5 py-4">
                                            <p class="rounded-lg bg-red-50 px-3 py-3 text-sm text-red-700">
                                                Batas perpanjangan sudah habis ({{ $maxExtensions }} kali). Selesaikan perbaikan lalu upload bukti, atau hubungi tim HSE.
                                            </p>
                                        </div>
                                    @endif
                                </div>

                                {{-- ---- Tab: bukti perbaikan ---- --}}
                                <form x-show="tab === 'close'" method="POST" action="{{ route('hazard-report.closing', $report) }}" enctype="multipart/form-data"
                                      @submit="busy = true" class="space-y-4 px-5 py-4" style="{{ $extensionErrors ? 'display: none;' : '' }}">
                                    @csrf
                                    @method('PUT')

                                    <div x-data="photoPicker({{ $maxPhotos }}, {{ $maxPhotoKb }})">
                                        <label class="{{ $label }}">Foto Setelah Diperbaiki <span class="text-red-500">*</span></label>
                                        <input type="file" name="closing_photos[]" multiple required accept="image/jpeg,image/png,image/webp" @change="pick($event)"
                                               class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-amber-50 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-amber-700 hover:file:bg-amber-100">
                                        <p class="mt-1 text-xs text-gray-500">Maksimal {{ $maxPhotos }} foto, masing-masing {{ $maxMb }} MB.</p>
                                        <p x-show="compressing" class="mt-1 text-xs font-medium text-blue-600" style="display: none;">Mengompres foto...</p>
                                        <p x-show="info" x-text="info" class="mt-1 text-xs text-emerald-700" style="display: none;"></p>
                                        <p x-show="error" x-text="error" class="mt-1 text-xs font-medium text-red-600" style="display: none;"></p>
                                        <div x-show="previews.length" class="mt-2 grid grid-cols-3 gap-2" style="display: none;">
                                            <template x-for="p in previews" :key="p.url">
                                                <div class="aspect-square overflow-hidden rounded-lg border border-gray-200 bg-gray-100">
                                                    <img :src="p.url" :alt="p.name" class="h-full w-full object-cover">
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="{{ $label }}">Perbaikan yang Dilakukan <span class="text-red-500">*</span></label>
                                        <textarea name="closing_note" rows="3" required maxlength="1000" class="{{ $input }}"
                                                  placeholder="Contoh: kabel terkelupas sudah diganti dan dirapikan dengan cable tray">{{ old('closing_note') }}</textarea>
                                    </div>

                                    <button type="submit" :disabled="busy"
                                            class="w-full rounded-lg bg-amber-500 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-600 disabled:opacity-60">
                                        <span x-text="busy ? 'Mengirim...' : 'Kirim ke HSE untuk Verifikasi'">Kirim ke HSE untuk Verifikasi</span>
                                    </button>
                                </form>
                            </section>
                        @endif

                        {{-- ============ 2b. HSE MENOLAK PERPANJANGAN ============ --}}
                        @if ($canRejectExtension)
                            @php $lastExt = $report->updates->last(); @endphp
                            <section class="bg-white rounded-xl border border-indigo-200 shadow-sm" x-data="{ open: {{ $errors->has('note') ? 'true' : 'false' }}, busy: false }">
                                <div class="px-5 py-4">
                                    <h4 class="text-sm font-bold text-gray-900">Perpanjangan dari PIC</h4>
                                    <p class="mt-1 text-xs text-gray-600">
                                        Batas digeser dari <b>{{ $lastExt->old_due_date?->translatedFormat('d M Y') }}</b>
                                        ke <b>{{ $lastExt->new_due_date->translatedFormat('d M Y') }}</b>.
                                        Perpanjangan langsung berlaku. Tolak kalau alasannya tidak bisa diterima.
                                    </p>
                                    <button type="button" x-show="! open" @click="open = true"
                                            class="mt-3 text-sm font-semibold text-red-600 hover:text-red-800">Tolak perpanjangan ini</button>
                                </div>
                                <form x-show="open" method="POST" action="{{ route('hazard-report.reject-extension', $report) }}"
                                      @submit="busy = true" class="space-y-3 border-t border-gray-100 px-5 py-4" style="{{ $errors->has('note') ? '' : 'display: none;' }}">
                                    @csrf
                                    @method('PUT')
                                    <div>
                                        <label class="{{ $label }}">Alasan ditolak <span class="text-red-500">*</span></label>
                                        <textarea name="note" rows="2" required maxlength="1000" class="{{ $input }}"
                                                  placeholder="Contoh: risiko tinggi, harus selesai sesuai batas awal">{{ old('note') }}</textarea>
                                        <p class="mt-1 text-xs text-gray-500">Batas waktu akan kembali ke {{ $lastExt->old_due_date?->translatedFormat('d M Y') }}.</p>
                                    </div>
                                    <button type="submit" :disabled="busy"
                                            class="w-full rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-60">Tolak Perpanjangan</button>
                                </form>
                            </section>
                        @endif

                        {{-- ============ 3. VERIFIKASI HSE ============ --}}
                        @if ($showVerify)
                            <section class="bg-white rounded-xl border-2 border-blue-200 shadow-sm"
                                     x-data="{ tab: '{{ $errors->has('verification_note') ? 'return' : 'approve' }}', busy: false }">
                                <div class="border-b border-gray-100 px-5 pt-4">
                                    <h4 class="text-sm font-bold text-gray-900">Verifikasi Bukti Perbaikan</h4>
                                    <p class="text-xs text-gray-500">Cek foto dan catatan perbaikan dari PIC di kolom kiri.</p>
                                    <div class="mt-3 flex gap-1">
                                        <button type="button" @click="tab = 'approve'"
                                                :class="tab === 'approve' ? 'border-emerald-600 text-emerald-700' : 'border-transparent text-gray-500 hover:text-gray-800'"
                                                class="border-b-2 px-3 pb-2 text-sm font-semibold transition">Setujui</button>
                                        <button type="button" @click="tab = 'return'"
                                                :class="tab === 'return' ? 'border-red-600 text-red-700' : 'border-transparent text-gray-500 hover:text-gray-800'"
                                                class="border-b-2 px-3 pb-2 text-sm font-semibold transition">Kembalikan</button>
                                    </div>
                                </div>

                                <form x-show="tab === 'approve'" method="POST" action="{{ route('hazard-report.approve-closing', $report) }}"
                                      @submit="busy = true" class="space-y-4 px-5 py-4">
                                    @csrf
                                    @method('PUT')
                                    <div>
                                        <label class="{{ $label }}">Catatan <span class="font-normal text-gray-400">(opsional)</span></label>
                                        <textarea name="verification_note" rows="2" maxlength="1000" class="{{ $input }}"></textarea>
                                    </div>
                                    <button type="submit" :disabled="busy"
                                            class="w-full rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 disabled:opacity-60">
                                        Setujui & Close
                                    </button>
                                </form>

                                <form x-show="tab === 'return'" method="POST" action="{{ route('hazard-report.return-closing', $report) }}"
                                      @submit="busy = true" class="space-y-4 px-5 py-4" style="display: none;">
                                    @csrf
                                    @method('PUT')
                                    <div>
                                        <label class="{{ $label }}">Alasan dikembalikan <span class="text-red-500">*</span></label>
                                        <textarea name="verification_note" rows="3" required maxlength="1000" class="{{ $input }}"
                                                  placeholder="Contoh: foto belum memperlihatkan area yang diperbaiki">{{ old('verification_note') }}</textarea>
                                        <p class="mt-1 text-xs text-gray-500">Bukti lama akan dihapus dan PIC harus upload ulang.</p>
                                    </div>
                                    <button type="submit" :disabled="busy"
                                            class="w-full rounded-lg bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700 disabled:opacity-60">
                                        Kembalikan ke PIC
                                    </button>
                                </form>
                            </section>
                        @endif

                    </x-slot:actions>
                @endif
            </x-hazard-report.detail>

        </div>
    </div>
    <x-photo-picker-script />
</x-app-layout>