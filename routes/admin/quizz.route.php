<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\QuizzController;

Route::post('/quizz', [QuizzController::class, 'store']);
