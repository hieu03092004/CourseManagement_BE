<?php

use Illuminate\Support\Facades\Route;

$prefixAdmin = config('admin.prefixAdmin');

// Auth routes (no middleware)
Route::prefix($prefixAdmin . '/auth')->group(function () {
    require __DIR__ . '/auth.route.php';
});

// Protected admin routes (with auth middleware)
Route::prefix($prefixAdmin)->middleware('admin.auth')->group(function () {
    // Dashboard
    Route::prefix('dashboard')->group(function () {
        require __DIR__ . '/dashboard.route.php';
    });

    // Products
    Route::prefix('products')->group(function () {
        require __DIR__ . '/product.route.php';
    });

    // Product Categories
    Route::prefix('products-category')->group(function () {
        require __DIR__ . '/products-category.route.php';
    });

    // Roles
    Route::prefix('roles')->group(function () {
        require __DIR__ . '/role.route.php';
    });

    // Accounts
    Route::prefix('accounts')->group(function () {
        require __DIR__ . '/account.route.php';
    });

    // My Account
    Route::prefix('my-account')->group(function () {
        require __DIR__ . '/my-account.route.php';
    });

    // Settings
    Route::prefix('settings')->group(function () {
        require __DIR__ . '/setting.route.php';
    });

    // Test Database (development only)
    Route::prefix('test-database')->group(function () {
        require __DIR__ . '/test-database.route.php';
    });
});

