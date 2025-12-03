<?php

namespace App\Http\Controllers\Admin;

use App\Models\Discussion;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Models\Lesson;
use App\Models\CourseModule;
use App\Models\ParentDiscussion;
use App\Models\Quizz;
use Illuminate\Support\Facades\Storage;

class LessonController extends BaseAPIController
{
    public function store(Request $request)
    {
        try {
            // Validation cơ bản
            $validated = $request->validate([
                'course_id' => 'required|integer',
                'data' => 'required|string', // Nhận string JSON, sẽ decode sau
            ]);
        
            $courseId = $request->input('course_id');
            $dataString = $request->input('data');
            
            // Decode JSON
            $data = json_decode($dataString, true);
        
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
                return $this->fail(
                    'Invalid JSON format in data field',
                    422,
                    'INVALID_DATA',
                    ['data' => ['The data field must be a valid JSON string']]
                );
            }
        
            $result = [];
        
            foreach ($data as $topicData) {
                // Validate topic data structure
                if (!isset($topicData['topic']) || !isset($topicData['lessons']) || !is_array($topicData['lessons'])) {
                    return $this->fail(
                        'Invalid topic data structure',
                        422,
                        'INVALID_DATA',
                        ['topic' => ['Each topic must have topic name and lessons array']]
                    );
                }
                
                // Tạo CourseModule cho từng topic
                $courseModule = CourseModule::create([
                    'courses_id' => $courseId,
                    'order_index' => $topicData['orderIndex'] ?? 0,
                    'title' => $topicData['topic'],
                ]);
        
                $lessonResults = [];
        
                foreach ($topicData['lessons'] as $lessonData) {
                    // Validate lesson data
                    if (!isset($lessonData['title'])) {
                        continue; // Skip invalid lesson
                    }
                    
                    // Xử lý upload video file
                    $videoUrl = null;
                    $fileFieldKey = $lessonData['fileField'] ?? null;
        
                    if ($fileFieldKey && $request->hasFile($fileFieldKey)) {
                        $videoFile = $request->file($fileFieldKey);
                        
                        // Validate file type và size
                        $allowedMimes = ['video/mp4', 'video/avi', 'video/quicktime', 'video/x-msvideo', 'video/x-matroska'];
                        $maxSize = 512000; // 500MB in KB
                        
                        if (!in_array($videoFile->getMimeType(), $allowedMimes)) {
                            return $this->fail(
                                'Invalid video file type',
                                422,
                                'INVALID_FILE',
                                [$fileFieldKey => ['Video file must be mp4, avi, mov, or mkv']]
                            );
                        }
                        
                        if ($videoFile->getSize() > $maxSize * 1024) {
                            return $this->fail(
                                'Video file too large',
                                422,
                                'FILE_TOO_LARGE',
                                [$fileFieldKey => ['Video file must not exceed 500MB']]
                            );
                        }
                        
                        // Tạo folder nếu chưa có
                        $videosPath = public_path('videos');
                        if (!file_exists($videosPath)) {
                            mkdir($videosPath, 0755, true);
                        }
                        
                        // Generate unique filename
                        $videoFileName = time() . rand(1000, 9999) . '.' . $videoFile->extension();
                        
                        // Lưu file vào public/videos/
                        $videoFile->move($videosPath, $videoFileName);
                        
                        // Lưu full URL vào DB
                        $videoUrl = url('videos/' . $videoFileName);
                    }
        
                    // Tạo Lesson
                    $lesson = Lesson::create([
                        'courses_modules_id' => $courseModule->courses_modules_id,
                        'title' => $lessonData['title'],
                        'video_url' => $videoUrl,
                        'duration' => $lessonData['duration'] ?? 0,
                        'order_index' => $lessonData['orderIndex'] ?? 0,
                    ]);
        
                    $lessonResults[] = [
                        'lesson_id' => $lesson->lesson_id,
                        'title' => $lesson->title,
                        'video_url' => $lesson->video_url, // Đã là full URL
                        'duration' => $lesson->duration,
                        'order_index' => $lesson->order_index,
                    ];
                }
        
                $result[] = [
                    'course_module_id' => $courseModule->courses_modules_id,
                    'topic' => $courseModule->title,
                    'order_index' => $courseModule->order_index,
                    'lessons' => $lessonResults,
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
                'An error occurred while creating lessons',
                500,
                'INTERNAL_ERROR',
                ['message' => $e->getMessage()]
            );
        }
    }

    public function show($id)
    {
        $lesson = Lesson::findOrFail($id);
        $lesson->video_url_full = $lesson->video_url ? asset('storage/' . $lesson->video_url) : null;

        return $this->ok($lesson);
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

        $lesson->video_url_full = $lesson->video_url ? asset('storage/' . $lesson->video_url) : null;

        return $this->ok($lesson);
    }

    public function delete($id)
    {
        $lesson = Lesson::findOrFail($id);

        if ($lesson->video_url && Storage::disk('public')->exists($lesson->video_url)) {
            Storage::disk('public')->delete($lesson->video_url);
        }

        foreach ($lesson->quizzes as $quizz) {
            $this->deleteQuizRecursive($quizz);
        }

        $lesson->delete();

        return $this->noContent();
    }

    private function deleteQuizRecursive(Quizz $quizz)
    {
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

    public function showByModule($courses_module_id)
    {
        $lessons = Lesson::where('courses_module_id', $courses_module_id)
            ->orderBy('order_index')
            ->get()
            ->map(function ($x) {
                $x->video_url_full = $x->video_url ? asset('storage/' . $x->video_url) : null;
                return $x;
            });

        return $this->ok($lessons);
    }


    // public function fullLesson() {}
}
