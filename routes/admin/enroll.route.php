<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EnrollmentController;

// Xem trước thanh toán 1 khóa học
Route::get('/preview-single/{courseId}', [EnrollmentController::class, 'previewSingleCourse']);

// Thanh toán 1 khóa học
Route::post('/pay-single', [EnrollmentController::class, 'paySingleCourse']);

// Thêm khóa học vào giỏ hàng
Route::post('/cart/add', [EnrollmentController::class, 'addToCart']);

// Lấy danh sách giỏ hàng
Route::get('/cart/{userId}', [EnrollmentController::class, 'getCart']);

// Xem trước đơn hàng nhiều khóa học (từ giỏ hàng)
Route::post('/preview-cart-order', [EnrollmentController::class, 'previewCartOrder']);

// Thanh toán khóa học từ giỏ hàng
Route::post('/pay-cart', [EnrollmentController::class, 'payFromCart']);
