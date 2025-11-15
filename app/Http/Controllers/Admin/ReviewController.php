<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Lấy tất cả review của 1 khóa học
     */
    public function getReviews($courseId)
    {
        $reviews = Review::with('user')
            ->where('courses_id', $courseId)
            ->orderBy('created_at', 'DESC')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $reviews
        ]);
    }

    /**
     * Tạo mới review
     */
    public function store(Request $request)
    {
        $request->validate([
            'courses_id' => 'required|integer',
            'user_id'    => 'required|integer',
            'context'    => 'required|string',
            'rating'     => 'required|numeric|min:1|max:5',
        ]);

        $review = Review::create([
            'courses_id' => $request->courses_id,
            'user_id'    => $request->user_id,
            'context'    => $request->context,
            'rating'     => $request->rating,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Tạo đánh giá thành công',
            'data' => $review
        ]);
    }

    /**
     * Chỉnh sửa review
     */
    public function update(Request $request, $reviewId)
    {
        $review = Review::find($reviewId);

        if (!$review) {
            return response()->json([
                'status' => false,
                'message' => 'Không tìm thấy đánh giá'
            ], 404);
        }

        $request->validate([
            'context' => 'nullable|string',
            'rating'  => 'nullable|numeric|min:1|max:5',
        ]);

        $review->update([
            'context'    => $request->context ?? $review->context,
            'rating'     => $request->rating ?? $review->rating,
            'updated_at' => now(),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Cập nhật đánh giá thành công',
            'data' => $review
        ]);
    }

    /**
     * Xóa review
     */
    public function destroy($reviewId)
    {
        $review = Review::find($reviewId);

        if (!$review) {
            return response()->json([
                'status' => false,
                'message' => 'Không tìm thấy đánh giá'
            ], 404);
        }

        $review->delete();

        return response()->json([
            'status' => true,
            'message' => 'Xóa đánh giá thành công'
        ]);
    }
}
