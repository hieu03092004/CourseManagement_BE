<?php

namespace App\Services;

use App\Models\Quizz;
use App\Models\Question;
use App\Models\Answer;
use App\Models\Discussion;
use App\Models\ParentDiscussion;
use Illuminate\Support\Facades\DB;

class QuizzService
{
    public function getAll()
    {
        $quizzes = Quizz::with(['lesson.courseModule.course'])->get();

        return $quizzes->map(function ($quiz) {
            return [
                'id' => (string)$quiz->quiz_id,
                'lessonName' => $quiz->lesson->title ?? '',
                'quizName' => $quiz->title ?? '',
                'courseName' => $quiz->lesson->courseModule->course->title ?? '',
                'status' => $quiz->status ?? 'active'
            ];
        });
    }

    public function createQuiz($data, $lessonId)
    {
        $quizz = Quizz::create([
            'lesson_id' => $lessonId,
            'title' => $data['title'],
            'time_limit' => $data['timeLimit'] ?? 0,
            'status' => $data['status'] ?? 'active'
        ]);

        $result = [];

        foreach ($data['questions'] as $item) {
            $question = $quizz->questions()->create([
                'content' => $item['questionName'] ?? $item['question'],
                'true_answer' => (int)$item['trueAnswer']
            ]);

            $answersList = [];

            foreach ($item['answers'] as $ans) {
                $answer = $question->answers()->create(['content' => $ans]);
                $answersList[] = ['answer_id' => $answer->answer_id, 'content' => $answer->content];
            }

            $result[] = [
                'question_id' => $question->question_id,
                'question'   => $question->content,
                'true_answer' => $question->true_answer,
                'answers'    => $answersList
            ];
        }

        return [
            'quiz_id' => $quizz->quiz_id,
            'lesson_id' => $lessonId,
            'title' => $quizz->title,
            'timeLimit' => $quizz->time_limit,
            'status' => $quizz->status,
            'questions' => $result
        ];
    }

    public function store(array $data)
    {
        if (empty($data) || !is_array($data)) {
            throw new \InvalidArgumentException('Data must be an array of objects');
        }

        $result = [];

        foreach ($data as $item) {
            if (!isset($item['lessonId'], $item['question'], $item['answers'], $item['trueAnswer'])) {
                continue;
            }

            $lessonId = $item['lessonId'];
            $questionContent = $item['question'];
            $answers = $item['answers'];
            $trueAnswerIndex = (int)$item['trueAnswer'];

            if (!is_array($answers) || empty($answers)) continue;
            if ($trueAnswerIndex < 0 || $trueAnswerIndex >= count($answers)) continue;

            $quizz = Quizz::firstOrCreate(['lesson_id' => $lessonId]);

            $question = $quizz->questions()->create([
                'content' => $questionContent,
                'true_answer' => 0
            ]);

            $trueAnswerId = null;
            $createdAnswers = [];

            foreach ($answers as $index => $ans) {
                $answer = $question->answers()->create(['content' => $ans]);

                $createdAnswers[] = [
                    'answer_id' => $answer->answer_id,
                    'content' => $answer->content
                ];

                if ($index === $trueAnswerIndex) {
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

        return $result;
    }

    public function addQuestion($quizId, array $data)
    {
        $question = Question::create([
            'quiz_id'     => $quizId,
            'content'     => $data['content'] ?? 'Nội dung 1',
            'true_answer' => $data['true_answer'] ?? 0
        ]);

        $answersData = $data['answers'] ?? [
            ['content' => 'Đáp án 1']
        ];

        $createdAnswers = [];

        foreach ($answersData as $a) {
            $answerContent = $a['content'] ?? 'Đáp án 1';

            $answer = $question->answers()->create([
                'content' => $answerContent
            ]);

            $createdAnswers[] = $answer;
        }

        $question->answers = $createdAnswers;

        return $question;
    }

    public function addAnswer($questionId, array $data)
    {
        return Answer::create([
            'question_id' => $questionId,
            'content' => $data['content'] ?? 'Đáp án 1'
        ]);
    }

    public function deleteQuestion($questionId)
    {
        $question = Question::findOrFail($questionId);

        // Xóa đáp án trước
        $question->answers()->delete();

        // Xóa câu hỏi
        $question->delete();

        return true;
    }

    public function deleteAnswer($answerId)
    {
        $answer = Answer::findOrFail($answerId);
        $answer->delete();

        return true;
    }

    public function updateQuestion($questionId, $data)
    {
        $question = Question::findOrFail($questionId);

        if (isset($data['new_content'])) {
            $question->content = $data['new_content'];
        }

        if (isset($data['true_answer'])) {
            $question->true_answer = $data['true_answer'];
        }

        $question->save();

        return $question;
    }

    public function deleteQuiz($quizzId)
    {
        $quizz = Quizz::findOrFail($quizzId);

        // Xoá tất cả Discussion theo dạng cây
        foreach ($quizz->discussions as $discuss) {
            $this->deleteDiscussionTree($discuss->discussion_id);
        }

        // Xoá question + answers
        foreach ($quizz->questions as $question) {
            $question->answers()->delete();
            $question->delete();
        }

        // Xoá quizz attempt (nếu có)
        foreach ($quizz->quizzatemps as $attempt) {
            $attempt->hasquestions()->delete();
            $attempt->delete();
        }

        // Xoá quiz
        $quizz->delete();
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

    public function showQuestion($quizId)
    {
        return Question::where('quiz_id', $quizId)->get();
    }

    public function showAnswer($questionId)
    {
        return Answer::where('question_id', $questionId)->get();
    }

    public function getQuizDetails($id)
    {
        $quizz = Quizz::with([
            'lesson.courseModule.course',
            'questions.answers'
        ])->findOrFail($id);

        $lessonName = $quizz->lesson->title ?? '';
        $courseName = $quizz->lesson->courseModule->course->title ?? '';

        $questions = $quizz->questions->map(function ($question) {
            $answers = $question->answers->map(function ($answer) {
                return [
                    'answerId'   => $answer->answer_id,
                    'answerName' => $answer->content
                ];
            })->toArray();

            return [
                'questionId'  => $question->question_id,
                'questionName' => $question->content,
                'answers'     => $answers,
                'trueAnswer'  => $question->true_answer
            ];
        })->toArray();

        return [
            'id'         => (string)$quizz->quiz_id,
            'lessonName' => $lessonName,
            'quizName'   => $quizz->title,
            'courseName' => $courseName,
            'status'     => $quizz->status,
            'questions'  => $questions
        ];
    }

    public function changeStatus($id, $status)
    {
        $quizz = Quizz::findOrFail($id);
        $quizz->status = $status;
        $quizz->save();
        return $quizz;
    }

    public function editQuiz($request)
    {
        $data = $request->json()->all();
        if (empty($data)) {
            $rawContent = $request->getContent();
            if (!empty($rawContent)) {
                $data = json_decode($rawContent, true);
            }
        }

        if (empty($data) || !isset($data['quiz_id'])) {
            throw new \Exception('Data must contain quiz_id');
        }

        $quizId = $data['quiz_id'];
        $quizz = Quizz::findOrFail($quizId);

        DB::beginTransaction();

        try {

            // Xóa question
            if (isset($data['deletedQuestionIds'])) {
                foreach ($data['deletedQuestionIds'] as $questionId) {
                    $question = Question::find($questionId);
                    if ($question && $question->quiz_id == $quizId) {
                        $question->answers()->delete();
                        $question->delete();
                    }
                }
            }

            // Tạo question mới
            if (isset($data['questionsToCreate'])) {
                foreach ($data['questionsToCreate'] as $item) {
                    if (!isset($item['content']) || !isset($item['answers']) || !isset($item['true_answer'])) continue;

                    $question = Question::create([
                        'quiz_id' => $quizId,
                        'content' => $item['content'],
                        'true_answer' => $item['true_answer']
                    ]);

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

            // Cập nhật question
            if (isset($data['questionsToUpdate'])) {
                foreach ($data['questionsToUpdate'] as $item) {
                    if (!isset($item['question_id'])) continue;

                    $question = Question::where('question_id', $item['question_id'])
                        ->where('quiz_id', $quizId)->first();

                    if ($question) {
                        if (isset($item['content'])) $question->content = $item['content'];
                        if (isset($item['true_answer'])) $question->true_answer = $item['true_answer'];
                        $question->save();

                        // update answer
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

            DB::commit();
            return $quizId;
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
}
