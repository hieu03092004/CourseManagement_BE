<?php

namespace App\Http\Controllers\Admin;

use App\Models\Answer;
use App\Models\Discussion;
use App\Models\ParentDiscussion;
use App\Models\Question;
use App\Models\Quizz;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class QuizzController extends BaseAPIController
{
    public function getAll()
    {
        try {
            $quizzes = Quizz::with(['lesson.courseModule.course'])->get();

            $result = $quizzes->map(function ($quiz) {
                $lessonName = $quiz->lesson->title ?? '';
                $courseName = $quiz->lesson->courseModule->course->title ?? '';

                return [
                    'id' => (string)$quiz->quiz_id,
                    'lessonName' => $lessonName,
                    'quizName' => $quiz->title ?? '',
                    'courseName' => $courseName,
                    'status' => $quiz->status ?? 'active'
                ];
            });

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

    public function create(Request $request, $lessonId)
    {
        try {
            $data = $request->json()->all();
            if (empty($data)) {
                $rawContent = $request->getContent();
                if (!empty($rawContent)) {
                    $data = json_decode($rawContent, true);
                }
            }

            if (empty($data) || !is_array($data)) {
                return $this->fail(
                    'Invalid data format',
                    422,
                    'VALIDATION_ERROR',
                    ['message' => 'Data must be an object with title, lessonId, and questions']
                );
            }

            if (!isset($data['title']) || !isset($data['questions']) || !is_array($data['questions'])) {
                return $this->fail(
                    'Invalid data format',
                    422,
                    'VALIDATION_ERROR',
                    ['message' => 'Data must contain title and questions array']
                );
            }

        $quizz = Quizz::create([
            'lesson_id' => $lessonId,
            'title' => $data['title'],
            'time_limit' => $data['timeLimit'] ?? 0,
            'status' => $data['status'] ?? 'active'
        ]);

            $result = [];

            foreach ($data['questions'] as $item) {
                $questionContent = $item['questionName'] ?? $item['question'] ?? '';
                if (empty($questionContent)) {
                    continue;
                }

                if (!isset($item['answers']) || !is_array($item['answers']) || empty($item['answers'])) {
                    continue;
                }

                if (!isset($item['trueAnswer'])) {
                    continue;
                }

                $answers = $item['answers'];
                $trueAnswerIndex = (int)$item['trueAnswer'];

                if ($trueAnswerIndex < 0 || $trueAnswerIndex >= count($answers)) {
                    continue;
                }

                $question = $quizz->questions()->create([
                    'content' => $questionContent,
                    'true_answer' => $trueAnswerIndex
                ]);

                $createdAnswers = [];

                foreach ($answers as $aIndex => $answerContent) {
                    $answer = $question->answers()->create([
                        'content' => $answerContent
                    ]);

                    $createdAnswers[] = [
                        'answer_id' => $answer->answer_id,
                        'content' => $answer->content
                    ];
                }

                $result[] = [
                    'question_id' => $question->question_id,
                    'question' => $question->content,
                    'true_answer' => $trueAnswerIndex,
                    'answers' => $createdAnswers
                ];
            }

        return $this->created([
            'quiz_id' => $quizz->quiz_id,
            'lesson_id' => $lessonId,
            'title' => $quizz->title,
            'timeLimit' => $quizz->time_limit,
            'status' => $quizz->status,
            'questions' => $result
        ]);

        } catch (ValidationException $e) {
            return $this->fail(
                'Validation failed',
                422,
                'VALIDATION_ERROR',
                $e->errors()
            );
        } catch (\Exception $e) {
            return $this->fail(
                'An error occurred while creating quiz',
                500,
                'INTERNAL_ERROR',
                ['message' => $e->getMessage()]
            );
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

            if (empty($data) || !is_array($data)) {
                return $this->fail(
                    'Invalid data format',
                    422,
                    'VALIDATION_ERROR',
                    ['message' => 'Data must be an array of objects']
                );
            }

            $result = [];

            foreach ($data as $index => $item) {
                if (!isset($item['lessonId']) || !isset($item['question']) || !isset($item['answers']) || !isset($item['trueAnswer'])) {
                    continue;
                }

                $lessonId = $item['lessonId'];
                $questionContent = $item['question'];
                $answers = $item['answers'];
                $trueAnswerIndex = (int)$item['trueAnswer'];

                if (!is_array($answers) || empty($answers)) {
                    continue;
                }

                if ($trueAnswerIndex < 0 || $trueAnswerIndex >= count($answers)) {
                    continue;
                }

                $quizz = Quizz::firstOrCreate([
                    'lesson_id' => $lessonId
                ]);

                $question = $quizz->questions()->create([
                    'content' => $questionContent,
                    'true_answer' => 0
                ]);

                $createdAnswers = [];
                $trueAnswerId = null;

                foreach ($answers as $aIndex => $answerContent) {
                    $answer = $question->answers()->create([
                        'content' => $answerContent
                    ]);

                    $createdAnswers[] = [
                        'answer_id' => $answer->answer_id,
                        'content' => $answer->content
                    ];

                    if ($aIndex === $trueAnswerIndex) {
                        $trueAnswerId = $answer->answer_id;
                    }
                }

                if ($trueAnswerId) {
                    $question->update(['true_answer' => $trueAnswerId]);
                }

                $result[] = [
                    'lesson_id' => $lessonId,
                    'quiz_id' => $quizz->quiz_id,
                    'question_id' => $question->question_id,
                    'question' => $question->content,
                    'true_answer_id' => $trueAnswerId,
                    'answers' => $createdAnswers
                ];
            }

            return $this->created($result);

        } catch (ValidationException $e) {
            return $this->fail(
                'Validation failed',
                422,
                'VALIDATION_ERROR',
                $e->errors()
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

    // API thêm câu hỏi cho quiz
    public function addQuestion(Request $request, $quizId)
    {
        // Tạo question
        $question = Question::create([
            'quiz_id' => $quizId,
            'content' => $request->content ?? 'Nội dung 1',
            'true_answer' => $request->true_answer ?? 0
        ]);

        $createdAnswers = [];

        // Lấy answers nếu có, nếu không thì tạo mặc định 1 đáp án
        $answers = $request->answers ?? [
            ['content' => 'Đáp án 1']
        ];

        foreach ($answers as $aIndex => $a) {
            $answerContent = $a['content'] ?? 'Đáp án 1';

            $answer = $question->answers()->create([
                'content' => $answerContent
            ]);

            $createdAnswers[] = $answer;
        }

        $question->answers = $createdAnswers;

        return response()->json([
            'message' => 'Thêm câu hỏi thành công',
            'question' => $question
        ]);
    }


    // API thêm đáp án cho câu hỏi
    public function addAnswer(Request $request, $questionId)
    {
        $answer = Answer::create([
            'question_id' => $questionId,
            'content' => $request->content ?? 'Đáp án 1'
        ]);

        return response()->json([
            'message' => 'Thêm đáp án thành công',
            'answer' => $answer
        ]);
    }

    // API xóa câu hỏi
    public function deleteQuestion($questionId)
    {
        $question = Question::findOrFail($questionId);
        $question->answers()->delete(); // Xóa luôn các đáp án
        $question->delete();

        return response()->json([
            'message' => 'Xóa câu hỏi thành công'
        ]);
    }

    // API xóa đáp án
    public function deleteAnswer($answerId)
    {
        $answer = Answer::findOrFail($answerId);
        $answer->delete();

        return response()->json([
            'message' => 'Xóa đáp án thành công'
        ]);
    }

    // API cập nhật câu hỏi
    public function updateTrueAnswer(Request $request, $questionId)
    {
        $question = Question::findOrFail($questionId);

        $answerId = $request->true_answer;
        $newcontent = $request->new_content;

        $question->content = $newcontent;
        $question->true_answer = $answerId;
        $question->save();

        return response()->json([
            'message' => 'Cập nhật câu hỏi thành công',
            'question' => $question
        ]);
    }

    // API xóa quizz
    public function deleteQuiz($quizzId)
    {
        try {
            $quizz = Quizz::findOrFail($quizzId);

            foreach ($quizz->quizzatemps as $attemp) {
                $attemp->hasquestions()->delete();
                $attemp->delete();
            }

            foreach ($quizz->questions as $question) {
                $question->answers()->delete();
                $question->delete();
            }

            foreach ($quizz->discussions as $discuss) {
                $this->deleteDiscussionTree($discuss->discussion_id);
            }

            $quizz->delete();

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

    private function deleteDiscussionTree($id)
    {
        $children = Discussion::where('parent_id', $id)->get();

        foreach ($children as $child) {
            $this->deleteDiscussionTree($child->discussion_id);
        }

        ParentDiscussion::where('parent_id', $id)->delete();

        Discussion::where('discussion_id', $id)->delete();
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

    // API hiển thị question
    public function showquestion($quizzId)
    {
        $questions = Question::where('quiz_id', $quizzId)
            ->get();

        return response()->json([
            'message' => 'Lấy questions thành công',
            'data' => $questions
        ]);
    }

    //API hiển thị answer
    public function showanswer($questionId)
    {
        $answers = Answer::where('question_id', $questionId)
            ->get();

        return response()->json([
            'message' => 'Lấy questions thành công',
            'data' => $answers
        ]);
    }

    // API thay đổi status của quiz
    public function changeStatus(Request $request)
    {
        try {
            $data = $request->json()->all();
            if (empty($data)) {
                $rawContent = $request->getContent();
                if (!empty($rawContent)) {
                    $data = json_decode($rawContent, true);
                }
            }

            if (empty($data) || !isset($data['quizzId']) || !isset($data['status'])) {
                return $this->fail(
                    'Invalid data format',
                    422,
                    'VALIDATION_ERROR',
                    ['message' => 'Data must contain quizzId and status']
                );
            }

            $quizz = Quizz::findOrFail($data['quizzId']);
            $quizz->status = $data['status'];
            $quizz->save();

            return $this->ok([
                'message' => 'Cập nhật status thành công',
                'quiz_id' => $quizz->quiz_id,
                'status' => $quizz->status
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
                'An error occurred while updating quiz status',
                500,
                'INTERNAL_ERROR',
                ['message' => $e->getMessage()]
            );
        }
    }

    // API lấy chi tiết quiz
    public function details($id)
    {
        try {
            $quizz = Quizz::with([
                'lesson.courseModule.course',
                'questions.answers'
            ])->findOrFail($id);

            $lessonName = $quizz->lesson->title ?? '';
            $courseName = $quizz->lesson->courseModule->course->title ?? '';

            $questions = $quizz->questions->map(function ($question) {
                $answers = $question->answers->map(function ($answer) {
                    return [
                        'answerId' => $answer->answer_id,
                        'answerName' => $answer->content
                    ];
                })->toArray();

                return [
                    'questionId' => $question->question_id,
                    'questionName' => $question->content,
                    'answers' => $answers,
                    'trueAnswer' => $question->true_answer
                ];
            })->toArray();

            $result = [
                'id' => (string)$quizz->quiz_id,
                'lessonName' => $lessonName,
                'quizName' => $quizz->title,
                'courseName' => $courseName,
                'status' => $quizz->status,
                'questions' => $questions
            ];

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
            $data = $request->json()->all();
            if (empty($data)) {
                $rawContent = $request->getContent();
                if (!empty($rawContent)) {
                    $data = json_decode($rawContent, true);
                }
            }

            if (empty($data) || !isset($data['quiz_id'])) {
                return $this->fail(
                    'Invalid data format',
                    422,
                    'VALIDATION_ERROR',
                    ['message' => 'Data must contain quiz_id']
                );
            }

            $quizId = $data['quiz_id'];
            $quizz = Quizz::findOrFail($quizId);

            DB::beginTransaction();

            // Xử lý xóa questions
            if (isset($data['deletedQuestionIds']) && is_array($data['deletedQuestionIds'])) {
                foreach ($data['deletedQuestionIds'] as $questionId) {
                    $question = Question::find($questionId);
                    if ($question && $question->quiz_id == $quizId) {
                        $question->answers()->delete();
                        $question->delete();
                    }
                }
            }

            // Xử lý tạo questions mới
            if (isset($data['questionsToCreate']) && is_array($data['questionsToCreate'])) {
                foreach ($data['questionsToCreate'] as $item) {
                    if (!isset($item['content']) || !isset($item['answers']) || !isset($item['true_answer'])) {
                        continue;
                    }

                    $question = Question::create([
                        'quiz_id' => $quizId,
                        'content' => $item['content'],
                        'true_answer' => $item['true_answer']
                    ]);

                    if (isset($item['answers']) && is_array($item['answers'])) {
                        foreach ($item['answers'] as $answerData) {
                            if (isset($answerData['content'])) {
                                Answer::create([
                                    'question_id' => $question->question_id,
                                    'content' => $answerData['content']
                                ]);
                            }
                        }
                    }
                }
            }

            // Xử lý cập nhật questions
            if (isset($data['questionsToUpdate']) && is_array($data['questionsToUpdate'])) {
                foreach ($data['questionsToUpdate'] as $item) {
                    if (!isset($item['question_id'])) {
                        continue;
                    }

                    $question = Question::where('question_id', $item['question_id'])
                        ->where('quiz_id', $quizId)
                        ->first();

                    if ($question) {
                        if (isset($item['content'])) {
                            $question->content = $item['content'];
                        }
                        if (isset($item['true_answer'])) {
                            $question->true_answer = $item['true_answer'];
                        }
                        $question->save();

                        // Cập nhật answers
                        if (isset($item['answers']) && is_array($item['answers'])) {
                            foreach ($item['answers'] as $answerData) {
                                if (isset($answerData['answer_id']) && isset($answerData['content'])) {
                                    $answer = Answer::where('answer_id', $answerData['answer_id'])
                                        ->where('question_id', $question->question_id)
                                        ->first();

                                    if ($answer) {
                                        $answer->content = $answerData['content'];
                                        $answer->save();
                                    }
                                }
                            }
                        }
                    }
                }
            }

            DB::commit();

            return $this->ok([
                'message' => 'Cập nhật quiz thành công',
                'quiz_id' => $quizId
            ]);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return $this->fail(
                'Quiz not found',
                404,
                'NOT_FOUND',
                ['message' => 'Quiz không tồn tại']
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->fail(
                'An error occurred while updating quiz',
                500,
                'INTERNAL_ERROR',
                ['message' => $e->getMessage()]
            );
        }
    }
}
