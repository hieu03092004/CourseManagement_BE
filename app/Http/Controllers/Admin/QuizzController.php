<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Answer;
use App\Models\Question;
use App\Models\Quizz;
use Illuminate\Http\Request;

class QuizzController extends Controller
{
    public function store(Request $request)
    {
        $quizz = Quizz::create([
            'lesson_id' => $request->lesson_id
        ]);

        $quizzId = $quizz->quiz_id;

        $question = Question::create([
            'quiz_id' => $quizzId,
            'title' => $request->title,
            'content' => $request->content,
            'true_answer' => $request->true_answer,
            'order_index' => $request->order_index
        ]);

        $questionId = $question->question_id;

        $answer = Answer::create([
            'question_id' => $questionId,
            'content' => $request->answer_content
        ]);

        return response()->json([
            'message' => 'Tạo quizz, câu hỏi và câu trả lời thành công',
            'data' => [
                'quizz' => $quizz,
                'question' => $question,
                'answer' => $answer
            ]
        ]);
    }
}
