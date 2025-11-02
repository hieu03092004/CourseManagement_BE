<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AccountController;

Route::get('/', [AccountController::class, 'index']);
Route::get('create', [AccountController::class, 'create']);
Route::post('/', [AccountController::class, 'store']);
Route::get('{id}/edit', [AccountController::class, 'edit']);
Route::patch('{id}', [AccountController::class, 'update']);
Route::delete('{id}', [AccountController::class, 'destroy']);

