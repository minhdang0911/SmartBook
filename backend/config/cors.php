<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    // ✅ Ghi đúng chính xác origin đang chạy FE (Next.js, React)
    'allowed_origins' => ['http://localhost:3000'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // ✅ ĐANG DÙNG JWT/TOKEN thì PHẢI true
    'supports_credentials' => true,
];
