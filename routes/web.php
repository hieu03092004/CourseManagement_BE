<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return 'hello_world';
});

require __DIR__ . '/auth/auth.route.php';
require __DIR__ . '/admin/index.route.php';
