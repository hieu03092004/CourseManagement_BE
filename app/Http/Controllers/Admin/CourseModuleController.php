<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CourseModule;

class CourseModuleController extends Controller
{
    /**
     * Hiển thị danh sách tất cả module
     */
    public function index()
    {
        $modules = CourseModule::getAll();

        return response()->json([
            'success' => true,
            'data' => $modules
        ]);
    }

    /**
     * Lấy module theo khóa học
     */
    public function getByCourse($courseId)
    {
        $modules = CourseModule::getByCourseId($courseId);

        return response()->json([
            'success' => true,
            'data' => $modules
        ]);
    }

    /**
     * Tạo module mới
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'COURSES_ID' => 'required|integer',
            'ORDER_INDEX' => 'required|integer',
            'TITLE' => 'required|string|max:255',
        ]);

        $moduleId = CourseModule::create($validated);

        if (!$moduleId) {
            return response()->json([
                'success' => false,
                'message' => 'Tạo module thất bại'
            ], 500);
        }

        $module = CourseModule::find($moduleId);

        return response()->json([
            'success' => true,
            'message' => 'Tạo module thành công!',
            'module' => $module
        ]);
    }

    /**
     * Hiển thị chi tiết module (chỉ module)
     */
    public function showBasic($id)
    {
        $module = CourseModule::find($id);

        if (!$module) {
            return response()->json([
                'success' => false,
                'message' => 'Module không tồn tại'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $module
        ]);
    }

    /**
     * Hiển thị chi tiết module kèm lesson + quiz
     */
    public function showDetail($id)
    {
        $module = CourseModule::getModuleStructure($id);

        if (!$module) {
            return response()->json([
                'success' => false,
                'message' => 'Module không tồn tại'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $module
        ]);
    }

    /**
     * Cập nhật module
     */
    public function update(Request $request, $id)
    {
        $module = CourseModule::find($id);

        if (!$module) {
            return response()->json([
                'success' => false,
                'message' => 'Module không tồn tại'
            ], 404);
        }

        $validated = $request->validate([
            'COURSES_ID' => 'sometimes|integer',
            'ORDER_INDEX' => 'sometimes|integer',
            'TITLE' => 'sometimes|string|max:255',
        ]);

        // Merge dữ liệu hiện tại với dữ liệu gửi lên
        $data = array_merge($module, $validated);

        $success = CourseModule::update($id, $data);

        if (!$success) {
            return response()->json([
                'success' => false,
                'message' => 'Cập nhật module thất bại'
            ], 500);
        }

        $updatedModule = CourseModule::find($id);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật module thành công!',
            'module' => $updatedModule
        ]);
    }

    /**
     * Xóa module
     */
    public function destroy($id)
    {
        $module = CourseModule::find($id);

        if (!$module) {
            return response()->json([
                'success' => false,
                'message' => 'Module không tồn tại'
            ], 404);
        }

        $success = CourseModule::delete($id);

        if (!$success) {
            return response()->json([
                'success' => false,
                'message' => 'Xóa module thất bại'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Xóa module thành công!'
        ]);
    }
}
