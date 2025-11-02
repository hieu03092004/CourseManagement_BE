<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ProductController;

Route::get('/', [ProductController::class, 'index']);
Route::get('create', [ProductController::class, 'create']);
Route::post('/', [ProductController::class, 'store']);
Route::get('/edit/{id}', [ProductController::class, 'edit']);
Route::patch('{id}', [ProductController::class, 'update']);
Route::delete('{id}', [ProductController::class, 'destroy']);

