<?php

namespace App\Http\Controllers\Client;

use App\Models\Quizz;
use App\Models\QuizzAttemps;
use App\Models\HasQuestion;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
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

    public function createattemp(Request $request)
    {
        try {
            $request->validate([
                'quizId' => ['required', 'integer'],
                'userId' => ['required', 'integer'],
                'answers' => ['required', 'array'],
                'answers.*.questionId' => ['required', 'integer'],
                'answers.*.choice' => ['required', 'integer'],
            ]);

            DB::beginTransaction();

            $quizAttempt = QuizzAttemps::create([
                'quiz_id' => $request->quizId,
                'user_id' => $request->userId,
            ]);

            foreach ($request->answers as $answer) {
                HasQuestion::create([
                    'quiz_attemps_id' => $quizAttempt->quiz_attemps_id,
                    'question_id' => $answer['questionId'],
                    'user_choices' => $answer['choice'],
                ]);
            }

            DB::commit();

            return $this->created([
                'message' => 'Quiz attempt created successfully',
                'quizAttemptId' => $quizAttempt->quiz_attemps_id,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return $this->fail(
                'Validation failed',
                422,
                'VALIDATION_ERROR',
                $e->errors()
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->fail(
                'An error occurred while creating quiz attempt',
                500,
                'INTERNAL_ERROR',
                ['message' => $e->getMessage()]
            );
        }
    }

    public function getQuizzAttemp($userId, $lessonId)
    {
        try {
            $quizzes = Quizz::where('lesson_id', $lessonId)->pluck('quiz_id');

            $quizAttempts = QuizzAttemps::where('user_id', $userId)
                ->whereIn('quiz_id', $quizzes)
                ->with('quiz')
                ->orderBy('created_at', 'desc')
                ->get();

            $result = $quizAttempts->map(function ($attempt) {
                $attemptDate = '';
                if ($attempt->created_at) {
                    $attemptDate = is_string($attempt->created_at)
                        ? Carbon::parse($attempt->created_at)->format('Y-m-d')
                        : $attempt->created_at->format('Y-m-d');
                }

                return [
                    'id' => $attempt->quiz_attemps_id,
                    'quizTitle' => $attempt->quiz->title ?? '',
                    'attemptDate' => $attemptDate,
                ];
            })->toArray();

            return $this->ok($result);

        } catch (\Exception $e) {
            return $this->fail(
                'An error occurred while fetching quiz attempts',
                500,
                'INTERNAL_ERROR',
                ['message' => $e->getMessage()]
            );
        }
    }

    public function getQuestion($quizId)
    {
        try {
            $questions = Question::where('quiz_id', $quizId)
                ->with('answers')
                ->get();

            $result = $questions->map(function ($question) {
                $answers = $question->answers->pluck('content')->toArray();

                return [
                    'id' => $question->question_id,
                    'quizId' => $question->quiz_id,
                    'question' => $question->content,
                    'answers' => $answers,
                    'correctAnswer' => $question->true_answer,
                ];
            })->toArray();

            return $this->ok($result);

        } catch (\Exception $e) {
            return $this->fail(
                'An error occurred while fetching questions',
                500,
                'INTERNAL_ERROR',
                ['message' => $e->getMessage()]
            );
        }
    }

    public function getQuizAttemptDetail($quizzAttempId)
    {
        try {
            $quizAttempt = QuizzAttemps::with(['quiz', 'hasquestions'])
                ->findOrFail($quizzAttempId);

            $answers = $quizAttempt->hasquestions->map(function ($hasQuestion) {
                return [
                    'questionId' => $hasQuestion->question_id,
                    'userAnswer' => $hasQuestion->user_choices,
                ];
            })->toArray();

            $result = [
                'quizId' => $quizAttempt->quiz_id,
                'answers' => $answers,
                'quizzName' => $quizAttempt->quiz->title ?? '',
            ];

            return $this->ok($result);

        } catch (ModelNotFoundException $e) {
            return $this->fail(
                'Quiz attempt not found',
                404,
                'NOT_FOUND',
                ['message' => 'Quiz attempt does not exist']
            );
        } catch (\Exception $e) {
            return $this->fail(
                'An error occurred while fetching quiz attempt detail',
                500,
                'INTERNAL_ERROR',
                ['message' => $e->getMessage()]
            );
        }
    }
}

