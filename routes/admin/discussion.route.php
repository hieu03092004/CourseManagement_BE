<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DiscussionController;

Route::post('/creatediscuss', [DiscussionController::class, 'create']);
Route::delete('/{discussionId}/deletediscuss', [DiscussionController::class, 'delete']);
Route::put('/{discussionId}/editdiscuss', [DiscussionController::class, 'edit']);
Route::get('/{quizId}/showdiscuss', [DiscussionController::class, 'showByQuiz']);
Route::get('/{quizId}/getparent', [DiscussionController::class, 'getParentByQuiz']);
Route::get('/{quizId}/getallparent', [DiscussionController::class, 'getAllParentByQuiz']);
Route::get('/{parentId}/getchildren', [DiscussionController::class, 'getChildByParent']);
Route::get('/{parentId}/getallchildren', [DiscussionController::class, 'getAllChildByParent']);
Route::get('/{quizId}/countdiscuss', [DiscussionController::class, 'countDiscussionByQuiz']);
Route::get('/{parentId}/countchild', [DiscussionController::class, 'countReplyByParent']);
