<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DiscussionController;

Route::post('/creatediscuss', [DiscussionController::class, 'create']);
Route::delete('/{discussionId}/deletediscuss', [DiscussionController::class, 'delete']);
Route::put('/{discussionId}/editdiscuss', [DiscussionController::class, 'edit']);
Route::get('/{quizId}/showdiscuss', [DiscussionController::class, 'showByQuiz']);
