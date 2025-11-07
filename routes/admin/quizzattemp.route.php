<?php

use App\Http\Controllers\Admin\QuizzAttempController;
use Illuminate\Support\Facades\Route;

Route::post('createattemp', [QuizzAttempController::class, 'store']);
Route::post('{attempId}/{questionId}/hasquestion', [QuizzAttempController::class, 'createAnswer']);
Route::get('{userId}/{quizzId}/answers', [QuizzAttempController::class, 'reviewQuizz']);
Route::get('{userId}/allattemp', [QuizzAttempController::class, 'getAllAttemp']);
