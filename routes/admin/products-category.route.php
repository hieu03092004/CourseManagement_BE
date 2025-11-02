<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ProductCategoryController;

Route::get('/', [ProductCategoryController::class, 'index']);
Route::get('create', [ProductCategoryController::class, 'create']);
Route::post('/', [ProductCategoryController::class, 'store']);
Route::get('{id}/edit', [ProductCategoryController::class, 'edit']);
Route::patch('{id}', [ProductCategoryController::class, 'update']);
Route::delete('{id}', [ProductCategoryController::class, 'destroy']);

