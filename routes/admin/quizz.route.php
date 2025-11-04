<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\QuizzController;

Route::post('', [QuizzController::class, 'store']);
Route::post('{quizId}/questions', [QuizzController::class, 'addQuestion']);
Route::post('questions/{questionId}/answers', [QuizzController::class, 'addAnswer']);
Route::delete('questions/{questionId}', [QuizzController::class, 'deleteQuestion']);
Route::delete('answers/{answerId}', [QuizzController::class, 'deleteAnswer']);
Route::put('questions/{questionId}/true-answer', [QuizzController::class, 'updateTrueAnswer']);
