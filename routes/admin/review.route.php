<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ReviewController;

Route::get('/courses/{id}/reviews', [ReviewController::class, 'getReviews']);
Route::post('/reviews', [ReviewController::class, 'store']);
Route::put('/reviews/{id}', [ReviewController::class, 'update']);
Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);
