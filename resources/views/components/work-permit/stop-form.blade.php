{{-- Tombol Stop Work: dipakai di halaman pengaju & tasklist --}}
@props(['permit'])

<section class="bg-white rounded-xl border border-red-200 shadow-sm" x-data="{ open: {{ $errors->has('stop_reason') ? 'true' : 'false' }}, busy: false }">
    <div class="px-5 py-4">
        <h4 class="text-sm font-bold text-red-700">Stop Work</h4>
        <p class="text-xs text-gray-500">Hentikan pekerjaan kalau kondisi tidak aman ({{ $permit->text('stop_hint', 'mis. kondisi berubah menjadi berbahaya') }}).</p>
        <button type="button" x-show="! open" @click="open = true"
                class="mt-3 w-full rounded-lg border border-red-300 px-4 py-2 text-sm font-semibold text-red-700 hover:bg-red-50">Hentikan Pekerjaan</button>
    </div>
    <form x-show="open" method="POST" action="{{ route('permit.stop', $permit) }}" @submit="busy = true"
          class="space-y-3 border-t border-gray-100 px-5 py-4" style="{{ $errors->has('stop_reason') ? '' : 'display: none;' }}">
        @csrf
        @method('PUT')
        <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1">Alasan dihentikan <span class="text-red-500">*</span></label>
            <textarea name="stop_reason" rows="2" required maxlength="1000"
                      class="block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-red-500 focus:ring-red-500"
                      placeholder="{{ $permit->text('stop_example') }}">{{ old('stop_reason') }}</textarea>
            <p class="mt-1 text-xs text-gray-500">Pekerjaan hanya bisa dilanjutkan setelah diizinkan QHSE.</p>
        </div>
        <button type="submit" :disabled="busy"
                class="w-full rounded-lg bg-red-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-60">STOP WORK</button>
    </form>
</section>