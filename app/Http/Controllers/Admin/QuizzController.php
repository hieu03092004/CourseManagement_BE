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

        // Nếu không có questions gửi lên, tạo mặc định 1 câu hỏi + 1 đáp án
        $questions = $request->questions ?? [
            [
                'content' => 'Nội dung 1',
                'true_answer' => 0,
                'order_index' => 1,
                'answers' => [
                    ['content' => 'Đáp án 1']
                ]
            ]
        ];

        foreach ($questions as $qIndex => $q) {
            $content = $q['content'] ?? 'Nội dung 1';
            $true_answer = $q['true_answer'] ?? 0;
            $orderIndex = $q['order_index'] ?? ($qIndex + 1);

            // Tạo question
            $question = $quizz->questions()->create([
                'content' => $content,
                'true_answer' => $true_answer,
                'order_index' => $orderIndex
            ]);

            $createdAnswers = [];

            // Nếu không có answers, tạo mặc định 1 đáp án
            $answers = $q['answers'] ?? [
                ['content' => 'Đáp án 1']
            ];

            foreach ($answers as $aIndex => $a) {
                $answerContent = $a['content'] ?? 'Đáp án 1';

                // Tạo answer
                $answer = $question->answers()->create([
                    'content' => $answerContent
                ]);

                $createdAnswers[] = $answer;
            }

            $question->answers = $createdAnswers;
            $createdQuestions[] = $question;
        }

        return response()->json([
            'message' => 'Tạo quiz thành công',
            'data' => [
                'quizz' => $quizz,
                'questions' => $createdQuestions
            ]
        ]);
    }

    // API thêm câu hỏi cho quiz
    public function addQuestion(Request $request, $quizId)
    {
        $question = Question::create([
            'quiz_id' => $quizId,
            'content' => $request->content ?? 'Nội dung 1',
            'true_answer' => $request->true_answer ?? 0,
            'order_index' => $request->order_index ?? (Question::where('quiz_id', $quizId)->count() + 1)
        ]);

        // Tạo mặc định 1 đáp án nếu không có
        $answer = Answer::create([
            'question_id' => $question->id,
            'content' => 'Đáp án 1'
        ]);

        $question->answers = [$answer];

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
        $index = $request->new_index;

        $question->content = $newcontent;
        $question->true_answer = $answerId;
        $question->order_index = $index;
        $question->save();

        return response()->json([
            'message' => 'Cập nhật câu hỏi thành công',
            'question' => $question
        ]);
    }

    // API xóa quizz
    public function deleteQuiz($quizzId)
    {
        $quizz = Quizz::findOrFail($quizzId);

        foreach ($quizz->questions as $question) {
            $question->answers()->delete();
            $question->delete();
        }

        $quizz->delete();

        return response()->json([
            'message' => 'Xóa quizz thành công',
            'quizz' => $quizzId
        ]);
    }

    // API hiển thị quiz
    public function show($quizzId)
    {
        $quizz = Quizz::findOrFail($quizzId);

        $questionsData = [];

        foreach ($quizz->questions as $question) {
            $answers = $question->answers()->get();

            $questionsData[] = [
                'id' => $question->question_id,
                'content' => $question->content,
                'true_answer' => $question->true_answer,
                'order_index' => $question->order_index,
                'answers' => $answers
            ];
        }

        return response()->json([
            'message' => 'Lấy quiz thành công',
            'data' => [
                'quizz' => $quizz,
                'questions' => $questionsData
            ]
        ]);
    }
}
