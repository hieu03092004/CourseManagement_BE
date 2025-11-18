<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ReviewController;

Route::post('/', [ReviewController::class, 'create']);
Route::put('/{id}', [ReviewController::class, 'update']);
Route::delete('/{id}', [ReviewController::class, 'delete']);
