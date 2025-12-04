<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Client\CourseController;


// Lấy chi tiết khóa học theo cấu trúc FE
Route::get('/detail/{id}', [CourseController::class, 'detail']);

// Cập nhật khóa học theo cấu trúc FE
