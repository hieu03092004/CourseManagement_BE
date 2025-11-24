<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\EnrollmentController;

// Xem trước thanh toán 1 khóa học
Route::get('/{userId}', [EnrollmentController::class, 'getOrders']);