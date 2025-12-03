<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\QuizzController;

Route::get('/getAll', [QuizzController::class, 'getAll']);
Route::post('/create/{lessonId}', [QuizzController::class, 'create']);
Route::post('', [QuizzController::class, 'store']);
Route::post('{quizId}/questions', [QuizzController::class, 'addQuestion']);
Route::post('{questionId}/answers', [QuizzController::class, 'addAnswer']);
Route::delete('delquestions/{questionId}', [QuizzController::class, 'deleteQuestion']);
Route::delete('delanswers/{answerId}', [QuizzController::class, 'deleteAnswer']);
Route::put('{questionId}/putquestion', [QuizzController::class, 'updateTrueAnswer']);
Route::get('{lessonId}/getquizz', [QuizzController::class, 'showquiz']);
Route::get('{quizzId}/getquestion', [QuizzController::class, 'showquestion']);
Route::get('{questionId}/getanswer', [QuizzController::class, 'showanswer']);
Route::patch('/changeStatus', [QuizzController::class, 'changeStatus']);
Route::delete('/delete/{id}', [QuizzController::class, 'deleteQuiz']);
Route::get('/details/{id}', [QuizzController::class, 'details']);
Route::post('/edit', [QuizzController::class, 'edit']);
