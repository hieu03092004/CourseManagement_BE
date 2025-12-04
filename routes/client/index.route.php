<?php

use Illuminate\Support\Facades\Route;

$prefixClient = config('client.prefixClient');
// Client routes
Route::prefix($prefixClient)->group(function () {
    Route::prefix('courses')->group(function () {
        require __DIR__ . '/course.route.php';
    });

    Route::prefix('cart')->group(function () {
        require __DIR__ . '/cart.route.php';
    });
});

