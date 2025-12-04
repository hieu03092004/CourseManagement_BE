<?php

use Illuminate\Support\Facades\Route;

use Illuminate\Http\Request;

Route::get('/reset-password/{token}', function (Request $request, string $token) {
    $frontend = env('FRONTEND_URL', 'http://localhost:3000');
    $email = $request->query('email');

    $url = rtrim($frontend, '/') . '/member/reset-password?token=' . urlencode($token);
    if ($email) {
        $url .= '&email=' . urlencode($email);
    }

    return redirect()->away($url);
})->name('password.reset');

require __DIR__ . '/auth/auth.route.php';
require __DIR__ . '/admin/index.route.php';
require __DIR__ . '/client/index.route.php';
