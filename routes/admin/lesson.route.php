<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\LessonController;

Route::post('/', [LessonController::class, 'store']);
