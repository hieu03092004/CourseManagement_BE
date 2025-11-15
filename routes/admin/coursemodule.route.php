<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\CourseModuleController;

// Cập nhật module
Route::put('{id}', [CourseModuleController::class, 'update']);

// Xóa module
Route::delete('{id}', [CourseModuleController::class, 'delete']);
