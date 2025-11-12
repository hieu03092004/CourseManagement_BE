<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\CourseModuleController;

// Hiển thị danh sách tất cả các module
Route::get('/', [CourseModuleController::class, 'index'])->name('admin.coursemodule.index');

// Lấy các module theo khóa học
Route::get('course/{courseId}', [CourseModuleController::class, 'getByCourse'])->name('admin.coursemodule.getByCourse');

// Hiển thị module cơ bản theo ID
Route::get('{id}/basic', [CourseModuleController::class, 'showBasic'])->name('admin.coursemodule.showBasic');

// Hiển thị module chi tiết (kèm lessons + quizzes)
Route::get('{id}/detail', [CourseModuleController::class, 'showDetail'])->name('admin.coursemodule.showDetail');

// Tạo mới một module
Route::post('/', [CourseModuleController::class, 'store'])->name('admin.coursemodule.store');

// Cập nhật module
Route::put('{id}', [CourseModuleController::class, 'update'])->name('admin.coursemodule.update');

// Xóa module
Route::delete('{id}', [CourseModuleController::class, 'destroy'])->name('admin.coursemodule.destroy');
