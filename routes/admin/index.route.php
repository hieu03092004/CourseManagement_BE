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
    // Quizz
    Route::prefix('quizz')->group(function () {
        require __DIR__ . '/quizz.route.php';
    });
    //Lesson
    Route::prefix('lesson')->group(function () {
        require __DIR__ . '/lesson.route.php';
    });
    //QuizzAttemp
    Route::prefix('attemp')->group(function () {
        require __DIR__ . '/quizzattemp.route.php';
    });
    //Discussion
    Route::prefix('discuss')->group(function () {
        require __DIR__ . '/discussion.route.php';
    });
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

