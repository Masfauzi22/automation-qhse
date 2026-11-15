<?php

/*
|--------------------------------------------------------------------------
| Pengaturan Safety Campaign
|--------------------------------------------------------------------------
| Nilai awal. Kalau HSE minta diubah, cukup ganti di sini lalu:
| php artisan config:clear
*/

return [

    // Foto / poster per campaign
    'max_images'   => 5,
    'max_image_kb' => 5120, // 5 MB per foto

    // Soal pilihan ganda per campaign (0 = tanpa soal, cukup konfirmasi baca)
    'max_questions' => 3,
    'min_options'   => 2,
    'max_options'   => 4,

    // Komentar karyawan
    'max_comment_length' => 1000,

];