<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    */

    'paths' => ['*'], // <<< تغییر: همه مسیرها را شامل شود

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*'], // <<< تغییر: اجازه به همه دامنه‌ها (برای محیط توسعه)

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
