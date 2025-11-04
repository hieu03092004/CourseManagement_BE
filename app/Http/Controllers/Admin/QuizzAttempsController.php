<?php

namespace App\Http\Controllers\Admin;

use App\Models\HasQuesstion;
use App\Models\QuizzAttemps;
use Illuminate\Http\Request;

class QuizzAttempController
{
    public function store(Request $request)
    {
        $attemp = QuizzAttemps::create([
            'quiz_id' => $request->quizId,
            'user_id' => $request->userId
        ]);

        return response()->json([
            'message' => 'Tạo attemp thành công',
            'data' => $attemp
        ]);
    }

    public function createAnswer(Request $request, $attempId, $questionId)
    {
        $ques = HasQuesstion::create([
            'quiz_attemps_id' => $attempId,
            'question_id' => $questionId,
            'user_choices' => $request->choice
        ]);

        return response()->json([
            'message' => 'Tạo hasquestion thành công',
            'data' => $ques
        ]);
    }

    public function reviewQuizz($userId, $quizzId)
    {
        $attemp = QuizzAttemps::with(['hasQuestions.question.answers'])
            ->where('user_id', $userId)
            ->where('quiz_id', $quizzId)
            ->first();

        if (!$attemp) {
            return response()->json([
                'message' => 'User chưa thực hiện attempt cho quiz này',
                'data' => null
            ], 404);
        }

        // Chuẩn bị dữ liệu câu hỏi + user choice
        $questionsData = $attemp->hasQuestions->map(function ($hq) {
            return [
                'question_id' => $hq->question_id,
                'content' => $hq->question->content,
                'answers' => $hq->question->answers->map(function ($ans) {
                    return [
                        'id' => $ans->id,
                        'content' => $ans->content
                    ];
                }),
                'user_choice' => $hq->user_choices,
                'is_correct' => $hq->user_choices == $hq->question->true_answer
            ];
        });

        return response()->json([
            'message' => 'Review quiz thành công',
            'data' => [
                'attempt' => $attemp,
                'questions' => $questionsData
            ]
        ]);
    }
}
