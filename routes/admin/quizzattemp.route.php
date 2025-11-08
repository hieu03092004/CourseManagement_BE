<?php

use App\Http\Controllers\Admin\QuizzAttempController;
use Illuminate\Support\Facades\Route;

Route::post('createattemp', [QuizzAttempController::class, 'store']);
Route::get('{userId}/{quizzId}/answers', [QuizzAttempController::class, 'reviewQuizz']);
Route::get('{userId}/{quizId}/allattemp', [QuizzAttempController::class, 'getAllAttemp']);
