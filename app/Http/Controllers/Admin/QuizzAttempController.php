<?php

namespace App\Http\Controllers\Admin;

use App\Models\HasQuestion;
use App\Models\Question;
use App\Models\QuizzAttemps;
use Illuminate\Http\Request;

class QuizzAttempController
{
    public function store(Request $request)
    {
        // Tạo attempt
        $attemp = QuizzAttemps::create([
            'quiz_id' => $request->quizId,
            'user_id' => $request->userId
        ]);

        $createdQuestions = [];

        $questions = $request->questions ?? [];

        foreach ($questions as $q) {
            $hasQuestion = $attemp->hasquestions()->create([
                'question_id' => $q['question_id'],
                'user_choices' => $q['choice'] ?? null
            ]);

            $createdQuestions[] = $hasQuestion;
        }

        return response()->json([
            'message' => 'Tạo attemp và hasquestion thành công',
            'data' => [
                'attemp' => $attemp,
                'has_questions' => $createdQuestions
            ]
        ]);
    }

    public function reviewQuizz($userId, $quizzId)
    {
        // Lấy attempt của user
        $attemp = QuizzAttemps::where('user_id', $userId)
            ->where('quiz_id', $quizzId)
            ->first();

        if (!$attemp) {
            return response()->json([
                'message' => 'User chưa thực hiện quiz này',
                'data' => null
            ], 404);
        }

        // Lấy tất cả câu hỏi của quiz này
        $questions = Question::where('quiz_id', $quizzId)
            ->with('answers')
            ->orderBy('order_index')
            ->get();

        // Lấy tất cả câu trả lời của user
        $userAnswers = HasQuestion::where('quiz_attemps_id', $attemp->quiz_attemps_id)
            ->pluck('user_choices', 'question_id')
            ->toArray();

        // Map dữ liệu
        $questionsData = $questions->map(function ($question) use ($userAnswers) {
            $userChoiceId = $userAnswers[$question->question_id] ?? null;

            // Tìm content của câu trả lời user chọn
            $userChoiceContent = null;
            if ($userChoiceId) {
                $userAnswer = $question->answers->firstWhere('answer_id', $userChoiceId);
                $userChoiceContent = $userAnswer ? $userAnswer->content : null;
            }

            return [
                'question_id' => $question->question_id,
                'question' => $question->content,
                'answers' => $question->answers->map(function ($ans) {
                    return [
                        'answer_id' => $ans->answer_id,
                        'content' => $ans->content
                    ];
                }),
                'user_choice_id' => $userChoiceId,
                'user_choice' => $userChoiceContent,
                'correct_answer_id' => $question->true_answer,
                'is_correct' => $userChoiceId == $question->true_answer
            ];
        });

        return response()->json([
            'message' => 'Review quiz thành công',
            'data' => $questionsData
        ]);
    }

    public function getAllAttemp($userId, $quizId)
    {
        $attempts = QuizzAttemps::where('user_id', $userId)
            ->where('quiz_id', $quizId)
            ->orderBy('quiz_attemps_id', 'desc')
            ->get();

        return response()->json([
            'message' => 'Danh sách attempt của user',
            'data' => $attempts
        ]);
    }
}
