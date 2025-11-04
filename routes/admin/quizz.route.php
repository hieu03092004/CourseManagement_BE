<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\QuizzController;

Route::post('', [QuizzController::class, 'store']);
Route::post('{quizId}/questions', [QuizzController::class, 'addQuestion']);
Route::post('{questionId}/answers', [QuizzController::class, 'addAnswer']);
Route::delete('delquestions/{questionId}', [QuizzController::class, 'deleteQuestion']);
Route::delete('delanswers/{answerId}', [QuizzController::class, 'deleteAnswer']);
Route::delete('delquizz/{quizzId}', [QuizzController::class, 'deleteQuiz']);
Route::put('{questionId}/putquestion', [QuizzController::class, 'updateTrueAnswer']);
Route::get('{quizzId}/getquizz', [QuizzController::class, 'show']);
