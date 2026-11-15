<?php

/*
|--------------------------------------------------------------------------
| Jenis Permit to Work (PTW)
|--------------------------------------------------------------------------
| Satu modul untuk semua jenis permit: Pekerjaan Panas, Ketinggian,
| Penggalian, dan Ruang Terbatas & Tertutup. Menambah jenis baru cukup
| menambah blok baru di 'types' berikut, tanpa membuat tabel baru.
|
| Setelah mengubah file ini: php artisan config:clear
*/

return [

    'number_prefix' => 'PTW/IBT',   // PTW/IBT/HW/2026/11/0001

    // Sertifikat Penyelesaian (terbit saat permit Closed)
    'certificate' => [
        'number_prefix' => 'CERT/PTW/IBT',                         // CERT/PTW/IBT/2026/11/0001
        'issuer'        => 'HSE Department',                       // tertulis sebagai penerbit
        'site'          => 'Site IBT – Mekar Putih, Pulau Laut',   // nama lokasi kerja
    ],
    'max_photos'    => 3,
    'max_photo_kb'  => 5120,
    'max_jsa_kb'    => 5120,

    // Batas aman hasil pengukuran gas (WAJIB dikonfirmasi HSE sebelum dipakai).
    // Jenis permit bisa punya batas sendiri lewat 'gas_limits' di bloknya (mis. Ruang Terbatas).
    'gas_limits' => [
        'o2'  => ['label' => 'O₂',  'unit' => '%',   'min' => 19.5, 'max' => 23.5],
        'lel' => ['label' => 'LEL', 'unit' => '%',   'min' => null, 'max' => 10],
        'h2s' => ['label' => 'H₂S', 'unit' => 'ppm', 'min' => null, 'max' => 10],
        'co'  => ['label' => 'CO',  'unit' => 'ppm', 'min' => null, 'max' => 25],
    ],

    'types' => [

        // =============================================================
        // PEKERJAAN PANAS — form PTIBT/HSE/FRM/007/001 Rev. 2
        // =============================================================
        'hot_work' => [
            'code'        => 'HW',
            'label'       => 'Pekerjaan Panas',
            'title'       => 'Surat Izin Keselamatan Pekerjaan Panas',
            'form_number' => 'PTIBT/HSE/FRM/007/001',
            'form_rev'    => '2',
            'max_days'    => 7,
            'icon_color'  => '#dc2626',

            'icon'        => 'flame',

            // Petugas pengawas di lapangan (disimpan di kolom fire_watch_name)
            'watcher' => [
                'label'       => 'Nama Fire Watch',
                'short'       => 'Fire watch',
                'placeholder' => 'Orang yang mengawasi percikan api selama & setelah pekerjaan',
            ],

            // Jumlah baris "Keterangan lain yang diperlukan" (0 = tidak ada di form)
            'extra_notes' => 0,

            // Jenis Pekerjaan Yang Akan Dilakukan
            'work_types' => [
                'mengelas'    => 'Mengelas',
                'memotong'    => 'Memotong',
                'menggerinda' => 'Menggerinda',
                'lain'        => 'Lain-lain',
            ],

            // Daftar Pemeriksaan Keselamatan
            //   allow_na    : boleh dijawab "Tidak Berlaku"
            //   note        : petunjuk isian catatan
            //   requires    : 'jsa' = wajib upload JSA, 'gas_test' = kalau "Ya" wajib isi hasil ukur gas
            'checklist' => [
                'apar'          => ['text' => 'Terdapat Alat Pemadam Api Ringan (APAR) atau Fire Hose di wilayah kerja.', 'allow_na' => false, 'note' => 'Contoh: APAR 6 kg x2'],
                'apd'           => ['text' => 'Alat Pelindung Diri yang disyaratkan (Full Face Shield, Apron, Goggles, Leather Hand glove, dsb).', 'allow_na' => false],
                'gas_cylinder'  => ['text' => 'Tabung Oxygen & Acetylene dalam posisi berdiri dan terikat.', 'allow_na' => true],
                'grounding'     => ['text' => 'Arc welding digrounding dengan benar.', 'allow_na' => true],
                'flammable_11m' => ['text' => 'Sudah memindahkan bahan yang mudah terbakar / Meledak (minyak, solar, tinner, cat, oli, grease, dll) sekitar ± 11 meter dari lokasi bekerja.', 'allow_na' => false],
                'fire_blanket'  => ['text' => 'Disekitar area terdapat material bahan mudah terbakar harus ditutup dengan terpal anti api atau dibasahi.', 'allow_na' => true],
                'jsa'           => ['text' => 'Dilampirkan analisa keselamat pekerjaan / JSA untuk pekerjaan yang dimaksud.', 'allow_na' => false, 'requires' => 'jsa'],
                'fire_watch'    => ['text' => 'Dilakukan monitoring diwilayah kerja untuk mengantisipasi terjadinya kebakaran akibat percikan api.', 'allow_na' => false, 'note' => 'Isi nama fire watch'],
                'fine_coal'     => ['text' => 'Apakah ada potensi fine coal / debu batubara yang dapat terbakar.', 'allow_na' => true, 'note' => 'Kalau ada, tulis pengendaliannya (mis. area disiram air)'],
                'gas_test'      => ['text' => 'Diperlukan pengukuran kadar gas (bila pekerjaan dilakukan di ruang terbatas & tertutup atau flammable area).', 'allow_na' => true, 'requires' => 'gas_test'],
            ],

            // Pengawasan oleh Supervisi Pelaksana: dicentang otomatis pada tahap tertentu
            //   stage: check = saat diperiksa, daily = saat aktivasi harian, closeout = saat close-out
            'supervision' => [
                'inspected'   => ['text' => 'Telah dilakukan pemeriksaan dan telah memenuhi semua persyaratan diatas.', 'stage' => 'check'],
                'area_safe'   => ['text' => 'Area untuk bekerja dinyatakan aman dari bahaya kebakaran.', 'stage' => 'check'],
                'continuous'  => ['text' => 'Dilaksanakan pengawasan terus menerus selama pekerjaan dilakukan.', 'stage' => 'daily'],
                'first_2h'    => ['text' => 'Dilakukan pengawasan selama 2 jam pertama dengan terus menerus.', 'stage' => 'daily'],
                'post_1h'     => ['text' => 'Dilakukan pengawasan selama 1 jam setelah pekerjaan selesai.', 'stage' => 'closeout'],
            ],

            // Pengawasan setelah pekerjaan selesai minimal (menit), dicek saat close-out
            'post_watch_minutes' => 60,

            // Konfirmasi singkat yang dicentang Spv setiap pagi saat "Aktifkan Hari Ini"
            'daily_confirmations' => [
                'apar_ready'  => 'APAR / fire hose tersedia dan siap pakai',
                'area_clear'  => 'Bahan mudah terbakar sudah dipindahkan / ditutup',
                'fire_watch'  => 'Fire watch siap mengawasi selama pekerjaan & 2 jam pertama',
            ],

            // Pernyataan yang wajib dicentang atasan saat memeriksa di lapangan
            'check_confirmations' => [
                'checked' => [
                    'inspected' => 'Saya sudah memeriksa lokasi dan semua persyaratan di Daftar Pemeriksaan terpenuhi.',
                    'area_safe' => 'Area kerja dinyatakan aman dari bahaya kebakaran.',
                ],
                'close_checked' => [
                    'area_clean' => 'Saya sudah memeriksa lokasi setelah pekerjaan: tidak ada sisa api / bara, area bersih dan aman.',
                ],
            ],

            // Teks & contoh isian yang berbeda per jenis permit
            'texts' => [
                'perhatian'         => 'Sebelum Surat Izin Keselamatan dikeluarkan, pastikan hal-hal yang dapat menimbulkan bahaya kebakaran dapat dihindari, dan tidak ada cara atau metode lain yang lebih aman.',
                'declaration'       => 'pencegahan bahaya kebakaran sudah dilakukan sesuai Daftar Pemeriksaan',
                'tools'             => 'Contoh: Mesin las SMAW, gerinda tangan 4", tabung oksigen & acetylene',
                'daily_note'        => 'Contoh: lanjut las handrail segmen 2',
                'final_condition'   => 'Contoh: tidak ada sisa bara, terpal dilepas, area dibersihkan, tabung dikembalikan',
                'closeout_confirm'  => 'Pengawasan 1 jam setelah pekerjaan sudah dilakukan dan area dinyatakan aman.',
                'stop_hint'         => 'mis. angin kencang, ada kebocoran gas, fire watch tidak ada',
                'stop_example'      => 'Contoh: percikan api mengenai kabel conveyor, fire watch tidak di lokasi',
                'resume_example'    => 'Contoh: kabel conveyor sudah ditutup fire blanket, fire watch kembali standby',
                'approve_note'      => 'Contoh: fire watch wajib standby sampai jam 18.00',
                'return_note'       => 'Contoh: APAR di lokasi belum ada, JSA belum ditandatangani',
                'return_close_note' => 'Contoh: masih ada sisa bara di area, foto kondisi akhir kurang jelas',
            ],

            'distribution' => ['Ditempel – Asli', 'QHSE section – kuning'],
        ],

        // =============================================================
        // PEKERJAAN DI KETINGGIAN — form PTIBT/HSE/FRM/007/002 Rev. 1
        // =============================================================
        'height_work' => [
            'code'        => 'WH',
            'label'       => 'Pekerjaan di Ketinggian',
            'title'       => 'Surat Izin Keselamatan Pekerjaan di Ketinggian',
            'form_number' => 'PTIBT/HSE/FRM/007/002',
            'form_rev'    => '1',
            'max_days'    => 7,
            'icon_color'  => '#d97706',
            'icon'        => 'height',

            'watcher' => [
                'label'       => 'Nama Pengawas Pekerjaan',
                'short'       => 'Pengawas',
                'placeholder' => 'Orang yang mengawasi pekerja di ketinggian selama pekerjaan berlangsung',
            ],

            'extra_notes' => 3,

            // Sesuai form kertas
            'work_types' => [
                'mengelas'    => 'Mengelas',
                'memotong'    => 'Memotong',
                'menggerinda' => 'Menggerinda',
                'lain'        => 'Lain-lain',
            ],

            // Daftar Pemeriksaan Pengaman
            //   requires linked_permit : kalau "Ya" wajib memilih permit jenis linked_type yang terkait
            //   required_for_work_types: butir ini wajib "Ya" kalau jenis pekerjaan tsb dipilih
            'checklist' => [
                'barricade'     => ['text' => 'Area dibawah pekerjaan di ketinggian telah dibatasi dan atau ditandai "Ada Pekerjaan di Atas".', 'allow_na' => false, 'note' => 'Contoh: safety line + rambu, radius 5 m'],
                'apd'           => ['text' => 'Alat pelindung diri yang disyaratkan (Helm, Safety Shoes, Full Body Harness, Safety Line, dsb).', 'allow_na' => false, 'note' => 'Contoh: full body harness double lanyard x4'],
                'jsa'           => ['text' => 'Dilampirkan analisa keselamat pekerjaan / JSA untuk pekerjaan yang dimaksud.', 'allow_na' => false, 'requires' => 'jsa'],
                'ladder'        => ['text' => 'Tangga telah disediakan dan telah memenuhi syarat – syarat keselamatan kerja.', 'allow_na' => true, 'note' => 'Kalau pakai tangga: jenis & kondisinya'],
                'scaffolding'   => ['text' => 'Bila memakai perancah (scaffolding) telah diperiksa dan memenuhi syarat – syarat pengikatan serta keselamatan kerja (handrail & railing tiap lantai) dan safety line harus terpasang.', 'allow_na' => true, 'note' => 'Contoh: scaff tag hijau, diperiksa scaffolder ...'],
                'tools_secured' => ['text' => 'Peralatan kerja yang dibawa telah disimpan / diletakkan pada tempat yang aman dari resiko bahaya terjatuh.', 'allow_na' => false, 'note' => 'Contoh: tool lanyard, tool bag'],
                'roof'          => ['text' => 'Bila bekerja di atas atap, safety line harus terpasang dan full body harness harus mengait pada safety line.', 'allow_na' => true],
                'hot_work'      => ['text' => 'Bila ada pekerjaan panas, telah dipenuhi prosedur izin pekerjaan panas.', 'allow_na' => true,
                                    'requires' => 'linked_permit', 'linked_type' => 'hot_work',
                                    'required_for_work_types' => ['mengelas', 'memotong', 'menggerinda']],
            ],

            // Pengawasan (form kertas hanya 1 butir)
            'supervision' => [
                'continuous' => ['text' => 'Dilaksanakan pengawasan selama pekerjaan dilakukan.', 'stage' => 'daily'],
            ],

            // Tidak ada pengawasan setelah pekerjaan selesai → isian "jam pengawasan berakhir" tidak muncul
            'post_watch_minutes' => 0,

            'daily_confirmations' => [
                'barricade'       => 'Area di bawah sudah dibatasi & dipasang tanda "Ada Pekerjaan di Atas"',
                'fall_protection' => 'Full body harness, safety line, tangga / perancah sudah diperiksa dan layak pakai',
                'weather_watcher' => 'Cuaca aman (tidak hujan deras / angin kencang) dan pengawas siap di lokasi',
            ],

            'check_confirmations' => [
                'checked' => [
                    'inspected' => 'Saya sudah memeriksa lokasi dan semua persyaratan di Daftar Pemeriksaan Pengaman terpenuhi.',
                    'area_safe' => 'Area di bawah sudah dibatasi dan alat pengaman jatuh (harness, safety line, tangga / perancah) layak pakai.',
                ],
                'close_checked' => [
                    'area_clean' => 'Saya sudah memeriksa lokasi setelah pekerjaan: tidak ada alat / material tertinggal di ketinggian, perancah & barikade sudah aman.',
                ],
            ],

            'texts' => [
                'checklist_title'   => 'Daftar Pemeriksaan Pengaman',
                'supervision_title' => 'Pengawasan',
                'perhatian'         => 'Sebelum Surat Izin Keselamatan dikeluarkan, pastikan pekerjaan di ketinggian telah dilengkapi perlengkapan pengaman yang diperlukan dan area di bawahnya sudah dibatasi. Izin ini untuk mencegah bahaya terjatuh dan kejatuhan benda dari atas.',
                'declaration'       => 'perlengkapan pengaman jatuh sudah disiapkan dan area di bawah pekerjaan sudah dibatasi sesuai Daftar Pemeriksaan Pengaman',
                'tools'             => 'Contoh: scaffolding 3 lantai, tangga A 3 m, full body harness x4, mesin bor',
                'daily_note'        => 'Contoh: lanjut pemasangan atap segmen 2',
                'final_condition'   => 'Contoh: perancah dibongkar, tidak ada alat / material tertinggal di atas, barikade dilepas, area bersih',
                'closeout_confirm'  => 'Tidak ada alat / material tertinggal di ketinggian dan area di bawah sudah aman untuk dilalui.',
                'stop_hint'         => 'mis. hujan deras / angin kencang, safety line tidak terpasang, perancah goyah',
                'stop_example'      => 'Contoh: angin kencang di atas ship loader, pekerja tidak mengaitkan harness',
                'resume_example'    => 'Contoh: angin sudah reda, semua pekerja sudah mengaitkan harness ke safety line',
                'approve_note'      => 'Contoh: hentikan pekerjaan kalau hujan / angin kencang',
                'return_note'       => 'Contoh: perancah belum ada scaff tag, area bawah belum dibarikade',
                'return_close_note' => 'Contoh: masih ada material di atas atap, barikade belum dilepas',
            ],

            'distribution' => ['Ditempel – Asli', 'QHSE section – kuning'],
        ],

        // =============================================================
        // PEKERJAAN PENGGALIAN — form PTIBT/HSE/FRM/007/003 Rev. 2
        // =============================================================
        'excavation' => [
            'code'        => 'WD',
            'label'       => 'Pekerjaan Penggalian',
            'title'       => 'Surat Izin Keselamatan Pekerjaan Penggalian',
            'form_number' => 'PTIBT/HSE/FRM/007/003',
            'form_rev'    => '2',
            'max_days'    => 7,
            'icon_color'  => '#a16207',
            'icon'        => 'dig',

            'watcher' => [
                'label'       => 'Nama Pengawas Pekerjaan',
                'short'       => 'Pengawas',
                'placeholder' => 'Orang yang mengawasi area galian selama pekerjaan berlangsung',
            ],

            'extra_notes' => 3,

            // Form penggalian tidak punya pilihan jenis pekerjaan
            'work_types' => [],

            //   requires document : kalau "Ya" wajib melampirkan file (PDF / foto), mis. denah utilitas
            'checklist' => [
                'cable_map'      => ['text' => 'Denah kabel listrik / peralatan elektronik dan kabel telephone dibawah tanah terlampir.', 'allow_na' => true, 'requires' => 'document', 'note' => 'Pilih Tidak Berlaku kalau tidak ada kabel di area galian'],
                'pipe_map'       => ['text' => 'Denah jalur pipa air, pipa minyak / pipa bahan bakar terlampir.', 'allow_na' => true, 'requires' => 'document', 'note' => 'Pilih Tidak Berlaku kalau tidak ada pipa di area galian'],
                'network_map'    => ['text' => 'Denah jalur networking system dibawah tanah terlampir.', 'allow_na' => true, 'requires' => 'document', 'note' => 'Pilih Tidak Berlaku kalau tidak ada jaringan di area galian'],
                'shoring'        => ['text' => 'Penopang longsoran tanah telah dipasang.', 'allow_na' => true, 'note' => 'Contoh: shoring kayu, kedalaman galian 1,5 m'],
                'barricade'      => ['text' => 'Area sekitar penggalian dipasang pengaman dan rambu-rambu.', 'allow_na' => false, 'note' => 'Contoh: barikade + rambu "Awas Galian", lampu malam'],
                'access'         => ['text' => 'Akses masuk yang cukup dan tersedianya tangga untuk masuk ke area kerja.', 'allow_na' => true],
                'road_diversion' => ['text' => 'Diperlukan peralihan alur jalan.', 'allow_na' => true, 'note' => 'Kalau Ya, tulis rute pengalihannya'],
                'apd'            => ['text' => 'Pelaksanaan pekerjaan telah dilengkapi dengan APD (Helmet, Safety Shoes, Kacamata, dsb).', 'allow_na' => false],
                'gas_test'       => ['text' => 'Telah dilakukan pengukuran kadar gas (bila pekerjaan dilakukan dalam ruang terbatas dan tertutup).', 'allow_na' => true, 'requires' => 'gas_test'],
            ],

            'supervision' => [
                'continuous' => ['text' => 'Dilaksanakan pengawasan selama pekerjaan dilakukan.', 'stage' => 'daily'],
            ],

            'post_watch_minutes' => 0,

            'daily_confirmations' => [
                'barricade' => 'Pengaman & rambu-rambu di sekitar galian terpasang',
                'shoring'   => 'Dinding galian / penopang aman, tidak ada retakan atau tanda longsor',
                'access'    => 'Akses keluar-masuk & tangga tersedia, galian tidak tergenang air',
            ],

            'check_confirmations' => [
                'checked' => [
                    'inspected' => 'Saya sudah memeriksa lokasi dan semua persyaratan di Daftar Pemeriksaan Pengaman terpenuhi.',
                    'area_safe' => 'Posisi utilitas bawah tanah (kabel, pipa, jaringan) sudah dipastikan dan area galian aman.',
                ],
                'close_checked' => [
                    'area_clean' => 'Saya sudah memeriksa lokasi setelah pekerjaan: galian sudah ditimbun / diberi pengaman, material sisa & rambu sudah dirapikan.',
                ],
            ],

            'texts' => [
                'checklist_title'   => 'Daftar Pemeriksaan Pengaman',
                'supervision_title' => 'Pengawasan',
                'perhatian'         => 'Sebelum Surat Izin Keselamatan dikeluarkan, pastikan area telah aman untuk dilakukan pekerjaan penggalian dan posisi utilitas bawah tanah (kabel, pipa, jaringan) sudah diketahui.',
                'declaration'       => 'posisi utilitas bawah tanah sudah dipastikan dan pengaman area galian sudah dipasang sesuai Daftar Pemeriksaan Pengaman',
                'tools'             => 'Contoh: excavator mini, cangkul, linggis, pompa air, cable locator',
                'daily_note'        => 'Contoh: lanjut galian jalur pipa segmen 2',
                'final_condition'   => 'Contoh: galian sudah ditimbun & dipadatkan, barikade dilepas, sisa tanah diangkut',
                'closeout_confirm'  => 'Galian sudah ditimbun / ditutup atau diberi pengaman, dan area aman untuk dilalui.',
                'stop_hint'         => 'mis. tanah retak / longsor, kabel atau pipa tidak terduga ditemukan, galian tergenang',
                'stop_example'      => 'Contoh: ditemukan kabel tidak ada di denah pada kedalaman 80 cm',
                'resume_example'    => 'Contoh: kabel sudah diidentifikasi & diamankan oleh tim electrical',
                'approve_note'      => 'Contoh: galian manual (tanpa excavator) dalam radius 1 m dari jalur kabel',
                'return_note'       => 'Contoh: denah jalur pipa belum dilampirkan, barikade belum ada',
                'return_close_note' => 'Contoh: galian belum ditimbun, rambu masih tertinggal di area',
            ],

            'distribution' => ['Ditempel – Asli', 'QHSE section – kuning'],
        ],

        // =============================================================
        // PEKERJAAN DALAM RUANG TERBATAS & TERTUTUP — form PTIBT/HSE/FRM/007/004 Rev. 0
        // =============================================================
        'confined_space' => [
            'code'        => 'CS',
            'label'       => 'Ruang Terbatas & Tertutup',
            'title'       => 'Surat Ijin Keselamatan Pekerjaan Dalam Ruang Terbatas dan Tertutup',
            'form_number' => 'PTIBT/HSE/FRM/007/004',
            'form_rev'    => '0',
            'max_days'    => 1,     // hanya 1 hari kerja
            'max_hours'   => 8,     // instruksi b: berlaku 8 jam, lebih dari itu ajukan permit baru
            'icon_color'  => '#7c3aed',
            'icon'        => 'box',

            'watcher' => [
                'label'       => 'Nama Petugas Jaga (Standby Person)',
                'short'       => 'Petugas jaga',
                'placeholder' => 'Orang yang berjaga di pintu masuk selama ada pekerja di dalam',
            ],

            'extra_notes' => 3,
            'work_types'  => [],

            // Batas aman sesuai form "Hasil Pengukuran Alat Gas Detector"
            // (menggantikan batas umum di atas khusus untuk jenis ini)
            'gas_limits' => [
                'o2'  => ['label' => 'O₂',  'unit' => '%',   'min' => 19.5, 'max' => 23.5],
                'co2' => ['label' => 'CO₂', 'unit' => 'ppm', 'min' => null, 'max' => 2500],
                'h2s' => ['label' => 'H₂S', 'unit' => 'ppm', 'min' => null, 'max' => 10],
                'lel' => ['label' => 'LEL', 'unit' => '%',   'min' => null, 'max' => 0],
            ],

            // Gas wajib diukur ulang setiap aktivasi (tepat sebelum pekerja masuk)
            'daily_gas_test' => true,

            'checklist' => [
                'ventilated'       => ['text' => 'Telah dilakukan Sirkulasi Udara di Dalam Ruang Terbatas Dan Tertutup.', 'allow_na' => false, 'note' => 'Contoh: blower 2 unit, 30 menit sebelum masuk'],
                'isolated'         => ['text' => 'Semua jalur yang terhubung dengan Ruang Terbatas dan Tertutup telah diputus (pipa dsb).', 'allow_na' => true, 'note' => 'Contoh: valve ditutup & dikunci (LOTO), blind flange'],
                'gas_measured'     => ['text' => 'Telah dilakukan pengukuran dengan Alat Gas Detector dan dinyatakan aman.', 'allow_na' => false, 'requires' => 'gas_test'],
                'material_removed' => ['text' => 'Material yang tersimpan dalam Ruang Terbatas dan Tertutup telah dipindahkan.', 'allow_na' => true],
                'hazmat_removed'   => ['text' => 'Bahan beracun, mudah terbakar/meledak telah dipindahkan.', 'allow_na' => true],
                'continuous_vent'  => ['text' => 'Dilakukan Sirkulasi Udara secara terus menerus dalam Ruang Terbatas dan Tertutup.', 'allow_na' => false],
                'safety_equipment' => ['text' => 'Telah dilengkapi dengan peralatan keselamatan kerja.', 'allow_na' => false, 'note' => 'Contoh: SCBA, tripod & winch, lampu 24V, radio'],
            ],

            'supervision' => [
                'continuous' => ['text' => 'Dilaksanakan pengawasan selama pekerjaan dilakukan.', 'stage' => 'daily'],
            ],

            'post_watch_minutes' => 0,

            'daily_confirmations' => [
                'ventilation' => 'Sirkulasi udara (blower / exhaust) berjalan terus menerus',
                'isolation'   => 'Semua jalur yang terhubung (pipa, listrik, mekanis) sudah diputus / dikunci',
                'standby'     => 'Petugas jaga siap di pintu masuk, alat komunikasi & penyelamatan tersedia',
            ],

            'check_confirmations' => [
                'checked' => [
                    'inspected' => 'Saya sudah memeriksa lokasi dan semua persyaratan di Daftar Pemeriksaan Pengaman terpenuhi.',
                    'area_safe' => 'Hasil ukur gas aman, sirkulasi udara berjalan, dan semua jalur yang terhubung sudah diputus.',
                ],
                'close_checked' => [
                    'area_clean' => 'Saya sudah memeriksa: semua pekerja sudah keluar, tidak ada alat / material tertinggal, akses sudah ditutup kembali.',
                ],
            ],

            'texts' => [
                'checklist_title'   => 'Daftar Pemeriksaan Pengaman',
                'supervision_title' => 'Pengawasan',
                'perhatian'         => 'Sebelum Surat Ijin Keselamatan dikeluarkan, pastikan area telah aman untuk dilakukan pekerjaan dalam ruang terbatas dan tertutup. Tidak boleh ada pekerja masuk sebelum hasil ukur gas dinyatakan aman.',
                'declaration'       => 'sirkulasi udara, isolasi jalur, dan pengukuran gas sudah dilakukan sesuai Daftar Pemeriksaan Pengaman',
                'tools'             => 'Contoh: blower, gas detector 4-gas, tripod & winch, lampu 24V, radio HT',
                'daily_note'        => 'Contoh: 3 orang masuk, inspeksi dinding dalam tangki',
                'final_condition'   => 'Contoh: 3 orang masuk – 3 orang keluar, alat dikeluarkan, manhole ditutup kembali',
                'closeout_confirm'  => 'Semua pekerja sudah keluar (jumlah sama dengan saat masuk) dan akses ruang terbatas sudah ditutup kembali.',
                'stop_hint'         => 'mis. gas detector berbunyi, blower mati, petugas jaga meninggalkan pintu masuk',
                'stop_example'      => 'Contoh: alarm gas detector berbunyi (O₂ turun ke 19%)',
                'resume_example'    => 'Contoh: sirkulasi udara 30 menit, diukur ulang O₂ 20,9%, LEL 0',
                'approve_note'      => 'Contoh: maksimal 2 orang di dalam, gas detector dipakai terus',
                'return_note'       => 'Contoh: hasil ukur gas belum diisi lengkap, SCBA belum tersedia',
                'return_close_note' => 'Contoh: jumlah pekerja keluar belum dicatat, manhole belum ditutup',
            ],

            'distribution' => ['Ditempel – Asli', 'QHSE section – kuning'],
        ],
    ],

];