<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;

class CourseController extends Controller
{
    // 1. Tạo khóa học mới
    public function create(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'price' => 'required|numeric',
            'type' => 'required',
        ]);

        $course = Course::create([
            'user_id' => $request->user_id ?? null,
            'title' => $request->title,
            'description' => $request->description,
            'target' => $request->target,
            'result' => $request->result,
            'image' => $request->image ?? null,
            'duration' => $request->duration ?? 0,
            'price' => $request->price,
            'type' => $request->type,
            'discount_percent' => $request->discount_percent ?? 0,
            'rating_avg' => 0,
            'total_students' => 0,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json([
            'message' => 'Course created successfully',
            'course_id' => $course->courses_id
        ]);
    }


    // 2. Lấy danh sách khóa học theo User
    public function getCoursesByUser($userId)
    {
        $courses = Course::where('user_id', $userId)->get();

        return response()->json([
            'data' => $courses
        ]);
    }

    // 3. Cập nhật thông tin khóa học
    public function update(Request $request, $id)
    {
        $course = Course::findOrFail($id);

        $course->update($request->all());

        return response()->json([
            'message' => 'Course updated successfully'
        ]);
    }


    // 4. Xóa khóa học
    public function delete($id)
    {
        $course = Course::findOrFail($id);

        $course->delete();

        return response()->json([
            'message' => 'Course deleted successfully'
        ]);
    }


    // 5. Chi tiết khóa học với cấu trúc như yêu cầu
    public function detail($id)
    {
        $course = Course::with([
            'modules.lessons.quizzes',
            'reviews.user'
        ])->findOrFail($id);

        $response = [
            "COURSES_ID" => $course->courses_id,
            "TITLE" => $course->title,
            "DESCRIPTION" => $course->description,
            "RATING_AVG" => $course->rating_avg,
            "TOTAL_REVIEWS" => count($course->reviews),
            "TOTAL_STUDENTS" => $course->total_students,
            "MODULES" => [],
            "REVIEWS" => []
        ];

        foreach ($course->modules as $module) {
            $response["MODULES"][] = [
                "COURSES_MODULES_ID" => $module->courses_modules_id,
                "TITLE" => $module->title,
                "ORDER_INDEX" => $module->order_index,
                "LESSONS" => $module->lessons->map(function ($lesson) {
                    return [
                        "LESSON_ID" => $lesson->lesson_id,
                        "TITLE" => $lesson->title,
                        "DESCRIPTION" => "",
                        "ORDER_INDEX" => $lesson->order_index,
                        "VIDEO_URL" => $lesson->video_url,
                        "QUIZZES" => $lesson->quizzes->map(function ($q) {
                            return ["QUIZ_ID" => $q->quiz_id];
                        })
                    ];
                })
            ];
        }

        foreach ($course->reviews as $review) {
            $response["REVIEWS"][] = [
                "FULL_NAME" => $review->user->full_name,
                "CONTEXT" => $review->context,
                "RATING" => $review->rating
            ];
        }

        return response()->json([
            "data" => $response
        ]);
    }
}
