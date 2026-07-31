<?php

return [

    /*
    |--------------------------------------------------------------------------
    | CAPTCHA Keamanan
    |--------------------------------------------------------------------------
    |
    | Captcha matematika sederhana (tanpa key eksternal / Google reCAPTCHA).
    | Aktifkan/nonaktifkan lewat variabel lingkungan CAPTCHA_ENABLED.
    |
    */

    'enabled' => (bool) env('CAPTCHA_ENABLED', true),

];
