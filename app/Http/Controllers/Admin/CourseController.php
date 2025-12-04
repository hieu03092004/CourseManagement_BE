<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use App\Models\Course;
use App\Models\OrderItem;
use App\Models\Order;
use App\Helpers\Course\CourseHelper;

class CourseController extends BaseAPIController
{
    public function getAll()
    {
        try {
            $courses = Course::with('instructor')->get();

            $result = $courses->map(function ($course) {
                $lectures = DB::table('course_modules')
                    ->join('lesson', 'course_modules.courses_modules_id', '=', 'lesson.courses_modules_id')
                    ->where('course_modules.courses_id', $course->courses_id)
                    ->count();

                $students = OrderItem::where('courses_id', $course->courses_id)
                    ->whereHas('order', function ($query) {
                        $query->where('payment_status', 'success');
                    })
                    ->count();

                $reviews = $course->reviews()->count();

                $originalPrice = $course->price;
                $price = $course->discount_percent > 0
                    ? $originalPrice * (1 - $course->discount_percent / 100)
                    : $originalPrice;

                $ratingAvg = 0;
                if ($reviews > 0) {
                    $totalRating = $course->reviews()->sum('rating');
                    $ratingAvg = $totalRating / $reviews;
                }

                return [
                    'id' => (string)$course->courses_id,
                    'name' => $course->title,
                    'description' => $course->description ?? '',
                    'instructor' => $course->instructor->full_name ?? '',
                    'avavatarInstructor' => $course->instructor->avt ?? '',
                    'lectures' => $lectures,
                    'students' => $students,
                    'rating' => (float)$ratingAvg,
                    'reviews' => $reviews,
                    'price' => (float)$price,
                    'originalPrice' => (float)$originalPrice,
                    'image' => $course->image ?? '',
                    'duration' => $course->duration ?? null,
                ];
            });

            return $this->ok($result);
        } catch (\Exception $e) {
            return $this->fail(
                'An error occurred while fetching courses',
                500,
                'INTERNAL_ERROR',
                ['message' => $e->getMessage()]
            );
        }
    }
    public function edit(Request $request, $id)
    {
        try {
            $course = Course::findOrFail($id);
            
            $updateData = [];
            
            // Xử lý upload image nếu có
            if ($request->hasFile('image')) {
                $request->validate([
                    'image' => 'image|mimes:jpeg,png,jpg,gif|max:2048'
                ]);

                $imageUrl = CourseHelper::uploadImage($request->file('image'), $course->image);
                if ($imageUrl) {
                    $updateData['image'] = $imageUrl;
                }
            }

            // Update các field khác nếu có trong request
            if ($request->filled('title')) {
                $updateData['title'] = $request->title;
            }
            
            if ($request->filled('description')) {
                $updateData['description'] = $request->description;
            }
            
            if ($request->filled('target')) {
                $updateData['target'] = $request->target;
            }
            
            if ($request->filled('result')) {
                $updateData['result'] = $request->result;
            }
            
            if ($request->filled('duration')) {
                $updateData['duration'] = (int) $request->duration;
            }
            
            if ($request->filled('price')) {
                $updateData['price'] = (float) $request->price;
            }
            
            if ($request->filled('type')) {
                $updateData['type'] = $request->type;
            }

            if ($request->filled('discountPercent')) {
                $updateData['discount_percent'] = (float) $request->discountPercent;
            }
    
            // Cập nhật updated_at
            $updateData['updated_at'] = now();
            
            if (!empty($updateData)) {
                $course->update($updateData);
                $course->refresh();
            }
    
            return $this->ok([
                'course_id'       => $course->courses_id,
                'title'           => $course->title,
                'image'           => $course->image,
                'discount_percent'=> $course->discount_percent,
                'message'         => 'Course updated successfully',
                'updated_fields'  => array_keys($updateData)
            ]);
    
        } catch (ModelNotFoundException $e) {
            return $this->fail('Course not found', 404, 'NOT_FOUND');
        } catch (ValidationException $e) {
            return $this->fail(
                'Validation failed',
                422,
                'VALIDATION_ERROR',
                $e->errors()
            );
        } catch (\Exception $e) {
            return $this->fail(
                'An error occurred while updating course',
                500,
                'INTERNAL_ERROR',
                ['message' => $e->getMessage()]
            );
        }
    }
    public function create(Request $request)
    {
        try {
            // Validation
            $validated = $request->validate([
                'user_id' => 'required|integer',
                'title' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
                'type' => 'required|string|max:10',
                'description' => 'nullable|string',
                'target' => 'nullable|string',
                'result' => 'nullable|string',
                'duration' => 'nullable|integer|min:0',
                'discount_percent' => 'nullable|numeric|min:0|max:100',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            // Xử lý upload image
            $imageUrl = null;
            if ($request->hasFile('image')) {
                $imageUrl = CourseHelper::uploadImage($request->file('image'));
            }

            $course = Course::create([
                'user_id' => $request->user_id,
                'title' => $request->title,
                'description' => $request->description,
                'target' => $request->target,
                'result' => $request->result,
                'image' => $imageUrl,
                'duration' => $request->duration,
                'price' => $request->price,
                'type' => $request->type,
                'discount_percent' => $request->discount_percent ?? 0,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return $this->created([
                'course_id' => $course->courses_id,
                'title' => $course->title,
                'image' => $course->image,
            ]);

        } catch (ValidationException $e) {
            return $this->fail(
                'Validation failed',
                422,
                'VALIDATION_ERROR',
                $e->errors()
            );
        } catch (\Exception $e) {
            return $this->fail(
                'An error occurred while creating course',
                500,
                'INTERNAL_ERROR',
                ['message' => $e->getMessage()]
            );
        }
    }

    public function getCoursesByUser($userId)
    {
        $courses = Course::where('user_id', $userId)->get();

        return $this->ok($courses);
    }

    public function delete($id)
    {
        try {
            $course = Course::findOrFail($id);
            $course->delete();

            return $this->noContent();
        } catch (\Exception $e) {
            return $this->fail(
                'An error occurred while deleting course',
                500,
                'INTERNAL_ERROR',
                ['message' => $e->getMessage()]
            );
        }
    }


    // 5. Chi tiết khóa học với cấu trúc như yêu cầu
    public function detail($id)
    {
        $course = Course::with([
            'modules.lessons.quizzes',
            'reviews.user'
        ])->findOrFail($id);

        $reviews = $course->reviews()->count();
        $ratingAvg = 0;
        if ($reviews > 0) {
            $totalRating = $course->reviews()->sum('rating');
            $ratingAvg = $totalRating / $reviews;
        }

        $students = OrderItem::where('courses_id', $course->courses_id)
            ->whereHas('order', function ($query) {
                $query->where('payment_status', 'success');
            })
            ->count();

        $response = [
            "COURSES_ID" => $course->courses_id,
            "TITLE" => $course->title,
            "DESCRIPTION" => $course->description,
            "RATING_AVG" => (float)$ratingAvg,
            "TOTAL_REVIEWS" => $reviews,
            "TOTAL_STUDENTS" => $students,
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

        return $this->ok($response);
    }

    public function details($id)
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
