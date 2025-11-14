<?php

namespace App\Models;

use App\Database\MySQLConnection;

class Cart
{
    /**
     * Lấy giỏ hàng của user, nếu chưa có thì tạo mới
     */
    public static function getOrCreateCart($userId)
    {
        $conn = MySQLConnection::connect();

        // Kiểm tra xem user đã có giỏ hàng chưa
        $stmt = $conn->prepare("SELECT CART_ID FROM CART WHERE USER_ID = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if ($row) {
            return $row['CART_ID'];
        }

        // Nếu chưa có, tạo mới
        $stmtInsert = $conn->prepare("INSERT INTO CART (USER_ID) VALUES (?)");
        $stmtInsert->bind_param("i", $userId);
        $stmtInsert->execute();

        return $conn->insert_id;
    }

    /**
     * Thêm khóa học vào giỏ hàng
     */
    public static function addCourseToCart($userId, $courseId)
    {
        $conn = MySQLConnection::connect();

        $cartId = self::getOrCreateCart($userId);

        // Kiểm tra xem khoá học đã có trong giỏ chưa
        $stmtCheck = $conn->prepare("SELECT * FROM CART_ITEM WHERE CART_ID = ? AND COURSES_ID = ?");
        $stmtCheck->bind_param("ii", $cartId, $courseId);
        $stmtCheck->execute();

        if ($stmtCheck->get_result()->num_rows > 0) {
            return true; // Đã có rồi, không thêm nữa
        }

        // Thêm vào CART_ITEM
        $stmtInsert = $conn->prepare("
            INSERT INTO CART_ITEM (CART_ID, COURSES_ID)
            VALUES (?, ?)
        ");
        $stmtInsert->bind_param("ii", $cartId, $courseId);
        return $stmtInsert->execute();
    }

    /**
     * Lấy toàn bộ giỏ hàng của người dùng
     */
    public static function getCartItems($userId)
    {
        $conn = MySQLConnection::connect();

        $query = "
            SELECT c.CART_ID, ci.COURSES_ID, co.TITLE, co.PRICE, co.IMAGE
            FROM CART c
            JOIN CART_ITEM ci ON c.CART_ID = ci.CART_ID
            JOIN COURSES co ON ci.COURSES_ID = co.COURSES_ID
            WHERE c.USER_ID = ?
        ";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();

        $result = $stmt->get_result();
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }

        return $items;
    }

    /**
     * Xoá khóa học khỏi giỏ hàng
     */
    public static function removeFromCart($userId, $courseId)
    {
        $conn = MySQLConnection::connect();

        $cartId = self::getOrCreateCart($userId);

        $stmt = $conn->prepare("DELETE FROM CART_ITEM WHERE CART_ID = ? AND COURSES_ID = ?");
        $stmt->bind_param("ii", $cartId, $courseId);
        return $stmt->execute();
    }
}
