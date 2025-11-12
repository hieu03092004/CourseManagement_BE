<?php

namespace App\Models;

use App\Database\MySQLConnection;

class Order
{
    /**
     * Tạo đơn hàng mới khi người dùng đăng ký khoá học
     */
    public static function createOrder($userId, $totalPrice)
    {
        $conn = MySQLConnection::connect();

        $stmt = $conn->prepare("
            INSERT INTO ORDERS (USER_ID, TOTAL_PRICE, PAYMENT_STATUS, CREATED_AT)
            VALUES (?, ?, 'pending', NOW())
        ");
        $stmt->bind_param("id", $userId, $totalPrice);

        if ($stmt->execute()) {
            return $conn->insert_id; // trả về ID của order mới tạo
        }

        return false;
    }

    /**
     * Cập nhật trạng thái thanh toán
     */
    public static function updatePaymentStatus($orderId, $status)
    {
        $conn = MySQLConnection::connect();

        $stmt = $conn->prepare("
            UPDATE ORDERS 
            SET PAYMENT_STATUS = ?, 
                CANCEL_REASON = NULL 
            WHERE ORDERS_ID = ?
        ");
        $stmt->bind_param("si", $status, $orderId);
        return $stmt->execute();
    }

    /**
     * Hủy đơn hàng
     */
    public static function cancelOrder($orderId, $reason)
    {
        $conn = MySQLConnection::connect();

        $stmt = $conn->prepare("
            UPDATE ORDERS 
            SET PAYMENT_STATUS = 'cancelled',
                CANCEL_REASON = ?
            WHERE ORDERS_ID = ?
        ");
        $stmt->bind_param("si", $reason, $orderId);
        return $stmt->execute();
    }

    /**
     * Lấy danh sách đơn hàng của người dùng
     */
    public static function getOrdersByUser($userId)
    {
        $conn = MySQLConnection::connect();

        $query = "
            SELECT * 
            FROM ORDERS 
            WHERE USER_ID = ?
            ORDER BY CREATED_AT DESC
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
