<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Review;

class ReviewController extends Controller
{
    /**
     * Hiển thị tất cả các đánh giá của một khóa học
     */
    public function index($courseId)
    {
        // Lấy tất cả các đánh giá của khóa học từ model Review
        $reviews = Review::getReviewsByCourseId($courseId);

        if (empty($reviews)) {
            return response()->json([
                'success' => false,
                'message' => 'Không có đánh giá nào cho khóa học này.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $reviews
        ]);
    }

    /**
     * Tạo đánh giá mới cho một khóa học
     */
    public function store(Request $request)
    {
        // Validate dữ liệu đầu vào
        $validated = $request->validate([
            'COURSES_ID' => 'required|integer',
            'USER_ID' => 'required|integer',
            'RATING' => 'required|integer|between:1,5', // Rating từ 1 đến 5
            'CONTEXT' => 'required|string',
        ]);

        // Thêm mới đánh giá
        $reviewId = Review::create($validated);

        if ($reviewId) {
            return response()->json([
                'success' => true,
                'message' => 'Đánh giá được tạo thành công!',
                'review_id' => $reviewId
            ], 201);
        }

        return response()->json([
            'success' => false,
            'message' => 'Có lỗi xảy ra khi tạo đánh giá. Vui lòng thử lại.'
        ], 500);
    }

    /**
     * Cập nhật đánh giá của người dùng
     */
    public function update(Request $request, $reviewId)
    {
        // Validate dữ liệu đầu vào
        $validated = $request->validate([
            'RATING' => 'required|integer|between:1,5', // Rating từ 1 đến 5
            'CONTEXT' => 'required|string',
        ]);

        // Cập nhật đánh giá
        $updated = Review::update($reviewId, $validated);

        if ($updated) {
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật đánh giá thành công!'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Đánh giá không tồn tại hoặc đã xảy ra lỗi khi cập nhật.'
        ], 404);
    }

    /**
     * Xóa đánh giá của người dùng
     */
    public function destroy($reviewId)
    {
        // Xoá đánh giá
        $deleted = Review::delete($reviewId);

        if ($deleted) {
            return response()->json([
                'success' => true,
                'message' => 'Đánh giá đã được xóa thành công!'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Đánh giá không tồn tại hoặc đã xảy ra lỗi khi xoá.'
        ], 404);
    }

    /**
     * Lấy tổng số đánh giá và điểm trung bình của khóa học
     */
    public function ratingInfo($courseId)
    {
        // Lấy thông tin đánh giá tổng quan của khóa học
        $ratingInfo = Review::getCourseRatingInfo($courseId);

        if (!$ratingInfo) {
            return response()->json([
                'success' => false,
                'message' => 'Không có đánh giá cho khóa học này.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $ratingInfo
        ]);
    }
}
