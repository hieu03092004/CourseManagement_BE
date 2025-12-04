<?php

return [
    'app_id' => env('ZALOPAY_APP_ID'),
    'key1' => env('ZALOPAY_KEY1'),
    'key2' => env('ZALOPAY_KEY2'),
    'endpoint' => env('ZALOPAY_ENDPOINT', 'https://sb-openapi.zalopay.vn/v2/create'),
    'query_endpoint' => env('ZALOPAY_QUERY_ENDPOINT', 'https://sb-openapi.zalopay.vn/v2/query'),
    'callback_url' => env('ZALOPAY_CALLBACK_URL'),
    'return_url' => env('ZALOPAY_RETURN_URL'),
];
