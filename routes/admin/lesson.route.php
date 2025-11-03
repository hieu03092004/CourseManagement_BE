<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\LessonController;

Route::post('/lesson', [LessonController::class, 'store']);
