<?php

namespace App\Http\Controllers\Client;

use App\Models\Quizz;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class QuizzController extends BaseAPIController
{
    public function detail($lessonId)
    {
        try {
            // Query các quiz theo lesson_id
            $quizzes = Quizz::where('lesson_id', $lessonId)->get();

            // Map thành format response
            $result = $quizzes->map(function ($quiz) {
                return [
                    'id' => $quiz->quiz_id,
                    'title' => $quiz->title,
                ];
            })->toArray();

            return $this->ok($result);

        } catch (\Exception $e) {
            return $this->fail(
                'An error occurred while fetching quizzes',
                500,
                'INTERNAL_ERROR',
                ['message' => $e->getMessage()]
            );
        }
    }
}

