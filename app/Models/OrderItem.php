<?php

namespace App\Models;

use App\Database\MySQLConnection;

class OrderItem
{
    /**
     * Thêm một khoá học vào đơn hàng
     */
    public static function addCourseToOrder($orderId, $courseId, $unitPrice)
    {
        $conn = MySQLConnection::connect();

        $activationCode = uniqid("ACT-", true);
        $stmt = $conn->prepare("
            INSERT INTO ORDER_ITEM 
            (COURSES_ID, ORDERS_ID, UNIT_PRICE_, ACTIVATED_, ACTIVATION_CODE_, ACTIVATED_AT_, EXPIRED_AT)
            VALUES (?, ?, ?, 0, ?, NOW(), DATE_ADD(NOW(), INTERVAL 1 YEAR))
        ");

        $stmt->bind_param("iiss", $courseId, $orderId, $unitPrice, $activationCode);

        return $stmt->execute();
    }

    /**
     * Kích hoạt khóa học khi thanh toán thành công
     */
    public static function activateCourse($orderId, $courseId)
    {
        $conn = MySQLConnection::connect();

        $stmt = $conn->prepare("
            UPDATE ORDER_ITEM 
            SET ACTIVATED_ = 1, ACTIVATED_AT_ = NOW() 
            WHERE ORDERS_ID = ? AND COURSES_ID = ?
        ");
        $stmt->bind_param("ii", $orderId, $courseId);
        return $stmt->execute();
    }

    /**
     * Lấy danh sách khóa học trong đơn hàng
     */
    public static function getItemsByOrder($orderId)
    {
        $conn = MySQLConnection::connect();

        $query = "
            SELECT oi.*, c.TITLE, c.IMAGE, c.PRICE 
            FROM ORDER_ITEM oi
            JOIN COURSES c ON c.COURSES_ID = oi.COURSES_ID
            WHERE oi.ORDERS_ID = ?
        ";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $orderId);
        $stmt->execute();

        $result = $stmt->get_result();
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }

        return $items;
    }
}
