<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;

class CourseController extends Controller
{
    /**
     * Hiển thị danh sách khoá học
     */
    public function index()
    {
        $courses = Course::getAll();
        return response()->json([
            'success' => true,
            'data' => $courses
        ]);
    }

    /**
     * Tạo khoá học mới
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'USER_ID' => 'required|integer',
            'TITLE' => 'required|string|max:255',
            'DESCRIPTION' => 'required|string',
            'TARGET' => 'required|string',
            'RESULT' => 'required|string',
            'IMAGE' => 'nullable|string',
            'DURATION' => 'required|integer',
            'PRICE' => 'required|numeric',
            'TYPE' => 'required|string',
            'DISCOUNT_PERCENT' => 'nullable|numeric'
        ]);

        $course = Course::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Tạo khoá học thành công!',
            'course' => $course
        ]);
    }

    /**
     * Cập nhật khoá học
     */
    // public function update(Request $request, $id)
    // {
    //     $course = Course::find($id);
    //     if (!$course) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Khoá học không tồn tại'
    //         ], 404);
    //     }

    //     $validated = $request->validate([
    //         'TITLE' => 'sometimes|string|max:255',
    //         'DESCRIPTION' => 'sometimes|string',
    //         'TARGET' => 'sometimes|string',
    //         'RESULT' => 'sometimes|string',
    //         'IMAGE' => 'nullable|string',
    //         'DURATION' => 'sometimes|integer',
    //         'PRICE' => 'sometimes|numeric',
    //         'TYPE' => 'sometimes|string',
    //         'DISCOUNT_PERCENT' => 'nullable|numeric'
    //     ]);

    //     $course->update($validated);

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Cập nhật khoá học thành công!',
    //         'course' => $course
    //     ]);
    // }

    // /**
    //  * Xoá khoá học
    //  */
    // public function destroy($id)
    // {
    //     $course = Course::find($id);
    //     if (!$course) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Khoá học không tồn tại'
    //         ], 404);
    //     }

    //     $course->delete();

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Xoá khoá học thành công!'
    //     ]);
    // }

    /**
     * Hiển thị trang chi tiết khóa học
     */
    public function show($courseId)
    {
        $courseInfo = Course::getCourseInfo($courseId);

        if (!$courseInfo) {
            return response()->json([
                'success' => false,
                'message' => 'Khóa học không tồn tại'
            ], 404);
        }

        $structure = Course::getCourseStructure($courseId);
        $reviews = Course::getCourseReviews($courseId);

        return response()->json([
            'success' => true,
            'data' => [
                'COURSES_ID' => $courseInfo['COURSES_ID'],
                'TITLE' => $courseInfo['TITLE'],
                'DESCRIPTION' => $courseInfo['DESCRIPTION'],
                'RATING_AVG' => (float) $courseInfo['RATING_AVG'],
                'TOTAL_REVIEWS' => (int) $courseInfo['TOTAL_REVIEWS'],
                'TOTAL_STUDENTS' => (int) $courseInfo['TOTAL_STUDENTS'],
                'MODULES' => $structure,
                'REVIEWS' => $reviews
            ]
        ]);
    }
}
