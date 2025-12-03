<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\EmailVerification\EmailVerificationController;



Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::match(['get','post'], '/email/verify/{id}', [EmailVerificationController::class, 'verify'])
        ->name('api.email.verify');

    Route::post('/email/resend', [EmailVerificationController::class, 'resend']);
    Route::get('/me', [AuthController::class, 'me'])->middleware('auth:sanctum');
    Route::post('/forgot-password', [AuthController::class, 'forgot']);
    Route::post('/reset-password', [AuthController::class, 'reset']);
});
