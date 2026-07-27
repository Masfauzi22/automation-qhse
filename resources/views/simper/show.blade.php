<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <a href="{{ route('simper.index') }}" class="mr-4 p-2 rounded-full text-gray-400 hover:text-[#A9791F] hover:bg-[#F5EDD9] transition-all duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <div>
                    <h2 class="font-semibold text-xl text-gray-900 leading-tight">{{ __('Detail Data SIMPER') }}</h2>
                    <p class="text-sm text-gray-500 mt-1">Informasi lengkap perizinan, persetujuan atasan, dan otorisasi alat berat.</p>
                </div>
            </div>

            <div class="flex space-x-3">
                <button onclick="window.print()" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 transition-all duration-200 hidden sm:inline-flex">
                    <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Print
                </button>
                @hasanyrole('superadmin|qhse')
                <a href="{{ route('simper.edit', $simper->id) }}" class="inline-flex items-center px-4 py-2 bg-[#161B2E] border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#232B45] shadow-sm transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    Edit Data
                </a>
                @endhasanyrole
            </div>
        </div>
    </x-slot>

    <!-- Fonts: serif for identity, mono for credential numbers -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Spectral:ital,wght@0,500;0,600;1,500&family=IBM+Plex+Mono:wght@500;600&display=swap" rel="stylesheet">

    <style>
        :root {
            --ink: #161B2E;
            --steel: #5B6478;
            --paper: #F7F6F2;
            --line: #E8E5DC;
            --gold: #A9791F;
            --gold-soft: #F5EDD9;
            --green: #1F7A4D;
            --green-soft: #E7F4EC;
            --red: #B42318;
            --red-soft: #FCEAE8;
            --slate-soft: #EEF0F3;
        }
        .font-serif-credential { font-family: 'Spectral', Georgia, serif; }
        .font-mono-credential { font-family: 'IBM Plex Mono', ui-monospace, monospace; }

        @media print {
            body * { visibility: hidden; }
            #printable-area, #printable-area * { visibility: visible; }
            #printable-area { position: absolute; left: 0; top: 0; width: 100%; }
            .print-border-none { border: none !important; box-shadow: none !important; }
            .print-bg-transparent { background-color: transparent !important; }
            .seal-badge { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
        }
    </style>

    @php
        $status = $simper->status;
        $statusConfig = [
            'Active'  => ['label' => 'Aktif',   'ring' => 'var(--green)', 'tint' => 'var(--green-soft)', 'text' => 'var(--green)'],
            'Pending' => ['label' => 'Pending', 'ring' => 'var(--gold)',  'tint' => 'var(--gold-soft)',  'text' => 'var(--gold)'],
            'Expired' => ['label' => 'Expired', 'ring' => 'var(--red)',   'tint' => 'var(--red-soft)',   'text' => 'var(--red)'],
        ];
        $sc = $statusConfig[$status] ?? ['label' => 'Dicabut', 'ring' => 'var(--steel)', 'tint' => 'var(--slate-soft)', 'text' => 'var(--steel)'];

        // Perbaikan logika tanggal
        $issue = $simper->issue_date ? \Carbon\Carbon::parse($simper->issue_date)->startOfDay() : \Carbon\Carbon::now()->startOfDay();
        $expiry = $simper->valid_until ? \Carbon\Carbon::parse($simper->valid_until)->startOfDay() : \Carbon\Carbon::now()->addYear()->startOfDay();
        $today = \Carbon\Carbon::now()->startOfDay();
        
        $totalSpan = max($issue->diffInDays($expiry), 1);
        $daysLeft = (int) $today->diffInDays($expiry, false); 
        $elapsed = max($issue->diffInDays($today, false), 0);
        $elapsed = min($elapsed, $totalSpan);
        
        $progress = $expiry->isPast() ? 100 : round(($elapsed / $totalSpan) * 100);

        $savedEquipments = is_array($simper->equipments) ? $simper->equipments : (json_decode($simper->equipments, true) ?? []);
    @endphp

    <div class="py-10" id="printable-area" style="background:var(--paper);">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Alert Notifikasi Sukses -->
            @if (session('success'))
                <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-r-lg shadow-sm">
                    <p class="text-sm text-green-700 font-medium">{{ session('success') }}</p>
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border print-border-none relative" style="border-color:var(--line); box-shadow: 0 1px 2px rgba(22,27,46,0.04), 0 8px 24px -12px rgba(22,27,46,0.08);">

                <!-- Gold hairline crest at the very top -->
                <div class="h-[3px] w-full" style="background: linear-gradient(90deg, var(--gold) 0%, var(--gold-soft) 55%, transparent 100%);"></div>

                <!-- BAGIAN ATAS: Identitas -->
                <div class="p-6 md:p-8 border-b flex flex-col sm:flex-row items-start gap-6 relative" style="border-color:var(--line);">

                    <!-- Seal-style status badge -->
                    <div class="absolute top-6 right-6 md:top-8 md:right-8 seal-badge">
                        <div class="relative w-[64px] h-[64px] rounded-full flex flex-col items-center justify-center border-2 rotate-[6deg]"
                             style="border-color: {{ $sc['ring'] }}; background: {{ $sc['tint'] }};">
                            <div class="absolute inset-[4px] rounded-full border" style="border-color: {{ $sc['ring'] }}; opacity:0.4;"></div>
                            @if($status == 'Active')
                                <svg class="w-4 h-4" style="color:{{ $sc['text'] }};" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                            @elseif($status == 'Expired')
                                <svg class="w-4 h-4" style="color:{{ $sc['text'] }};" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                            @else
                                <svg class="w-4 h-4" style="color:{{ $sc['text'] }};" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 8v4l3 3"></path></svg>
                            @endif
                            <span class="text-[9px] font-bold uppercase tracking-wider mt-0.5" style="color:{{ $sc['text'] }};">{{ $sc['label'] }}</span>
                        </div>
                    </div>

                    <!-- Pas Foto -->
                    <div class="shrink-0 h-32 w-24 rounded-lg overflow-hidden border-2 shadow-sm flex items-center justify-center" style="border-color: var(--gold-soft); background: var(--paper);">
                        @if($simper->photo_path)
                            <img src="{{ asset('storage/' . $simper->photo_path) }}" alt="Foto" class="h-full w-full object-cover">
                        @else
                            <span class="text-3xl font-serif-credential font-semibold" style="color:var(--steel);">{{ strtoupper(substr($simper->name, 0, 1)) }}</span>
                        @endif
                    </div>

                    <!-- Identitas -->
                    <div class="pt-1 w-full">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.15em] mb-1" style="color:var(--gold);">Pemegang Izin</p>
                        <h3 class="text-2xl font-serif-credential font-semibold pr-20" style="color:var(--ink);">{{ $simper->name }}</h3>
                        <p class="font-medium mt-1" style="color:var(--steel);">{{ $simper->position }}</p>

                        <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 text-sm">
                            <div>
                                <span class="block text-xs uppercase tracking-wide" style="color:var(--steel);">Perusahaan</span>
                                <span class="font-semibold" style="color:var(--ink);">{{ $simper->company }}</span>
                            </div>
                            <div>
                                <span class="block text-xs uppercase tracking-wide" style="color:var(--steel);">Departemen</span>
                                <span class="font-semibold" style="color:var(--ink);">{{ $simper->department }}</span>
                            </div>
                            <div>
                                <span class="block text-xs uppercase tracking-wide" style="color:var(--steel);">Golongan Darah</span>
                                <span class="inline-block font-bold px-2 py-0.5 rounded mt-0.5" style="color:var(--red); background:var(--red-soft);">{{ $simper->blood_type }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- BAGIAN TENGAH 1: STATUS APPROVAL PIC DEPT (ATASAN) -->
                <div class="p-6 md:p-8 border-b" style="border-color:var(--line); background:#FCFCFA;">
                    <h4 class="text-[11px] font-semibold uppercase tracking-[0.15em] mb-4 flex items-center" style="color:var(--steel);">
                        <span class="w-6 h-px mr-3" style="background:var(--gold);"></span>
                        Status Persetujuan Atasan (PIC Dept)
                    </h4>

                    <div class="bg-white border rounded-xl p-5 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4" style="border-color:var(--line);">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs uppercase tracking-wide text-gray-500">Status ACC:</span>
                                <span class="px-2.5 py-0.5 rounded text-xs font-bold 
                                    {{ $simper->pic_status == 'Approved' ? 'bg-green-100 text-green-800' : ($simper->pic_status == 'Rejected' ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800') }}">
                                    {{ $simper->pic_status == 'Approved' ? 'Disetujui' : ($simper->pic_status == 'Rejected' ? 'Ditolak' : 'Menunggu Persetujuan') }}
                                </span>
                            </div>
                            @if($simper->pic)
                                <p class="text-xs text-gray-500 mt-1">Ditinjau oleh: <span class="font-semibold text-gray-800">{{ $simper->pic->name }}</span></p>
                            @endif
                            @if($simper->pic_note)
                                <p class="text-xs text-gray-600 mt-2 italic bg-gray-50 p-2 rounded border border-gray-100">Catatan: "{{ $simper->pic_note }}"</p>
                            @endif
                        </div>

                        <!-- Tombol Aksi Approval untuk PIC Dept / Superadmin / QHSE -->
                        @hasanyrole('superadmin|qhse|pic_dept')
                        <div x-data="{ openModal: false }">
                            <button @click="openModal = true" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 transition-all duration-200">
                                Ubah Approval PIC
                            </button>

                            <!-- Modal Popup ACC Atasan -->
                            <div x-show="openModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" style="display: none;">
                                <div class="bg-white p-6 rounded-2xl w-full max-w-md shadow-xl border border-gray-100">
                                    <h3 class="text-lg font-bold text-gray-900 mb-4">Form Persetujuan PIC Dept</h3>
                                    <form action="{{ route('simper.pic-approval', $simper->id) }}" method="POST" class="space-y-4">
                                        @csrf
                                        @method('PUT')
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Keputusan</label>
                                            <select name="pic_status" class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                                <option value="Approved" {{ $simper->pic_status == 'Approved' ? 'selected' : '' }}>Setujui (Approved)</option>
                                                <option value="Rejected" {{ $simper->pic_status == 'Rejected' ? 'selected' : '' }}>Tolak (Rejected)</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Catatan / Alasan</label>
                                            <textarea name="pic_note" rows="3" class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Tulis catatan atau instruksi jika ada...">{{ $simper->pic_note }}</textarea>
                                        </div>
                                        <div class="flex justify-end gap-2 pt-2">
                                            <button type="button" @click="openModal = false" class="px-4 py-2 bg-gray-100 text-gray-700 text-xs font-bold rounded-lg hover:bg-gray-200">Batal</button>
                                            <button type="submit" class="px-4 py-2 bg-[#161B2E] text-white text-xs font-bold rounded-lg hover:bg-[#232B45]">Simpan Keputusan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endhasanyrole
                    </div>
                </div>

                <!-- BAGIAN TENGAH 2: Perizinan -->
                <div class="p-6 md:p-8 print-bg-transparent border-b" style="border-color:var(--line);">
                    <h4 class="text-[11px] font-semibold uppercase tracking-[0.15em] mb-6 flex items-center" style="color:var(--steel);">
                        <span class="w-6 h-px mr-3" style="background:var(--gold);"></span>
                        Rincian Izin Mengemudi
                    </h4>

                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
                        <div>
                            <p class="text-xs mb-1" style="color:var(--steel);">Nomor Registrasi SIMPER</p>
                            <p class="text-sm font-semibold font-mono-credential" style="color:var(--ink);">{{ $simper->simper_number ?? 'Belum diterbitkan' }}</p>
                        </div>
                        <div>
                            <p class="text-xs mb-1" style="color:var(--steel);">NRP / NIK</p>
                            <p class="text-sm font-semibold font-mono-credential" style="color:var(--ink);">{{ $simper->nrp ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-xs mb-1" style="color:var(--steel);">SIM Polisi Nasional</p>
                            <p class="text-sm font-semibold" style="color:var(--ink);">Tipe {{ $simper->police_license }}</p>
                        </div>
                        <div>
                            <p class="text-xs mb-1" style="color:var(--steel);">Area Operasional</p>
                            <p class="text-sm font-semibold" style="color:var(--ink);">{{ $simper->authorized_area ?? '-' }}</p>
                        </div>
                    </div>

                    <!-- Validity timeline -->
                    <div class="mt-6 bg-white border rounded-xl p-5 shadow-sm" style="border-color:var(--line);">
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <p class="text-xs mb-0.5" style="color:var(--steel);">Tanggal Terbit</p>
                                <p class="text-sm font-semibold" style="color:var(--ink);">{{ $simper->issue_date ? $issue->translatedFormat('d F Y') : '-' }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs mb-0.5" style="color:var(--steel);">Berlaku Sampai</p>
                                <p class="text-sm font-semibold" style="color: {{ $simper->valid_until && $expiry->isPast() ? 'var(--red)' : 'var(--ink)' }};">
                                    {{ $simper->valid_until ? $expiry->translatedFormat('d F Y') : '-' }}
                                </p>
                            </div>
                        </div>
                        <div class="relative h-1.5 rounded-full" style="background:var(--slate-soft);">
                            <div class="absolute inset-y-0 left-0 rounded-full" style="width: {{ $simper->valid_until ? $progress : 0 }}%; background: {{ $simper->valid_until && $expiry->isPast() ? 'var(--red)' : 'var(--gold)' }};"></div>
                        </div>
                        <p class="text-xs mt-2" style="color:var(--steel);">
                            @if($simper->valid_until)
                                @if($expiry->isPast())
                                    Masa berlaku telah berakhir <span class="font-bold" style="color:var(--red);">{{ abs($daysLeft) }} hari yang lalu</span>.
                                @else
                                    Sisa masa berlaku: <span class="font-bold" style="color:var(--ink);">{{ $daysLeft }} hari</span>.
                                @endif
                            @else
                                Masa berlaku belum ditentukan oleh QHSE.
                            @endif
                        </p>
                    </div>

                    <!-- Hasil Evaluasi / Ujian -->
                    <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="bg-white border rounded-xl p-4 shadow-sm" style="border-color:var(--line);">
                            <span class="text-xs text-gray-500 block mb-1">Nilai Tes Tulis</span>
                            <span class="font-bold text-lg font-mono-credential text-gray-900">{{ $simper->written_test_score !== null ? $simper->written_test_score : 'Belum Ujian' }}</span>
                        </div>
                        <div class="bg-white border rounded-xl p-4 shadow-sm" style="border-color:var(--line);">
                            <span class="text-xs text-gray-500 block mb-1">Status Tes Praktek / Berkendara</span>
                            <span class="font-bold text-lg text-gray-900">{{ $simper->practical_test_status ?? 'Belum Ujian' }}</span>
                        </div>
                    </div>
                </div>

                <!-- BAGIAN BAWAH: Otorisasi Alat Berat -->
                <div class="p-6 md:p-8">
                    <h4 class="text-[11px] font-semibold uppercase tracking-[0.15em] mb-5 flex items-center" style="color:var(--steel);">
                        <span class="w-6 h-px mr-3" style="background:var(--gold);"></span>
                        Otorisasi Pengoperasian Unit (Alat Berat)
                    </h4>

                    @if(count($savedEquipments) > 0)
                        <div class="border rounded-xl divide-y overflow-hidden" style="border-color:var(--line);">
                            @foreach($savedEquipments as $index => $eq)
                                <div class="flex items-center px-4 py-3 {{ $index % 2 === 0 ? 'bg-white' : '' }}" style="{{ $index % 2 !== 0 ? 'background:#FCFCFA;' : '' }} {{ !$loop->last ? 'border-color:var(--line);' : '' }}">
                                    <span class="w-6 text-xs font-mono-credential mr-3" style="color:var(--steel);">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
                                    <span class="flex items-center justify-center w-5 h-5 rounded-full mr-3 shrink-0" style="background:var(--gold-soft);">
                                        <svg class="w-3 h-3" style="color:var(--gold);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                    </span>
                                    <span class="text-sm font-medium" style="color:var(--ink);">{{ $eq }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="border border-dashed rounded-lg p-6 text-center" style="border-color:var(--line); background:var(--paper);">
                            <p class="text-sm" style="color:var(--steel);">Tidak ada otorisasi alat berat yang tercatat.</p>
                        </div>
                    @endif

                    <!-- Catatan -->
                    <div class="mt-8 rounded-lg p-4 flex items-start border" style="background:var(--red-soft); border-color:#F3D2CD;">
                        <svg class="w-5 h-5 mr-3 mt-0.5 shrink-0" style="color:var(--red);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <p class="text-xs font-medium" style="color:var(--red);">Segera laporkan kepada QHSE section selambat-lambatnya 2 minggu sebelum masa berlaku SIMPER habis.</p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>