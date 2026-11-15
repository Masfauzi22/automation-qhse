<?php

/*
|--------------------------------------------------------------------------
| Pengaturan Hazard Report
|--------------------------------------------------------------------------
| Nilai di sini adalah nilai awal. Kalau HSE minta diubah, cukup ganti di sini,
| lalu jalankan: php artisan config:clear
*/

return [

    // Tanggal temuan boleh mundur maksimal berapa hari dari hari ini
    'max_backdate_days' => 7,

    // Batas waktu follow up (hari) kalau prioritas tidak punya pengaturan sendiri
    'default_sla_days' => 3,

    // Upload foto
    'max_photos'   => 3,     // maksimal foto per jenis (temuan / closing)
    'max_photo_kb' => 5120,  // 5 MB per foto

    // Perpanjangan batas waktu oleh PIC (kalau temuan belum bisa langsung di-close)
    'max_extensions'           => 2,    // maksimal berapa kali per laporan
    'max_extension_days'       => 14,   // target baru maksimal berapa hari dari hari pengajuan
    'extension_photo_required' => true, // wajib foto tindakan sementara (barikade, rambu, dll)

    // Target laporan per orang per bulan. 0 = tidak ada target (hanya ditampilkan jumlahnya).
    // Laporan yang ditolak HSE tidak dihitung ke target.
    'monthly_target' => 0,

    // Peta lokasi temuan
    'map' => [
        // Titik awal peta kalau pelapor belum memilih lokasi → ganti dengan koordinat area kerja
        'default_lat'  => -4.0296860,   // PT Indonesia Bulk Terminal, Mekar Putih, Pulau Laut
        'default_lng'  => 116.0373000,
        'default_zoom' => 16,

        // true = pelapor wajib menandai titik di peta
        'require_coordinates' => false,

        // Citra satelit (Esri World Imagery). Wajib tetap menampilkan atribusi Esri.
        // Sebelum dipakai produksi, cek syarat Esri / daftar akun ArcGIS (gratis) bila diperlukan.
        'satellite_url'         => 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
        'satellite_attribution' => 'Tiles &copy; Esri &mdash; Esri, Maxar, Earthstar Geographics, GIS User Community',
        'labels_url'            => 'https://server.arcgisonline.com/ArcGIS/rest/services/Reference/World_Boundaries_and_Places/MapServer/tile/{z}/{y}/{x}',

        // Peta jalan (OpenStreetMap)
        'street_url'         => 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
        'street_attribution' => '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',

        // Zoom terdalam yang tersedia dari server tile; lebih dari ini gambar diperbesar
        'max_native_zoom' => 19,
    ],

    // Deteksi laporan duplikat (ditampilkan ke HSE saat validasi)
    'duplicate' => [
        'window_days'    => 30,  // bandingkan dengan laporan ± 30 hari dari tanggal temuan
        'min_score'      => 35,  // skor minimal (0-100) supaya dianggap mirip
        'max_results'    => 3,   // maksimal kandidat yang ditampilkan
        'max_candidates' => 300, // batas laporan yang diperiksa per pengecekan
    ],

    // Email notifikasi & reminder
    'notifications' => [
        // Saklar utama. false = tidak ada email sama sekali.
        'enabled' => env('HAZARD_MAIL_ENABLED', true),

        // Role yang dianggap tim HSE (penerima email validasi / verifikasi / ringkasan)
        'hse_roles' => ['qhse'],

        // Nyalakan / matikan per jenis email
        'events' => [
            'new_report'        => true,  // laporan baru → HSE
            'pic_updates'       => true,  // divalidasi, bukti dikembalikan, perpanjangan ditolak → PIC
            'reporter_updates'  => true,  // divalidasi, ditolak, closed → pelapor
            'closing_submitted' => true,  // PIC kirim bukti perbaikan → HSE
            'extension'         => true,  // PIC memperpanjang batas waktu → HSE
            'daily_reminder'    => true,  // reminder harian → PIC & HSE
        ],

        // Reminder harian
        'reminder_time'            => '07:00',          // jam kirim (lokal)
        'reminder_timezone'        => 'Asia/Jakarta',
        'remind_days_before'       => 1,                // ingatkan PIC H-1 sebelum batas waktu
        'pending_validation_hours' => 24,               // laporan belum divalidasi > 24 jam ditandai merah di ringkasan HSE

        // Kunci rahasia untuk Vercel Cron (isi CRON_SECRET di .env / Vercel)
        'cron_secret' => env('CRON_SECRET'),
    ],

    // Format nomor laporan: HZR/2026/11/0001
    'number_prefix' => 'HZR',

    // Alasan penolakan oleh HSE (diambil dari dashboard Google Sheet)
    'reject_reasons' => [
        'deskripsi_tidak_jelas' => 'Deskripsi tidak jelas',
        'duplikat'              => 'Duplikat',
        'sudah_aman'            => 'Sudah aman',
        'tidak_masuk_kriteria'  => 'Tidak masuk kriteria',
    ],

];