@php
    $input    = 'block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500';
    $label    = 'block text-sm font-semibold text-gray-700 mb-1.5';
    $hint     = 'mt-1 text-xs text-gray-500';
    $errorCls = 'mt-1 text-xs font-medium text-red-600';
    $maxMb    = round($maxPhotoKb / 1024);
    $profileIncomplete = ! $user->employee_number || ! $user->company || ! $user->department;
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-red-50 text-red-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3h.008M10.29 3.86L1.82 18a1.5 1.5 0 001.29 2.25h17.78a1.5 1.5 0 001.29-2.25L13.71 3.86a1.5 1.5 0 00-2.42 0z"/></svg>
            </span>
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Lapor Hazard</h2>
                <p class="text-xs text-gray-500">Laporkan kondisi atau tindakan tidak aman yang kamu temukan.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            @if ($errors->any())
                <div class="mb-6 rounded-lg border-l-4 border-red-500 bg-red-50 p-4">
                    <p class="text-sm font-semibold text-red-800">Laporan belum bisa dikirim. Periksa kembali isian berikut:</p>
                    <ul class="mt-2 list-disc list-inside text-sm text-red-700 space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('my-hazard-report.store') }}" method="POST" enctype="multipart/form-data"
                  x-data="{ submitting: false, closedOnSite: {{ old('is_closed_on_site') ? 'true' : 'false' }} }"
                  @submit="submitting = true"
                  class="space-y-6">
                @csrf

                <!-- 1. Data Pelapor -->
                <section class="bg-white rounded-xl border border-gray-100 shadow-sm">
                    <div class="border-b border-gray-100 px-6 py-4">
                        <h3 class="text-sm font-bold text-gray-900">1. Data Pelapor</h3>
                        <p class="text-xs text-gray-500">Terisi otomatis dari akun kamu.</p>
                    </div>
                    <div class="px-6 py-5">
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">Nama</dt>
                                <dd class="mt-0.5 font-medium text-gray-900">{{ $user->name }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">ID Karyawan</dt>
                                <dd class="mt-0.5 font-medium {{ $user->employee_number ? 'text-gray-900' : 'italic text-amber-600' }}">{{ $user->employee_number ?? 'Belum diisi' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">Perusahaan</dt>
                                <dd class="mt-0.5 font-medium {{ $user->company ? 'text-gray-900' : 'italic text-amber-600' }}">{{ $user->company->name ?? 'Belum diisi' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-semibold uppercase tracking-wider text-gray-400">Departemen</dt>
                                <dd class="mt-0.5 font-medium {{ $user->department ? 'text-gray-900' : 'italic text-amber-600' }}">{{ $user->department->name ?? 'Belum diisi' }}</dd>
                            </div>
                        </dl>

                        {{-- Jumlah laporan pelapor --}}
                        <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-2 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                            <span>Ini akan jadi laporan ke-<b>{{ $stats['this_month'] + 1 }}</b> kamu bulan ini</span>
                            <span class="text-emerald-700">· total {{ $stats['total'] }} laporan sejak awal</span>
                            @if ($stats['target'] > 0)
                                <span class="ml-auto rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $stats['target_met'] ? 'bg-emerald-600 text-white' : 'bg-white text-emerald-800 border border-emerald-200' }}">
                                    Target bulan ini: {{ $stats['this_month_counted'] }} / {{ $stats['target'] }}
                                </span>
                            @endif
                        </div>

                        @if ($profileIncomplete)
                            <div class="mt-4 rounded-lg bg-amber-50 border border-amber-200 px-4 py-3 text-xs text-amber-800">
                                Data akun kamu belum lengkap. Laporan tetap bisa dikirim, tapi minta admin melengkapi ID karyawan, perusahaan, dan departemen supaya rekap laporan akurat.
                            </div>
                        @endif
                    </div>
                </section>

                <!-- 2. Detail Temuan -->
                <section class="bg-white rounded-xl border border-gray-100 shadow-sm">
                    <div class="border-b border-gray-100 px-6 py-4">
                        <h3 class="text-sm font-bold text-gray-900">2. Detail Temuan</h3>
                    </div>
                    <div class="px-6 py-5 grid grid-cols-1 sm:grid-cols-2 gap-5">

                        <div>
                            <label for="finding_date" class="{{ $label }}">Tanggal Temuan <span class="text-red-500">*</span></label>
                            <input type="date" id="finding_date" name="finding_date" required
                                   value="{{ old('finding_date', today()->toDateString()) }}"
                                   min="{{ $minDate->toDateString() }}" max="{{ today()->toDateString() }}"
                                   class="{{ $input }}">
                            <p class="{{ $hint }}">Boleh tanggal yang sudah lewat, maksimal {{ config('hazard.max_backdate_days', 7) }} hari ke belakang.</p>
                            @error('finding_date') <p class="{{ $errorCls }}">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="hazard_category_id" class="{{ $label }}">Kategori Bahaya <span class="text-red-500">*</span></label>
                            <select id="hazard_category_id" name="hazard_category_id" required class="{{ $input }}">
                                <option value="">-- Pilih kategori --</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected(old('hazard_category_id') == $category->id)>{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('hazard_category_id') <p class="{{ $errorCls }}">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="hazard_priority_id" class="{{ $label }}">Kategori Prioritas <span class="text-red-500">*</span></label>
                            <select id="hazard_priority_id" name="hazard_priority_id" required class="{{ $input }}">
                                <option value="">-- Pilih prioritas --</option>
                                @foreach ($priorities as $priority)
                                    <option value="{{ $priority->id }}" data-color="{{ $priority->markerColor() }}" data-speed="{{ $priority->pulseSpeed() }}" @selected(old('hazard_priority_id') == $priority->id)>
                                        {{ $priority->name }} (follow up {{ $priority->sla_days }} hari)
                                    </option>
                                @endforeach
                            </select>
                            @error('hazard_priority_id') <p class="{{ $errorCls }}">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="location" class="{{ $label }}">Lokasi Temuan <span class="text-red-500">*</span></label>
                            <input type="text" id="location" name="location" required maxlength="255"
                                   value="{{ old('location') }}" placeholder="Contoh: Gudang B, rak 3, dekat pintu darurat"
                                   class="{{ $input }}">
                            <p class="{{ $hint }}">Tulis sespesifik mungkin supaya PIC mudah menemukan lokasinya.</p>
                            @error('location') <p class="{{ $errorCls }}">{{ $message }}</p> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label class="{{ $label }}">
                                Titik Lokasi di Peta
                                @if (config('hazard.map.require_coordinates'))
                                    <span class="text-red-500">*</span>
                                @else
                                    <span class="font-normal text-gray-400">(opsional, sangat disarankan)</span>
                                @endif
                            </label>
                            <x-hazard-report.map editable color-from="hazard_priority_id" :lat="old('latitude')" :lng="old('longitude')" :accuracy="old('location_accuracy')" />
                            <p class="{{ $hint }}">Supaya PIC dan HSE bisa langsung menemukan lokasinya. Ganti tampilan Satelit / Peta Jalan di pojok kanan atas peta.</p>
                            @error('latitude') <p class="{{ $errorCls }}">{{ $message }}</p> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="description" class="{{ $label }}">Deskripsi Temuan <span class="text-red-500">*</span></label>
                            <textarea id="description" name="description" rows="4" required maxlength="2000"
                                      placeholder="Jelaskan apa yang kamu lihat"
                                      class="{{ $input }}">{{ old('description') }}</textarea>
                            @error('description') <p class="{{ $errorCls }}">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </section>

                <!-- 3. Analisis -->
                <section class="bg-white rounded-xl border border-gray-100 shadow-sm">
                    <div class="border-b border-gray-100 px-6 py-4">
                        <h3 class="text-sm font-bold text-gray-900">3. Potensi Bahaya & Rekomendasi</h3>
                    </div>
                    <div class="px-6 py-5 grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label for="potential_hazard" class="{{ $label }}">Potensi Bahaya <span class="text-red-500">*</span></label>
                            <textarea id="potential_hazard" name="potential_hazard" rows="4" required maxlength="2000"
                                      placeholder="Apa yang bisa terjadi kalau dibiarkan?"
                                      class="{{ $input }}">{{ old('potential_hazard') }}</textarea>
                            @error('potential_hazard') <p class="{{ $errorCls }}">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="recommendation" class="{{ $label }}">Rekomendasi Perbaikan <span class="text-red-500">*</span></label>
                            <textarea id="recommendation" name="recommendation" rows="4" required maxlength="2000"
                                      placeholder="Apa yang sebaiknya dilakukan?"
                                      class="{{ $input }}">{{ old('recommendation') }}</textarea>
                            @error('recommendation') <p class="{{ $errorCls }}">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </section>

                <!-- 4. PIC & Bukti -->
                <section class="bg-white rounded-xl border border-gray-100 shadow-sm">
                    <div class="border-b border-gray-100 px-6 py-4">
                        <h3 class="text-sm font-bold text-gray-900">4. PIC Follow Up & Bukti</h3>
                    </div>
                    <div class="px-6 py-5 space-y-6">

                        <div class="sm:w-1/2">
                            <label for="pic_department_id" class="{{ $label }}">Departemen PIC Follow Up <span class="text-red-500">*</span></label>
                            <select id="pic_department_id" name="pic_department_id" required class="{{ $input }}">
                                <option value="">-- Pilih departemen --</option>
                                @foreach ($departments as $dept)
                                    <option value="{{ $dept->id }}" @selected(old('pic_department_id') == $dept->id)>{{ $dept->name }}</option>
                                @endforeach
                            </select>
                            <p class="{{ $hint }}">Departemen yang bertanggung jawab memperbaiki. HSE bisa mengoreksi saat validasi.</p>
                            @error('pic_department_id') <p class="{{ $errorCls }}">{{ $message }}</p> @enderror
                        </div>

                        <!-- Foto bukti temuan -->
                        <div x-data="photoPicker({{ $maxPhotos }}, {{ $maxPhotoKb }})">
                            <label class="{{ $label }}">Foto Bukti Temuan <span class="text-red-500">*</span></label>
                            <label class="relative flex cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 px-4 py-6 text-center transition hover:border-emerald-400 hover:bg-emerald-50/40">
                                <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z"/><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z"/></svg>
                                <span class="mt-2 text-sm font-medium text-gray-700">Klik untuk pilih atau ambil foto</span>
                                <span class="text-xs text-gray-500">Maksimal {{ $maxPhotos }} foto, masing-masing {{ $maxMb }} MB (JPG, PNG, WEBP)</span>
                                <input type="file" name="finding_photos[]" multiple accept="image/jpeg,image/png,image/webp" class="sr-only" @change="pick($event)">
                            </label>
                            <p x-show="compressing" class="mt-1 text-xs font-medium text-blue-600" style="display: none;">Mengompres foto...</p>
                            <p x-show="info" x-text="info" class="mt-1 text-xs text-emerald-700" style="display: none;"></p>
                            <p x-show="error" x-text="error" class="{{ $errorCls }}" style="display: none;"></p>
                            <div x-show="previews.length" class="mt-3 grid grid-cols-3 sm:grid-cols-4 gap-3" style="display: none;">
                                <template x-for="p in previews" :key="p.url">
                                    <div class="aspect-square overflow-hidden rounded-lg border border-gray-200 bg-gray-100">
                                        <img :src="p.url" :alt="p.name" class="h-full w-full object-cover">
                                    </div>
                                </template>
                            </div>
                            @error('finding_photos') <p class="{{ $errorCls }}">{{ $message }}</p> @enderror
                            @error('finding_photos.*') <p class="{{ $errorCls }}">{{ $message }}</p> @enderror
                        </div>

                        <!-- Langsung diperbaiki di tempat -->
                        <div class="rounded-lg border border-gray-200 p-4">
                            <input type="hidden" name="is_closed_on_site" value="0">
                            <label class="flex cursor-pointer items-start gap-3">
                                <input type="checkbox" name="is_closed_on_site" value="1" x-model="closedOnSite"
                                       class="mt-0.5 rounded border-gray-300 text-emerald-600 shadow-sm focus:ring-emerald-500">
                                <span>
                                    <span class="block text-sm font-semibold text-gray-800">Temuan sudah langsung diperbaiki di tempat</span>
                                    <span class="block text-xs text-gray-500">Centang kalau kamu sudah memperbaikinya sendiri. Laporan akan langsung Closed setelah divalidasi HSE.</span>
                                </span>
                            </label>

                            <div x-show="closedOnSite" x-transition class="mt-4 space-y-4 border-t border-gray-100 pt-4" style="display: none;">
                                <div x-data="photoPicker({{ $maxPhotos }}, {{ $maxPhotoKb }})">
                                    <label class="{{ $label }}">Foto Bukti Closing <span class="text-red-500">*</span></label>
                                    <input type="file" name="closing_photos[]" multiple accept="image/jpeg,image/png,image/webp" @change="pick($event)"
                                           class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-emerald-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-emerald-700 hover:file:bg-emerald-100">
                                    <p class="{{ $hint }}">Foto kondisi setelah diperbaiki. Maksimal {{ $maxPhotos }} foto, masing-masing {{ $maxMb }} MB.</p>
                                    <p x-show="compressing" class="mt-1 text-xs font-medium text-blue-600" style="display: none;">Mengompres foto...</p>
                                    <p x-show="info" x-text="info" class="mt-1 text-xs text-emerald-700" style="display: none;"></p>
                                    <p x-show="error" x-text="error" class="{{ $errorCls }}" style="display: none;"></p>
                                    <div x-show="previews.length" class="mt-3 grid grid-cols-3 sm:grid-cols-4 gap-3" style="display: none;">
                                        <template x-for="p in previews" :key="p.url">
                                            <div class="aspect-square overflow-hidden rounded-lg border border-gray-200 bg-gray-100">
                                                <img :src="p.url" :alt="p.name" class="h-full w-full object-cover">
                                            </div>
                                        </template>
                                    </div>
                                    @error('closing_photos') <p class="{{ $errorCls }}">{{ $message }}</p> @enderror
                                    @error('closing_photos.*') <p class="{{ $errorCls }}">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="closing_note" class="{{ $label }}">Catatan Perbaikan</label>
                                    <textarea id="closing_note" name="closing_note" rows="2" maxlength="1000"
                                              placeholder="Apa yang sudah dilakukan?"
                                              class="{{ $input }}">{{ old('closing_note') }}</textarea>
                                    @error('closing_note') <p class="{{ $errorCls }}">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>

                    </div>
                </section>

                <!-- Tombol -->
                <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-3">
                    <a href="{{ route('my-hazard-report.index') }}" class="text-center text-sm font-medium text-gray-500 hover:text-gray-700 px-4 py-2.5">Batal</a>
                    <button type="submit" :disabled="submitting"
                            class="inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 disabled:cursor-wait disabled:opacity-60">
                        <svg x-show="submitting" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24" style="display: none;"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
                        <span x-text="submitting ? 'Mengirim...' : 'Kirim Laporan'">Kirim Laporan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    <x-photo-picker-script />
</x-app-layout>