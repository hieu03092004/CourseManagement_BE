<?php

namespace App\Http\Controllers\Admin;

use App\Models\HasQuestion;
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
        $ques = HasQuestion::create([
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
        $attemp = QuizzAttemps::with(['hasquestions.question.answers'])
            ->where('user_id', $userId)
            ->where('quiz_id', $quizzId)
            ->first();

        if (!$attemp) {
            return response()->json([
                'message' => 'User chưa thực hiện attempt cho quiz này',
                'data' => null
            ], 404);
        }

        $questionsData = $attemp->hasquestions->map(function ($hq) {

            $answersMap = $hq->question->answers->pluck('content', 'id');

            $userChoiceContent = $answersMap[$hq->user_choices] ?? null;

            return [
                'question' => $hq->question->content,
                'answers' => $answersMap->values(),
                'user_choice' => $userChoiceContent,
                'is_correct' => $hq->user_choices == $hq->question->true_answer
            ];
        });

        return response()->json([
            'message' => 'Review quiz thành công',
            'data' => $questionsData
        ]);
    }


    public function getAllAttemp($userId)
    {
        $attempts = QuizzAttemps::where('user_id', $userId)
            ->orderBy('quiz_attemps_id', 'desc')
            ->get();

        if ($attempts->isEmpty()) {
            return response()->json([
                'message' => 'User chưa thực hiện attempt nào',
                'data' => []
            ], 404);
        }

        return response()->json([
            'message' => 'Danh sách attempt của user',
            'data' => $attempts
        ]);
    }
}
