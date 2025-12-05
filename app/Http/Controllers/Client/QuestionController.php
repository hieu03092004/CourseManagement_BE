<?php

namespace App\Http\Controllers\Client;

use App\Models\Question;
use App\Models\Quizz;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class QuestionController extends BaseAPIController
{
    public function detail($quizzId)
    {
        try {
            // Lấy thông tin quiz để lấy time_limit
            $quiz = Quizz::findOrFail($quizzId);

            // Query các question theo quiz_id và load answers
            $questions = Question::where('quiz_id', $quizzId)
                ->with('answers')
                ->get();

            // Map thành format response
            $result = $questions->map(function ($question) {
                // Lấy danh sách answers content
                $answers = $question->answers->pluck('content')->toArray();

                return [
                    'id' => $question->question_id,
                    'question' => $question->content,
                    'answers' => $answers,
                ];
            })->toArray();

            return $this->ok([
                'timeLimit' => $quiz->time_limit ?? 0,
                'questions' => $result,
            ]);

        } catch (\Exception $e) {
            return $this->fail(
                'An error occurred while fetching questions',
                500,
                'INTERNAL_ERROR',
                ['message' => $e->getMessage()]
            );
        }
    }
}

