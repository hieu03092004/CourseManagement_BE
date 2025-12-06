<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Client\QuizzController;

Route::get('/detail/{lessonId}', [QuizzController::class, 'detail']);
Route::post('/createattemp', [QuizzController::class, 'createattemp']);
Route::get('/getQuizzAttemp/{userId}/{lessonId}', [QuizzController::class, 'getQuizzAttemp']);
Route::get('/getQuestion/{id}', [QuizzController::class, 'getQuestion']);
Route::get('/getQuizAttemptDetail/{quizzAttempId}', [QuizzController::class, 'getQuizAttemptDetail']);
