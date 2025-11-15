<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CourseModule;

class CourseModuleController extends Controller
{
    // 1. Tạo module cho khóa học
    public function create(Request $request, $courseId)
    {
        $request->validate([
            'title' => 'required',
            'order_index' => 'nullable|integer',
        ]);

        $module = CourseModule::create([
            'courses_id' => $courseId,
            'title' => $request->title,
            'order_index' => $request->order_index ?? 0,
        ]);

        return response()->json([
            'message' => 'Module created successfully',
            'module_id' => $module->courses_modules_id,
        ]);
    }

    // 2. Cập nhật module
    public function update(Request $request, $id)
    {
        $module = CourseModule::findOrFail($id);

        $module->update([
            'title' => $request->title ?? $module->title,
            'order_index' => $request->order_index ?? $module->order_index,
        ]);

        return response()->json([
            'message' => 'Module updated successfully'
        ]);
    }

    // 3. Xóa module
    public function delete($id)
    {
        $module = CourseModule::findOrFail($id);
        $module->delete();

        return response()->json([
            'message' => 'Module deleted successfully'
        ]);
    }

    // 4. Hiển thị danh sách module cho khóa học
    public function getModulesByCourse($courseId)
    {
        $modules = CourseModule::with(['lessons.quizzes'])
            ->where('courses_id', $courseId)
            ->get();

        $response = $modules->map(function ($module) {
            return [
                'COURSES_MODULES_ID' => $module->courses_modules_id,
                'TITLE' => $module->title,
                'ORDER_INDEX' => $module->order_index,
                'LESSONS' => $module->lessons->map(function ($lesson) {
                    return [
                        'LESSON_ID' => $lesson->lesson_id,
                        'TITLE' => $lesson->title,
                        'ORDER_INDEX' => $lesson->order_index,
                        'VIDEO_URL' => $lesson->video_url,
                        'QUIZZES' => $lesson->quizzes->map(function ($quiz) {
                            return ['QUIZ_ID' => $quiz->quiz_id];
                        }),
                    ];
                }),
            ];
        });

        return response()->json([
            'data' => $response
        ]);
    }
}

