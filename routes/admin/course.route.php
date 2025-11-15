<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\CourseModuleController;


// Tạo khóa học mới
Route::post('', [CourseController::class, 'create']);

// Lấy toàn bộ danh sách khóa học của một user
Route::get('user/{userId}', [CourseController::class, 'getCoursesByUser']);

// Cập nhật thông tin khóa học
Route::put('{id}', [CourseController::class, 'update']);

// Xóa khóa học
Route::delete('{id}', [CourseController::class, 'delete']);

// Xem chi tiết khóa học
Route::get('{id}', [CourseController::class, 'detail']);

// Tạo module cho khóa học
Route::post('{courseId}/modules', [CourseModuleController::class, 'create']);

// Lấy danh sách module theo khóa học
Route::get('{courseId}/modules', [CourseModuleController::class, 'getModulesByCourse']);
