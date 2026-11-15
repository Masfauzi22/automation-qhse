{{--
    Peta lokasi temuan (Leaflet + citra satelit).

    Mode pilih (form Lapor Hazard):
        <x-hazard-report.map editable :lat="old('latitude')" :lng="old('longitude')" :accuracy="old('location_accuracy')" />
        → mengisi input tersembunyi: latitude, longitude, location_accuracy

        color-from="hazard_priority_id" → warna pin ikut pilihan prioritas (option punya data-color & data-speed)

    Mode lihat (detail laporan):
        <x-hazard-report.map :lat=".." :lng=".." :accuracy=".." color="#dc2626" speed="1s" :pulse="true" label="Prioritas Tinggi" />

    Pin punya animasi sinyal berdenyut. Warna & kecepatan mengikuti prioritas:
    merah cepat (tinggi), kuning sedang, hijau pelan (rendah). Laporan closed / ditolak tidak berdenyut.
--}}
@props([
    'editable' => false,
    'lat'      => null,
    'lng'      => null,
    'accuracy' => null,
    'height'   => null,
    'color'     => '#6b7280',  // warna pin (hex)
    'speed'     => '2s',       // kecepatan denyut
    'pulse'     => true,       // animasi sinyal aktif?
    'label'     => null,       // keterangan di bawah peta, mis. "Prioritas Tinggi"
    'colorFrom' => null,       // id <select> prioritas (mode pilih)
])

@php
    $map = config('hazard.map');
    $hasPoint = is_numeric($lat) && is_numeric($lng);

    $cfg = [
        'editable'   => (bool) $editable,
        'lat'        => $hasPoint ? (float) $lat : null,
        'lng'        => $hasPoint ? (float) $lng : null,
        'accuracy'   => is_numeric($accuracy) ? (int) $accuracy : null,
        'defaultLat' => (float) $map['default_lat'],
        'defaultLng' => (float) $map['default_lng'],
        'zoom'       => (int) $map['default_zoom'],
        'satUrl'     => $map['satellite_url'],
        'satAttr'    => $map['satellite_attribution'],
        'labelsUrl'  => $map['labels_url'],
        'streetUrl'  => $map['street_url'],
        'streetAttr' => $map['street_attribution'],
        'maxNative'  => (int) $map['max_native_zoom'],
        'color'      => preg_match('/^#[0-9a-fA-F]{6}$/', (string) $color) ? $color : '#6b7280',
        'speed'      => preg_match('/^\d+(\.\d+)?s$/', (string) $speed) ? $speed : '2s',
        'pulse'      => (bool) $pulse,
        'colorFrom'  => $colorFrom,
    ];

    $height = $height ?? ($editable ? '320px' : '220px');
@endphp

@once
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
    <style>
        /* Pin hazard dengan sinyal berdenyut di ujung pin */
        .hz-pin { position: relative; width: 30px; height: 40px; }
        .hz-pin svg { position: relative; z-index: 1; filter: drop-shadow(0 2px 3px rgba(0,0,0,.45)); }
        .hz-ring {
            position: absolute; left: 50%; bottom: 0;
            width: 18px; height: 18px; margin-left: -9px; margin-bottom: -9px;
            border-radius: 9999px; background: var(--hz-color); border: 2px solid var(--hz-color);
            opacity: 0; animation: hz-pulse var(--hz-speed) ease-out infinite;
        }
        .hz-ring + .hz-ring { animation-delay: calc(var(--hz-speed) / 2); }
        @keyframes hz-pulse {
            0%   { transform: scale(.4); opacity: .75; }
            100% { transform: scale(4.5); opacity: 0; }
        }
        @media (prefers-reduced-motion: reduce) { .hz-ring { animation: none; opacity: .35; transform: scale(2); } }
    </style>
    <script>
        // Objek Leaflet disimpan di variabel biasa (bukan data Alpine),
        // karena Leaflet rusak kalau dibungkus Proxy reaktif Alpine.
        function hazardMap(cfg) {
            let map = null, marker = null, circle = null;
            let color = cfg.color, speed = cfg.speed;

            // Pin + 2 lingkaran sinyal yang berdenyut bergantian
            const makeIcon = () => L.divIcon({
                className: '',
                iconSize: [30, 40],
                iconAnchor: [15, 40],
                html: '<div class="hz-pin" style="--hz-color:' + color + ';--hz-speed:' + speed + '">'
                    + (cfg.pulse ? '<span class="hz-ring"></span><span class="hz-ring"></span>' : '')
                    + '<svg width="30" height="40" viewBox="0 0 30 40"><path d="M15 0C6.7 0 0 6.7 0 15c0 11.2 15 25 15 25s15-13.8 15-25C30 6.7 23.3 0 15 0z" fill="' + color + '" stroke="#fff" stroke-width="2"/><circle cx="15" cy="15" r="5.5" fill="#fff"/></svg>'
                    + '</div>',
            });

            // Mode pilih: warna pin ikut <select> prioritas
            const readSelect = () => {
                const opt = document.getElementById(cfg.colorFrom)?.selectedOptions[0];
                color = opt?.dataset.color || cfg.color;
                speed = opt?.dataset.speed || cfg.speed;
                if (marker) marker.setIcon(makeIcon());
            };

            return {
                lat: cfg.lat,
                lng: cfg.lng,
                accuracy: cfg.accuracy,
                locating: false,
                geoError: '',

                init() {
                    const hasPoint = this.lat !== null;
                    const tileOpt  = { maxZoom: 20, maxNativeZoom: cfg.maxNative };

                    const satellite = L.layerGroup([
                        L.tileLayer(cfg.satUrl, { ...tileOpt, attribution: cfg.satAttr }),
                        L.tileLayer(cfg.labelsUrl, tileOpt), // nama jalan & tempat di atas citra satelit
                    ]);
                    const street = L.tileLayer(cfg.streetUrl, { ...tileOpt, attribution: cfg.streetAttr });

                    map = L.map(this.$refs.map, {
                        center: hasPoint ? [this.lat, this.lng] : [cfg.defaultLat, cfg.defaultLng],
                        zoom: hasPoint ? 18 : cfg.zoom,
                        layers: [satellite],
                        scrollWheelZoom: cfg.editable,
                    });
                    L.control.layers({ 'Satelit': satellite, 'Peta Jalan': street }, null, { position: 'topright' }).addTo(map);

                    if (cfg.colorFrom) {
                        readSelect();
                        document.getElementById(cfg.colorFrom)?.addEventListener('change', readSelect);
                    }

                    if (hasPoint) {
                        this.place(this.lat, this.lng);
                        this.drawAccuracy();
                    }

                    if (cfg.editable) {
                        map.on('click', (e) => {
                            this.accuracy = null; // titik dipilih manual, bukan dari GPS
                            this.place(e.latlng.lat, e.latlng.lng);
                        });
                    }

                    // Peta kadang dirender sebelum ukurannya final
                    setTimeout(() => map.invalidateSize(), 250);
                },

                place(lat, lng) {
                    this.lat = Math.round(lat * 1e7) / 1e7;
                    this.lng = Math.round(lng * 1e7) / 1e7;

                    if (! marker) {
                        marker = L.marker([this.lat, this.lng], { icon: makeIcon(), draggable: cfg.editable }).addTo(map);
                        marker.on('dragend', () => {
                            const p = marker.getLatLng();
                            this.accuracy = null;
                            this.place(p.lat, p.lng);
                        });
                    } else {
                        marker.setLatLng([this.lat, this.lng]);
                    }

                    if (this.accuracy === null && circle) {
                        map.removeLayer(circle);
                        circle = null;
                    }
                },

                drawAccuracy() {
                    if (circle) map.removeLayer(circle);
                    circle = this.accuracy
                        ? L.circle([this.lat, this.lng], { radius: this.accuracy, color: '#2563eb', weight: 1, fillOpacity: 0.12 }).addTo(map)
                        : null;
                },

                locate() {
                    this.geoError = '';
                    if (! window.isSecureContext) {
                        this.geoError = 'GPS hanya bisa dipakai lewat HTTPS. Untuk sekarang, tap titik lokasi di peta.';
                        return;
                    }
                    if (! navigator.geolocation) {
                        this.geoError = 'Browser ini tidak mendukung GPS. Tap titik lokasi di peta.';
                        return;
                    }

                    this.locating = true;
                    navigator.geolocation.getCurrentPosition((pos) => {
                        this.locating = false;
                        this.accuracy = Math.round(pos.coords.accuracy);
                        this.place(pos.coords.latitude, pos.coords.longitude);
                        this.drawAccuracy();
                        map.setView([this.lat, this.lng], 18);
                    }, (err) => {
                        this.locating = false;
                        this.geoError = {
                            1: 'Izin lokasi ditolak. Aktifkan izin lokasi di browser, atau tap titik di peta.',
                            2: 'Lokasi tidak terdeteksi. Coba di area terbuka, atau tap titik di peta.',
                            3: 'GPS terlalu lama merespons. Coba lagi, atau tap titik di peta.',
                        }[err.code] || 'Gagal mengambil lokasi.';
                    }, { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 });
                },

                clear() {
                    if (marker) { map.removeLayer(marker); marker = null; }
                    if (circle) { map.removeLayer(circle); circle = null; }
                    this.lat = this.lng = this.accuracy = null;
                },

                get coordText() {
                    return this.lat === null ? '' : this.lat.toFixed(6) + ', ' + this.lng.toFixed(6);
                },
            };
        }
    </script>
@endonce

<div x-data="hazardMap(@js($cfg))" {{ $attributes->merge(['class' => 'space-y-2']) }}>
    @if ($editable)
        <div class="flex flex-wrap items-center gap-2">
            <button type="button" @click="locate()" :disabled="locating"
                    class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 disabled:opacity-60">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 2v3m0 14v3M2 12h3m14 0h3"/><circle cx="12" cy="12" r="7"/><circle cx="12" cy="12" r="2.5" fill="currentColor"/></svg>
                <span x-text="locating ? 'Mencari lokasi...' : 'Gunakan Lokasi Saya'">Gunakan Lokasi Saya</span>
            </button>
            <button type="button" x-show="lat !== null" @click="clear()" style="display: none;"
                    class="rounded-lg px-3 py-2 text-sm font-medium text-gray-500 hover:bg-gray-100">Hapus titik</button>
            <span class="text-xs text-gray-500">atau tap/klik peta, pin bisa digeser.</span>
        </div>
        <p x-show="geoError" x-text="geoError" class="text-xs font-medium text-red-600" style="display: none;"></p>

        <input type="hidden" name="latitude" :value="lat ?? ''">
        <input type="hidden" name="longitude" :value="lng ?? ''">
        <input type="hidden" name="location_accuracy" :value="accuracy ?? ''">
    @endif

    {{-- isolate: supaya lapisan peta tidak menutupi dropdown / sidebar --}}
    <div class="relative isolate z-0 overflow-hidden rounded-lg border border-gray-200">
        <div x-ref="map" style="height: {{ $height }};" class="w-full bg-gray-100"></div>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-2 text-xs">
        <p x-show="lat !== null" class="text-gray-600" style="{{ $hasPoint ? '' : 'display: none;' }}">
            <span class="font-mono" x-text="coordText">{{ $hasPoint ? number_format((float) $lat, 6, '.', '') . ', ' . number_format((float) $lng, 6, '.', '') : '' }}</span>
            <template x-if="accuracy">
                <span :class="accuracy > 50 ? 'font-semibold text-amber-600' : 'text-gray-500'"
                      x-text="' · akurasi ±' + accuracy + ' m' + (accuracy > 50 ? ' (kurang akurat, geser pin bila perlu)' : '')"></span>
            </template>
        </p>
        @if ($editable)
            <p x-show="lat === null" class="text-gray-400" style="{{ $hasPoint ? 'display: none;' : '' }}">Belum ada titik lokasi.</p>
        @elseif ($hasPoint)
            <div class="flex items-center gap-4">
                @if ($label)
                    <span class="inline-flex items-center gap-1.5 font-semibold" style="color: {{ $cfg['color'] }}">
                        <span class="h-2.5 w-2.5 rounded-full" style="background: {{ $cfg['color'] }}"></span>{{ $label }}
                    </span>
                @endif
                <a href="https://www.google.com/maps?q={{ $lat }},{{ $lng }}" target="_blank" rel="noopener"
                   class="font-semibold text-emerald-700 hover:text-emerald-900">Buka di Google Maps →</a>
            </div>
        @endif
    </div>
</div>