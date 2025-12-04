<?php

return [
    // Áp dụng CORS cho các route API / auth / admin / client
    'paths' => ['api/*', 'auth/*', 'admin/*', 'client/*'],

    'allowed_methods' => ['*'],

    // Không dùng '*' khi dùng credentials. Chỉ cho phép origin FE của bạn.
    'allowed_origins' => [
        'http://localhost:3000',
        'http://127.0.0.1:3000',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // Cho phép gửi cookie / credentials
    'supports_credentials' => true,
];

