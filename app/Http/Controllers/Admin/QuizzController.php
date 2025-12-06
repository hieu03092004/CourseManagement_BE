<?php

namespace App\Http\Controllers\Admin;

use App\Models\Quizz;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Services\QuizzService;
use Illuminate\Http\Request;

class QuizzController extends BaseAPIController
{
    protected $quizzService;

    public function __construct(QuizzService $service)
    {
        $this->quizzService = $service;
    }

    public function getAll()
    {
        try {
            return $this->ok($this->quizzService->getAll());
        } catch (\Exception $e) {
            return $this->fail('Error', 500, 'INTERNAL_ERROR', ['message' => $e->getMessage()]);
        }
    }

    public function create(Request $request, $lessonId)
    {
        try {
            $data = $request->all();
            return $this->created($this->quizzService->createQuiz($data, $lessonId));
        } catch (\Exception $e) {
            return $this->fail('Create failed', 500, 'INTERNAL_ERROR', ['message' => $e->getMessage()]);
        }
    }

    public function store(Request $request)
    {
        try {
            $data = $request->json()->all();
            if (empty($data)) {
                $rawContent = $request->getContent();
                if (!empty($rawContent)) {
                    $data = json_decode($rawContent, true);
                }
            }

            $result = $this->quizzService->store($data);

            return $this->created($result);
        } catch (\InvalidArgumentException $e) {
            return $this->fail(
                'Invalid data format',
                422,
                'VALIDATION_ERROR',
                ['message' => $e->getMessage()]
            );
        } catch (\Exception $e) {
            return $this->fail(
                'An error occurred while creating quizzes',
                500,
                'INTERNAL_ERROR',
                ['message' => $e->getMessage()]
            );
        }
    }


    public function addQuestion(Request $request, $quizId)
    {
        $question = $this->quizzService->addQuestion($quizId, $request->all());

        return response()->json([
            'message' => 'Thêm câu hỏi thành công',
            'question' => $question
        ]);
    }


    public function addAnswer(Request $request, $questionId)
    {
        $answer = $this->quizzService->addAnswer($questionId, $request->all());

        return response()->json([
            'message' => 'Thêm đáp án thành công',
            'answer' => $answer
        ]);
    }

    public function deleteQuestion($questionId)
    {
        try {
            $this->quizzService->deleteQuestion($questionId);

            return response()->json([
                'message' => 'Xóa câu hỏi thành công'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Xóa câu hỏi thất bại',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteAnswer($answerId)
    {
        try {
            $this->quizzService->deleteAnswer($answerId);

            return response()->json([
                'message' => 'Xóa đáp án thành công'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Xóa đáp án thất bại',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateTrueAnswer(Request $request, $questionId)
    {
        try {
            $question = $this->quizzService->updateQuestion($questionId, $request->all());

            return response()->json([
                'message' => 'Cập nhật câu hỏi thành công',
                'question' => $question
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Cập nhật thất bại',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteQuiz($quizzId)
    {
        try {
            $this->quizzService->deleteQuiz($quizzId);

            return $this->ok([
                'message' => 'Xóa quizz thành công',
                'quiz_id' => $quizzId
            ]);
        } catch (ModelNotFoundException $e) {
            return $this->fail(
                'Quiz not found',
                404,
                'NOT_FOUND',
                ['message' => 'Quiz không tồn tại']
            );
        } catch (\Exception $e) {
            return $this->fail(
                'An error occurred while deleting quiz',
                500,
                'INTERNAL_ERROR',
                ['message' => $e->getMessage()]
            );
        }
    }

    // API hiển thị quiz
    public function showquiz($lessonId)
    {
        $quizz = Quizz::where('lesson_id', $lessonId)
            ->get();

        return response()->json([
            'message' => 'Lấy quiz thành công',
            'data' => $quizz
        ]);
    }

    public function showquestion($quizzId)
    {
        try {
            $questions = $this->quizzService->showQuestion($quizzId);

            return response()->json([
                'message' => 'Lấy questions thành công',
                'data'    => $questions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error while fetching questions',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function showanswer($questionId)
    {
        try {
            $answers = $this->quizzService->showAnswer($questionId);

            return response()->json([
                'message' => 'Lấy answers thành công',
                'data'    => $answers
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error while fetching answers',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function changeStatus(Request $request)
    {
        try {
            $res = $this->quizzService->changeStatus($request->quizzId, $request->status);
            return $this->ok($res);
        } catch (\Exception $e) {
            return $this->fail('Error', 500, 'INTERNAL_ERROR', ['message' => $e->getMessage()]);
        }
    }

    public function details($id)
    {
        try {
            $result = $this->quizzService->getQuizDetails($id);
            return $this->ok($result);
        } catch (ModelNotFoundException $e) {
            return $this->fail(
                'Quiz not found',
                404,
                'NOT_FOUND',
                ['message' => 'Quiz không tồn tại']
            );
        } catch (\Exception $e) {
            return $this->fail(
                'An error occurred while fetching quiz details',
                500,
                'INTERNAL_ERROR',
                ['message' => $e->getMessage()]
            );
        }
    }


    // API chỉnh sửa quiz
    public function edit(Request $request)
    {
        try {
            $res = $this->quizzService->editQuiz($request);

            return $this->ok([
                'message' => 'Cập nhật quiz thành công',
                'quiz_id' => $res
            ]);
        } catch (ModelNotFoundException $e) {
            return $this->fail('Quiz không tồn tại', 404, 'NOT_FOUND', ['message' => 'Quiz không tồn tại']);
        } catch (\Exception $e) {
            return $this->fail(
                'An error occurred while updating quiz',
                500,
                'INTERNAL_ERROR',
                ['message' => $e->getMessage()]
            );
        }
    }
}
