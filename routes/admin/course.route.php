<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\CourseModuleController;
use App\Http\Controllers\Admin\ReviewController;


// Lấy tất cả khóa học
Route::get('/getAll', [CourseController::class, 'getAll']);

// Lấy chi tiết khóa học theo cấu trúc FE
Route::get('/details/{id}', [CourseController::class, 'details']);

// Cập nhật khóa học theo cấu trúc FE
Route::patch('/edit/{id}', [CourseController::class, 'edit']);

// Tạo khóa học mới
Route::post('/create', [CourseController::class, 'create']);

// Lấy toàn bộ danh sách khóa học của một user
Route::get('user/{userId}', [CourseController::class, 'getCoursesByUser']);

// Cập nhật thông tin khóa học
Route::put('{id}', [CourseController::class, 'update']);

// Xóa khóa học
Route::delete('/delete/{id}', [CourseController::class, 'delete']);


// Tạo module cho khóa học
Route::post('{courseId}/modules', [CourseModuleController::class, 'create']);

// Lấy danh sách module theo khóa học
Route::get('{courseId}/modules', [CourseModuleController::class, 'getModulesByCourse']);

//Lấy danh sách review theo khoá học
Route::get('{id}/reviews', [ReviewController::class, 'getReviews']);
