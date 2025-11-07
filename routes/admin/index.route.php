<?php

use Illuminate\Support\Facades\Route;

$prefixAdmin = config('admin.prefixAdmin');

// Auth routes (no middleware)
Route::prefix($prefixAdmin . '/auth')->group(function () {
    require __DIR__ . '/auth.route.php';
});

// Protected admin routes (with auth middleware)
Route::prefix($prefixAdmin)->group(function () {
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
    // Route::prefix('test-database')->group(function () {
    //     require __DIR__ . '/test-database.route.php';
    // });

    // Courses
    Route::prefix('courses')->group(function () {
        require __DIR__ . '/course.route.php';
    });

    // CourseModule
    Route::prefix('coursesmodule')->group(function () {
        require __DIR__ . '/coursemodule.route.php';
    });

    Route::prefix('reviews')->group(function () {
        require __DIR__ . '/review.route.php';  // Bao gồm file review.route.php vào đây
    });
});
