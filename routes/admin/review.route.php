<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ReviewController;

// Lấy tất cả đánh giá của một khóa học
Route::get('course/{courseId}', [ReviewController::class, 'index']);

// Tạo mới một đánh giá cho khóa học
Route::post('course/{courseId}', [ReviewController::class, 'store']);

// Cập nhật một đánh giá
Route::put('update/{reviewId}', [ReviewController::class, 'update']);

// Xóa một đánh giá
Route::delete('delete/{reviewId}', [ReviewController::class, 'destroy']);

// Lấy thông tin tổng quan về đánh giá của khóa học (thông tin điểm trung bình và tổng số đánh giá)
Route::get('course/{courseId}/rating-info', [ReviewController::class, 'ratingInfo']);
