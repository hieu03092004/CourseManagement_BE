<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\CourseController;

Route::get('{courseId}', [CourseController::class, 'show']);
Route::get('/', [CourseController::class, 'index'])->name('courses.index');
Route::post('/', [CourseController::class, 'store'])->name('courses.store');
Route::put('/{id}', [CourseController::class, 'update'])->name('courses.update');
Route::delete('/{id}', [CourseController::class, 'destroy'])->name('courses.destroy');
