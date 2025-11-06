<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Lesson;
use Illuminate\Support\Facades\Storage;

class LessonController extends Controller
{
    public function store(Request $request)
    {

        $videoPath = null;

        if ($request->hasFile('video_file')) {
            $file = $request->file('video_file');

            $videoPath = Storage::disk('public')->put('videos', $file);
        }

        $lesson = Lesson::create([
            'courses_module_id' => $request->courses_module_id,
            'title' => $request->title,
            'video_url' => $videoPath,
            'duration' => $request->duration,
            'order_index' => $request->order_index
        ]);

        return response()->json([
            'message' => 'Tạo lesson thành công',
            'data' => $lesson,
            'video_url_full' => asset('storage/' . $videoPath)
        ]);
    }

    public function show($id)
    {
        $lesson = Lesson::findOrFail($id);

        return response()->json([
            'message' => 'Chi tiết lesson',
            'data' => $lesson,
            'video_url_full' => $lesson->video_url ? asset('storage/' . $lesson->video_url) : null
        ]);
    }

    public function update(Request $request, $id)
    {
        $lesson = Lesson::findOrFail($id);

        if ($request->hasFile('video_file')) {

            if ($lesson->video_url && Storage::disk('public')->exists($lesson->video_url)) {
                Storage::disk('public')->delete($lesson->video_url);
            }

            $lesson->video_url = Storage::disk('public')->put('videos', $request->file('video_file'));
        }

        $lesson->update([
            'courses_module_id' => $request->courses_module_id ?? $lesson->courses_module_id,
            'title' => $request->title ?? $lesson->title,
            'duration' => $request->duration ?? $lesson->duration,
            'order_index' => $request->order_index ?? $lesson->order_index
        ]);

        return response()->json([
            'message' => 'Cập nhật lesson thành công',
            'data' => $lesson,
            'video_url_full' => $lesson->video_url ? asset('storage/' . $lesson->video_url) : null
        ]);
    }

    public function delete($id)
    {
        $lesson = Lesson::findOrFail($id);

        if ($lesson->video_url && Storage::disk('public')->exists($lesson->video_url)) {
            Storage::disk('public')->delete($lesson->video_url);
        }

        $lesson->delete();

        return response()->json([
            'message' => 'Xóa lesson thành công'
        ]);
    }

    public function fullLesson() {}
}
