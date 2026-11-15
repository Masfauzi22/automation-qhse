@php
    $s       = $hazard['summary'];
    $target  = $hazard['target_close'];
    $fmtNum  = fn ($v) => number_format($v, 0, ',', '.');
    $fmtPct  = fn ($v) => $v === null ? '–' : number_format($v, 1, ',', '.') . '%';

    // Periode yang sudah ada datanya, misal "Jan – Nov"
    $aktif   = $hazard['monthly']->where('diterima', '>', 0);
    $periode = $aktif->isEmpty() ? '-' : substr($aktif->first()['bulan'], 0, 3) . ' – ' . substr($aktif->last()['bulan'], 0, 3);

    // Bulan dengan tunggakan open terbanyak
    $bulanOpenTerbanyak = $hazard['monthly']->sortByDesc('open')->first();
    $selisihTarget      = $s['pct_close'] === null ? null : round($s['pct_close'] - $target, 1);
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard AQHSE') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Section 1: Ucapan Selamat Datang -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100 p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h3 class="text-2xl font-bold text-gray-900">Selamat datang, <span class="text-indigo-600">{{ Auth::user()->name ?? 'User' }}</span>! 👋</h3>
                    <p class="text-sm text-gray-500 mt-1">Berikut adalah ringkasan aktivitas Health, Safety & Environment (HSE) hari ini.</p>
                </div>
                <div class="text-left md:text-right bg-indigo-50 px-4 py-2 rounded-lg border border-indigo-100">
                    <p class="text-xs font-bold text-indigo-800 uppercase tracking-wider">Tanggal Hari Ini</p>
                    <p class="text-sm font-medium text-indigo-900">{{ now()->translatedFormat('l, d F Y') }}</p>
                </div>
            </div>

            <!-- Pengingat Safety Campaign (hanya tampil kalau ada yang belum diselesaikan) -->
            <x-safety-campaign-reminder />

            <!-- Section 2: Grid Kartu Statistik -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

                <!-- Kartu: Hazard Report (data asli) -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center gap-4 hover:shadow-md transition">
                    <div class="p-3 bg-red-50 text-red-600 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Open Hazard</p>
                        <h4 class="text-2xl font-bold text-gray-900">{{ $fmtNum($s['open']) }}</h4>
                    </div>
                </div>

                <!-- Kartu: Permit to Work (dummy) -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center gap-4 hover:shadow-md transition">
                    <div class="p-3 bg-amber-50 text-amber-600 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Active PTW</p>
                        <h4 class="text-2xl font-bold text-gray-900">8</h4>
                    </div>
                </div>

                <!-- Kartu: Environmental Sampling (dummy) -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center gap-4 hover:shadow-md transition">
                    <div class="p-3 bg-green-50 text-green-600 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Sampling Lab</p>
                        <h4 class="text-2xl font-bold text-gray-900">24</h4>
                    </div>
                </div>

                <!-- Kartu: Dokumen Pending (dummy) -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center gap-4 hover:shadow-md transition">
                    <div class="p-3 bg-blue-50 text-blue-600 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Doc. Pending</p>
                        <h4 class="text-2xl font-bold text-gray-900">5</h4>
                    </div>
                </div>

            </div>

            <!-- Section 3: Jalan Pintas & Tren Hazard -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Jalan Pintas -->
                <div class="lg:col-span-1 bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-bold text-gray-900 border-b border-gray-200 pb-2 mb-4">Jalan Pintas</h3>
                    <div class="space-y-3">
                        <a href="{{ route('env-sampling.index') }}" class="flex items-center justify-between p-3 bg-gray-50 hover:bg-indigo-50 border border-gray-100 rounded-lg transition group">
                            <span class="text-sm font-medium text-gray-700 group-hover:text-indigo-700">Input Data Sampling</span>
                            <span class="text-gray-400 group-hover:text-indigo-500">&rarr;</span>
                        </a>
                        <a href="{{ route('documents.index') }}" class="flex items-center justify-between p-3 bg-gray-50 hover:bg-indigo-50 border border-gray-100 rounded-lg transition group">
                            <span class="text-sm font-medium text-gray-700 group-hover:text-indigo-700">Upload Dokumen Baru</span>
                            <span class="text-gray-400 group-hover:text-indigo-500">&rarr;</span>
                        </a>
                        <a href="#" class="flex items-center justify-between p-3 bg-gray-50 hover:bg-indigo-50 border border-gray-100 rounded-lg transition group">
                            <span class="text-sm font-medium text-gray-700 group-hover:text-indigo-700">Lapor Hazard (Bahaya)</span>
                            <span class="text-gray-400 group-hover:text-indigo-500">&rarr;</span>
                        </a>
                    </div>
                </div>

                <!-- Tren Hazard Bulanan -->
                <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-bold text-gray-900">Tren Hazard Report {{ $hazard['tahun'] }}</h3>
                    <p class="text-sm text-gray-500">Batang = temuan diterima (hijau sudah close, merah masih open). Garis = persentase close.</p>
                    <div class="relative h-72 mt-4">
                        <canvas id="hazardTrendChart"></canvas>
                    </div>
                </div>

            </div>

            <!-- Section 4: Hazard Report -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-6">

                <div class="flex flex-col md:flex-row md:items-end justify-between gap-2 border-b border-gray-200 pb-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Hazard Report {{ $hazard['tahun'] }}</h3>
                        <p class="text-sm text-gray-500">Rekap temuan, progres follow up per departemen, dan jenis temuan.</p>
                    </div>
                    <span class="text-xs text-gray-400">Sumber: Google Sheet Hazard Report {{ $hazard['tahun'] }}</span>
                </div>

                <!-- KPI -->
                <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
                    <div class="rounded-lg border border-gray-100 bg-gray-50 p-4">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Temuan</p>
                        <p class="text-3xl font-bold text-gray-900 mt-1">{{ $fmtNum($s['diterima']) }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $periode }} {{ $hazard['tahun'] }}</p>
                    </div>
                    <div class="rounded-lg border border-emerald-100 bg-emerald-50 p-4">
                        <p class="text-xs font-semibold text-emerald-700 uppercase tracking-wider">Close</p>
                        <p class="text-3xl font-bold text-emerald-700 mt-1">{{ $fmtNum($s['close']) }}</p>
                        <p class="text-xs text-gray-500 mt-1">sudah ditindaklanjuti</p>
                    </div>
                    <div class="rounded-lg border border-red-100 bg-red-50 p-4">
                        <p class="text-xs font-semibold text-red-700 uppercase tracking-wider">Masih Open</p>
                        <p class="text-3xl font-bold text-red-600 mt-1">{{ $fmtNum($s['open']) }}</p>
                        @if ($bulanOpenTerbanyak && $bulanOpenTerbanyak['open'] > 0)
                            <p class="text-xs text-gray-500 mt-1">{{ $bulanOpenTerbanyak['open'] }} di antaranya dari {{ $bulanOpenTerbanyak['bulan'] }}</p>
                        @endif
                    </div>
                    <div class="rounded-lg border border-indigo-100 bg-indigo-50 p-4">
                        <p class="text-xs font-semibold text-indigo-700 uppercase tracking-wider">% Close</p>
                        <p class="text-3xl font-bold text-indigo-700 mt-1">{{ $fmtPct($s['pct_close']) }}</p>
                        <div class="mt-2">
                            <div class="h-2 bg-white rounded-full overflow-hidden">
                                <div class="h-full bg-indigo-500 rounded-full" style="width: {{ min($s['pct_close'] ?? 0, 100) }}%"></div>
                            </div>
                            <div class="flex justify-between text-[11px] text-gray-500 mt-1">
                                <span>target {{ $target }}%</span>
                                @if ($selisihTarget !== null)
                                    <span class="font-semibold {{ $selisihTarget >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                        {{ $selisihTarget >= 0 ? '+' : '−' }}{{ number_format(abs($selisihTarget), 1, ',', '.') }} poin
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="rounded-lg border border-gray-100 bg-gray-50 p-4 col-span-2 lg:col-span-1">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Jenis Temuan</p>
                        <div class="flex items-baseline gap-2 mt-1">
                            <p class="text-3xl font-bold text-gray-900">{{ $fmtPct($s['pct_condition']) }}</p>
                            <p class="text-xs text-gray-500">Unsafe Condition</p>
                        </div>
                        <div class="flex h-2 rounded-full overflow-hidden mt-2 bg-white">
                            <div class="bg-indigo-500" style="width: {{ $s['pct_condition'] ?? 0 }}%"></div>
                            <div class="bg-amber-400" style="width: {{ $s['pct_action'] ?? 0 }}%"></div>
                        </div>
                        <p class="text-[11px] text-gray-500 mt-1">{{ $fmtNum($s['unsafe_condition']) }} kondisi · {{ $fmtNum($s['unsafe_action']) }} tindakan</p>
                    </div>
                </div>

                <!-- Departemen + Donut -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 rounded-lg border border-gray-100 p-5">
                        <h4 class="text-sm font-bold text-gray-900">Temuan per Departemen</h4>
                        <p class="text-xs text-gray-500">Diurutkan dari temuan open terbanyak.</p>
                        <div class="relative h-96 mt-4">
                            <canvas id="hazardDeptChart"></canvas>
                        </div>
                    </div>
                    <div class="rounded-lg border border-gray-100 p-5">
                        <h4 class="text-sm font-bold text-gray-900">Unsafe Condition vs Unsafe Action</h4>
                        <p class="text-xs text-gray-500">Komposisi seluruh temuan tahun ini.</p>
                        <div class="relative h-56 mt-4">
                            <canvas id="hazardTypeChart"></canvas>
                            <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                                <span class="text-2xl font-bold text-gray-900">{{ $fmtNum($s['diterima']) }}</span>
                                <span class="text-xs text-gray-500">temuan</span>
                            </div>
                        </div>
                        <div class="mt-4 space-y-2 text-sm">
                            <div class="flex items-center justify-between">
                                <span class="flex items-center gap-2 text-gray-700"><span class="w-3 h-3 rounded-sm bg-indigo-500"></span>Unsafe Condition</span>
                                <span class="font-semibold text-gray-900 tabular-nums">{{ $fmtNum($s['unsafe_condition']) }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="flex items-center gap-2 text-gray-700"><span class="w-3 h-3 rounded-sm bg-amber-400"></span>Unsafe Action</span>
                                <span class="font-semibold text-gray-900 tabular-nums">{{ $fmtNum($s['unsafe_action']) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Masuk vs Di-close -->
                <div class="rounded-lg border border-gray-100 p-5">
                    <h4 class="text-sm font-bold text-gray-900">Temuan Masuk vs Temuan Di-close</h4>
                    <p class="text-xs text-gray-500">Saat garis hijau di atas garis abu-abu, tim sedang mengurangi tunggakan dari bulan sebelumnya.</p>
                    <div class="relative h-72 mt-4">
                        <canvas id="hazardFlowChart"></canvas>
                    </div>
                </div>

                <!-- Tabel Progress Departemen -->
                <div>
                    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-2 mb-3">
                        <div>
                            <h4 class="text-sm font-bold text-gray-900">Progress Follow Up PIC Temuan</h4>
                            <p class="text-xs text-gray-500">Status: Aman ≥ {{ $target }}%, Perhatian 70–{{ $target }}%, Kritis &lt; 70%.</p>
                        </div>
                        <input id="hazardDeptSearch" type="text" placeholder="Cari departemen..."
                               class="text-sm border-gray-200 rounded-lg focus:border-indigo-500 focus:ring-indigo-500 w-full sm:w-60">
                    </div>
                    <div class="overflow-x-auto rounded-lg border border-gray-100">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <tr>
                                    <th class="px-4 py-3 text-left">Departemen</th>
                                    <th class="px-4 py-3 text-right">Open</th>
                                    <th class="px-4 py-3 text-right">Close</th>
                                    <th class="px-4 py-3 text-right">Total</th>
                                    <th class="px-4 py-3 text-left w-56">Progress</th>
                                    <th class="px-4 py-3 text-left">Status</th>
                                </tr>
                            </thead>
                            <tbody id="hazardDeptTable" class="divide-y divide-gray-100">
                                @foreach ($hazard['departments'] as $d)
                                    @php
                                        $p = $d['pct'] ?? 0;
                                        if ($p >= $target) {
                                            [$bar, $badge, $label] = ['bg-emerald-500', 'bg-emerald-50 text-emerald-700', 'Aman'];
                                        } elseif ($p >= 70) {
                                            [$bar, $badge, $label] = ['bg-amber-400', 'bg-amber-50 text-amber-700', 'Perhatian'];
                                        } else {
                                            [$bar, $badge, $label] = ['bg-red-500', 'bg-red-50 text-red-700', 'Kritis'];
                                        }
                                    @endphp
                                    <tr class="hover:bg-gray-50" data-name="{{ strtolower($d['nama']) }}">
                                        <td class="px-4 py-2.5 font-medium text-gray-800 whitespace-nowrap">{{ $d['nama'] }}</td>
                                        <td class="px-4 py-2.5 text-right tabular-nums {{ $d['open'] > 0 ? 'text-red-600 font-semibold' : 'text-gray-400' }}">{{ $d['open'] }}</td>
                                        <td class="px-4 py-2.5 text-right tabular-nums text-gray-700">{{ $d['close'] }}</td>
                                        <td class="px-4 py-2.5 text-right tabular-nums text-gray-700">{{ $d['total'] }}</td>
                                        <td class="px-4 py-2.5">
                                            <div class="flex items-center gap-2">
                                                <div class="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden">
                                                    <div class="h-full {{ $bar }} rounded-full" style="width: {{ $p }}%"></div>
                                                </div>
                                                <span class="text-xs font-medium text-gray-600 tabular-nums w-12 text-right">{{ $fmtPct($d['pct']) }}</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-2.5">
                                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $badge }}">{{ $label }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                                <tr id="hazardDeptEmpty" class="hidden">
                                    <td colspan="6" class="px-4 py-6 text-center text-gray-400">Departemen tidak ditemukan</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Rekap Bulanan -->
                <div>
                    <h4 class="text-sm font-bold text-gray-900 mb-3">Follow Up Hazard Report per Bulan</h4>
                    <div class="overflow-x-auto rounded-lg border border-gray-100">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <tr>
                                    <th class="px-4 py-3 text-left">Bulan</th>
                                    <th class="px-4 py-3 text-right">Diterima</th>
                                    <th class="px-4 py-3 text-right">Close</th>
                                    <th class="px-4 py-3 text-right">Open</th>
                                    <th class="px-4 py-3 text-right">% Close</th>
                                    <th class="px-4 py-3 text-right">Di-close Bulan Itu</th>
                                    <th class="px-4 py-3 text-right">Unsafe Condition</th>
                                    <th class="px-4 py-3 text-right">Unsafe Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($hazard['monthly'] as $m)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-2.5 font-medium text-gray-800">{{ $m['bulan'] }}</td>
                                        <td class="px-4 py-2.5 text-right tabular-nums text-gray-700">{{ $m['diterima'] }}</td>
                                        <td class="px-4 py-2.5 text-right tabular-nums text-gray-700">{{ $m['close'] }}</td>
                                        <td class="px-4 py-2.5 text-right tabular-nums {{ $m['open'] > 0 ? 'text-red-600 font-semibold' : 'text-gray-400' }}">{{ $m['open'] }}</td>
                                        <td class="px-4 py-2.5 text-right tabular-nums text-gray-700">{{ $fmtPct($m['pct_close']) }}</td>
                                        <td class="px-4 py-2.5 text-right tabular-nums text-gray-700">{{ $m['close_bulan_ini'] }}</td>
                                        <td class="px-4 py-2.5 text-right tabular-nums text-gray-700">{{ $m['unsafe_condition'] }}</td>
                                        <td class="px-4 py-2.5 text-right tabular-nums text-gray-700">{{ $m['unsafe_action'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50 font-bold text-gray-900">
                                <tr>
                                    <td class="px-4 py-3">Total</td>
                                    <td class="px-4 py-3 text-right tabular-nums">{{ $fmtNum($s['diterima']) }}</td>
                                    <td class="px-4 py-3 text-right tabular-nums">{{ $fmtNum($s['close']) }}</td>
                                    <td class="px-4 py-3 text-right tabular-nums text-red-600">{{ $fmtNum($s['open']) }}</td>
                                    <td class="px-4 py-3 text-right tabular-nums">{{ $fmtPct($s['pct_close']) }}</td>
                                    <td class="px-4 py-3 text-right tabular-nums">{{ $fmtNum($s['close_bulan_ini']) }}</td>
                                    <td class="px-4 py-3 text-right tabular-nums">{{ $fmtNum($s['unsafe_condition']) }}</td>
                                    <td class="px-4 py-3 text-right tabular-nums">{{ $fmtNum($s['unsafe_action']) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const monthly = @json($hazard['monthly']);
            const depts   = @json($hazard['departments']);
            const summary = @json($hazard['summary']);
            const target  = @json($target);

            const labels = monthly.map(m => m.bulan.substring(0, 3));
            const legend = { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8 } };

            Chart.defaults.font.family = getComputedStyle(document.body).fontFamily;
            Chart.defaults.color = '#6b7280';

            // 1. Tren bulanan: close + open bertumpuk, garis % close dan target
            new Chart(document.getElementById('hazardTrendChart'), {
                data: {
                    labels,
                    datasets: [
                        { type: 'bar', label: 'Close', data: monthly.map(m => m.close), backgroundColor: '#10b981', stack: 's', borderRadius: 4 },
                        { type: 'bar', label: 'Open',  data: monthly.map(m => m.open),  backgroundColor: '#ef4444', stack: 's', borderRadius: 4 },
                        { type: 'line', label: '% Close', data: monthly.map(m => m.pct_close), borderColor: '#4f46e5', backgroundColor: '#4f46e5', yAxisID: 'y1', tension: 0.3, pointRadius: 3 },
                        { type: 'line', label: `Target ${target}%`, data: labels.map(() => target), borderColor: '#9ca3af', borderDash: [5, 5], pointRadius: 0, yAxisID: 'y1' },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend,
                        tooltip: {
                            callbacks: {
                                label: c => c.dataset.yAxisID === 'y1'
                                    ? `${c.dataset.label}: ${c.parsed.y === null ? '–' : c.parsed.y.toLocaleString('id-ID')}%`
                                    : `${c.dataset.label}: ${c.parsed.y}`,
                            },
                        },
                    },
                    scales: {
                        x: { stacked: true, grid: { display: false } },
                        y: { stacked: true, beginAtZero: true, title: { display: true, text: 'Jumlah temuan' } },
                        y1: { position: 'right', min: 0, max: 100, grid: { drawOnChartArea: false }, ticks: { callback: v => v + '%' } },
                    },
                },
            });

            // 2. Departemen: bar horizontal bertumpuk
            new Chart(document.getElementById('hazardDeptChart'), {
                type: 'bar',
                data: {
                    labels: depts.map(d => d.nama),
                    datasets: [
                        { label: 'Open',  data: depts.map(d => d.open),  backgroundColor: '#ef4444', borderRadius: 3 },
                        { label: 'Close', data: depts.map(d => d.close), backgroundColor: '#10b981', borderRadius: 3 },
                    ],
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend,
                        tooltip: {
                            callbacks: {
                                footer: items => {
                                    const d = depts[items[0].dataIndex];
                                    return d.pct === null ? '' : `Progress close: ${d.pct.toLocaleString('id-ID')}%`;
                                },
                            },
                        },
                    },
                    scales: {
                        x: { stacked: true, beginAtZero: true },
                        y: { stacked: true, grid: { display: false }, ticks: { font: { size: 11 } } },
                    },
                },
            });

            // 3. Donut jenis temuan
            new Chart(document.getElementById('hazardTypeChart'), {
                type: 'doughnut',
                data: {
                    labels: ['Unsafe Condition', 'Unsafe Action'],
                    datasets: [{
                        data: [summary.unsafe_condition, summary.unsafe_action],
                        backgroundColor: ['#6366f1', '#fbbf24'],
                        borderWidth: 0,
                    }],
                },
                options: { responsive: true, maintainAspectRatio: false, cutout: '70%', plugins: { legend: { display: false } } },
            });

            // 4. Temuan masuk vs di-close per bulan
            new Chart(document.getElementById('hazardFlowChart'), {
                type: 'line',
                data: {
                    labels,
                    datasets: [
                        { label: 'Temuan masuk', data: monthly.map(m => m.diterima), borderColor: '#9ca3af', backgroundColor: '#9ca3af', tension: 0.3, pointRadius: 3 },
                        { label: 'Di-close bulan itu', data: monthly.map(m => m.close_bulan_ini), borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,.12)', fill: true, tension: 0.3, pointRadius: 3 },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: { legend },
                    scales: { x: { grid: { display: false } }, y: { beginAtZero: true } },
                },
            });

            // 5. Pencarian di tabel departemen
            const search = document.getElementById('hazardDeptSearch');
            const rows   = document.querySelectorAll('#hazardDeptTable tr[data-name]');
            const empty  = document.getElementById('hazardDeptEmpty');

            search.addEventListener('input', () => {
                const q = search.value.trim().toLowerCase();
                let visible = 0;
                rows.forEach(r => {
                    const match = r.dataset.name.includes(q);
                    r.classList.toggle('hidden', !match);
                    if (match) visible++;
                });
                empty.classList.toggle('hidden', visible > 0);
            });
        });
    </script>
</x-app-layout>