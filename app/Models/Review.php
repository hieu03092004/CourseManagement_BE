<?php

namespace App\Models;

use App\Database\MySQLConnection;

class Review
{
    /**
     * Lấy tất cả các đánh giá của một khóa học
     *
     * @param int $courseId
     * @return array
     */
    public static function getReviewsByCourseId($courseId)
    {
        // Kết nối cơ sở dữ liệu
        $conn = MySQLConnection::connect();

        // Truy vấn lấy danh sách đánh giá của khóa học
        $query = "
            SELECT 
                r.REVIEW_ID,
                r.CONTEXT,
                r.RATING,
                u.FULL_NAME AS USER_NAME,
                r.CREATED_AT
            FROM REVIEW r
            JOIN USER u ON u.USER_ID = r.USER_ID
            WHERE r.COURSES_ID = ?
            ORDER BY r.CREATED_AT DESC
        ";

        // Chuẩn bị và thực thi truy vấn
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $courseId);
        $stmt->execute();

        // Lấy kết quả trả về và chuyển thành mảng
        $result = $stmt->get_result();
        $reviews = [];
        while ($row = $result->fetch_assoc()) {
            $reviews[] = $row;
        }

        // Trả về danh sách đánh giá
        return $reviews;
    }

    /**
     * Thêm mới đánh giá
     *
     * @param array $data
     * @return int|false returns inserted review ID or false if failed
     */
    public static function create(array $data)
    {
        // Kết nối cơ sở dữ liệu
        $conn = MySQLConnection::connect();

        // Chuẩn bị câu lệnh SQL để thêm mới đánh giá
        $stmt = $conn->prepare("
            INSERT INTO REVIEW (COURSES_ID, USER_ID, RATING, CONTEXT, CREATED_AT, UPDATED_AT)
            VALUES (?, ?, ?, ?, NOW(), NOW())
        ");

        // Kiểm tra lỗi khi chuẩn bị câu lệnh
        if (!$stmt) {
            return false;
        }

        // Liên kết tham số với câu lệnh SQL
        $stmt->bind_param("iiis", $data['COURSES_ID'], $data['USER_ID'], $data['RATING'], $data['CONTEXT']);

        // Thực thi câu lệnh và trả về ID của đánh giá vừa thêm
        if ($stmt->execute()) {
            return $conn->insert_id;
        }

        // Trả về false nếu thực thi thất bại
        return false;
    }

    /**
     * Cập nhật đánh giá của người dùng
     *
     * @param int $reviewId
     * @param array $data
     * @return bool
     */
    public static function update($reviewId, array $data)
    {
        // Kết nối cơ sở dữ liệu
        $conn = MySQLConnection::connect();

        // Chuẩn bị câu lệnh SQL để cập nhật đánh giá
        $stmt = $conn->prepare("
            UPDATE REVIEW
            SET RATING = ?, CONTEXT = ?, UPDATED_AT = NOW()
            WHERE REVIEW_ID = ?
        ");

        // Kiểm tra lỗi khi chuẩn bị câu lệnh
        if (!$stmt) {
            return false;
        }

        // Liên kết tham số với câu lệnh SQL
        $stmt->bind_param("dsi", $data['RATING'], $data['CONTEXT'], $reviewId);

        // Thực thi câu lệnh và trả về true nếu thành công
        return $stmt->execute();
    }

    /**
     * Xóa đánh giá của người dùng
     *
     * @param int $reviewId
     * @return bool
     */
    public static function delete($reviewId)
    {
        // Kết nối cơ sở dữ liệu
        $conn = MySQLConnection::connect();

        // Chuẩn bị câu lệnh SQL để xóa đánh giá
        $stmt = $conn->prepare("DELETE FROM REVIEW WHERE REVIEW_ID = ?");

        // Kiểm tra lỗi khi chuẩn bị câu lệnh
        if (!$stmt) {
            return false;
        }

        // Liên kết tham số với câu lệnh SQL
        $stmt->bind_param("i", $reviewId);

        // Thực thi câu lệnh và trả về true nếu thành công
        return $stmt->execute();
    }

    /**
     * Lấy tổng số đánh giá và điểm trung bình của khóa học
     *
     * @param int $courseId
     * @return array
     */
    public static function getCourseRatingInfo($courseId)
    {
        // Kết nối cơ sở dữ liệu
        $conn = MySQLConnection::connect();

        // Truy vấn lấy điểm trung bình và tổng số đánh giá của khóa học
        $query = "
            SELECT 
                AVG(r.RATING) AS AVERAGE_RATING,
                COUNT(r.REVIEW_ID) AS TOTAL_REVIEWS
            FROM REVIEW r
            WHERE r.COURSES_ID = ?
        ";

        // Chuẩn bị và thực thi câu lệnh SQL
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $courseId);
        $stmt->execute();

        // Lấy kết quả trả về và trả về dưới dạng mảng
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }
}
