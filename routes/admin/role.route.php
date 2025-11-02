<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\RoleController;

Route::get('/', [RoleController::class, 'index']);
Route::get('create', [RoleController::class, 'create']);
Route::post('/', [RoleController::class, 'store']);
Route::get('{id}/edit', [RoleController::class, 'edit']);
Route::patch('{id}', [RoleController::class, 'update']);
Route::delete('{id}', [RoleController::class, 'destroy']);

