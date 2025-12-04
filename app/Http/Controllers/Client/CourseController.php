<?php

namespace App\Http\Controllers\Client;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use App\Models\Course;
use App\Models\OrderItem;
use App\Models\Order;

class CourseController extends BaseAPIController
{

    public function detail($id)
    {
        try {
            $course = Course::with(['modules.lessons'])->findOrFail($id);

            $lectures = DB::table('course_modules')
                ->join('lesson', 'course_modules.courses_modules_id', '=', 'lesson.courses_modules_id')
                ->where('course_modules.courses_id', $course->courses_id)
                ->count();

            $studentCount = OrderItem::where('courses_id', $course->courses_id)
                ->whereHas('order', function ($query) {
                    $query->where('payment_status', 'success');
                })
                ->count();

            $reviews = $course->reviews()->count();

            $rating = 0;
            if ($reviews > 0) {
                $totalRating = $course->reviews()->sum('rating');
                $rating = $totalRating / $reviews;
            }

            // Lấy feedback từ reviews với thông tin user
            $feedback = $course->reviews()->with('user')->get()->map(function ($review) {
                return [
                    'fullName' => $review->user->full_name ?? '',
                    'comment' => $review->context ?? '',
                    'rating' => (float)$review->rating,
                ];
            })->toArray();

            $topics = $course->modules->map(function ($module) {
                return [
                    'title' => $module->title,
                    'orderIndex' => $module->order_index,
                    'lessons' => $module->lessons->map(function ($lesson) {
                        return [
                            'id' => $lesson->lesson_id,
                            'title' => $lesson->title,
                            'videoUrl' => $lesson->video_url,
                            'duration' => $lesson->duration,
                            'orderIndex' => $lesson->order_index
                        ];
                    })
                ];
            });

        $result = [
            'title' => $course->title,
            'image' => $course->image ?? '',
            'description' => $course->description ?? '',
            'target' => $course->target ?? '',
            'result' => $course->result ?? '',
            'duration' => $course->duration ?? 0,
            'type' => $course->type,
            'price' => (float)$course->price,
            'discountPercent' => (float)$course->discount_percent,
            'rating' => (float)$rating,
            'studentCount' => $studentCount,
            'reviews' => $reviews,
            'feedback' => $feedback,
            'topics' => $topics
        ];

            return $this->ok($result);
        } catch (ModelNotFoundException $e) {
            return $this->fail(
                'Course not found',
                404,
                'NOT_FOUND'
            );
        } catch (\Exception $e) {
            return $this->fail(
                'An error occurred while fetching course details',
                500,
                'INTERNAL_ERROR',
                ['message' => $e->getMessage()]
            );
        }
    }
}
