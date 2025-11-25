<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\EnrollmentController;

// Lấy danh sách giỏ hàng
Route::get('/{userId}', [EnrollmentController::class, 'getCart']);