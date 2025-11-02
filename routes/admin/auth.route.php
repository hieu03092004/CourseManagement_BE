<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;

Route::get('login', [AuthController::class, 'login']);
Route::post('login', [AuthController::class, 'loginPost']);
Route::post('logout', [AuthController::class, 'logout']);

