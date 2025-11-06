<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\LessonController;

Route::post('/create', [LessonController::class, 'store']);
Route::get('/{id}/show', [LessonController::class, 'show']);
Route::post('/{id}/update', [LessonController::class, 'update']);
Route::delete('/{id}/delete', [LessonController::class, 'delete']);
Route::get('/{courses_module_id}/showbymodule', [LessonController::class, 'showByModule']);
