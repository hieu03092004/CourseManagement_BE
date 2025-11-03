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
}
