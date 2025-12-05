<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Client\QuestionController;

Route::get('/detail/{quizzId}', [QuestionController::class, 'detail']);

