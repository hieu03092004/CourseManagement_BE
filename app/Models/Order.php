<?php

namespace App\Models;

use App\Database\MySQLConnection;

class Order
{
    /**
     * Tạo đơn hàng (pending) khi người dùng đăng ký khóa học
     */
    public static function createOrder($userId, $courseId, $unitPrice, $expiredAt)
    {
        $conn = MySQLConnection::connect();

        try {
            $conn->begin_transaction();

            // 1️⃣ Tạo đơn hàng
            $stmtOrder = $conn->prepare("
                INSERT INTO ORDERS (USER_ID, TOTAL_PRICE, PAYMENT_STATUS, CREATED_AT)
                VALUES (?, ?, 'pending', NOW())
            ");
            $stmtOrder->bind_param("id", $userId, $unitPrice);
            $stmtOrder->execute();

            $orderId = $conn->insert_id;

            $stmtItem = $conn->prepare("
                INSERT INTO ORDER_ITEM (COURSES_ID, ORDERS_ID, UNIT_PRICE, EXPIRED_AT)
                VALUES (?, ?, ?, ?)
            ");
            $stmtItem->bind_param("iids", $courseId, $orderId, $unitPrice, $expiredAt);
            $stmtItem->execute();

            $conn->commit();
            return $orderId;

        } catch (\Exception $e) {
            $conn->rollback();
            error_log("Order creation failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cập nhật trạng thái thanh toán
     */
    public static function updatePaymentStatus($orderId, $status)
    {
        $conn = MySQLConnection::connect();
        $stmt = $conn->prepare("UPDATE ORDERS SET PAYMENT_STATUS = ? WHERE ORDERS_ID = ?");
        $stmt->bind_param("si", $status, $orderId);
        return $stmt->execute();
    }

    /**
     * Lấy danh sách đơn hàng của người dùng
     */
    public static function getUserOrders($userId)
    {
        $conn = MySQLConnection::connect();
        $query = "
            SELECT o.*, c.TITLE, c.IMAGE
            FROM ORDERS o
            JOIN ORDER_ITEM oi ON o.ORDERS_ID = oi.ORDERS_ID
            JOIN COURSES c ON oi.COURSES_ID = c.COURSES_ID
            WHERE o.USER_ID = ?
            ORDER BY o.CREATED_AT DESC
        ";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();

        $result = $stmt->get_result();
        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }

        return $orders;
    }
}
