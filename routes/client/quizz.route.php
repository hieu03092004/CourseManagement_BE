<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Client\QuizzController;

Route::get('/detail/{lessonId}', [QuizzController::class, 'detail']);

