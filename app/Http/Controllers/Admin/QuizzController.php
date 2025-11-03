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
        // Tạo quiz
        $quizz = Quizz::create([
            'lesson_id' => $request->lesson_id
        ]);

        $createdQuestions = [];

        // Tạo câu hỏi
        foreach ($request->questions as $q) {
            $question = Question::create([
                'quiz_id' => $quizz->quiz_id,
                'title' => $q['title'],
                'content' => $q['content'] ?? null,
                'true_answer' => $q['true_answer'],
                'order_index' => $q['order_index']
            ]);

            $createdAnswers = [];

            // Tạo câu trả lời
            foreach ($q['answers'] as $a) {
                $answer = Answer::create([
                    'question_id' => $question->question_id,
                    'content' => $a['content']
                ]);
                $createdAnswers[] = $answer;
            }

            $question->answers = $createdAnswers;
            $createdQuestions[] = $question;
        }

        return response()->json([
            'message' => 'Tạo quizz, câu hỏi và câu trả lời thành công',
            'data' => [
                'quizz' => $quizz,
                'questions' => $createdQuestions
            ]
        ]);
    }
}
