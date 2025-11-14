<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Database\MySQLConnection;
use App\Models\Course;
use App\Models\Order;
use App\Models\Cart;

class EnrollmentController extends Controller
{
    /**
     * 🔹 MUA NGAY — tạo đơn hàng và order_item
     */
    public function buyNow(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|integer',
            'course_id' => 'required|integer',
        ]);

        $userId = $validated['user_id'];
        $courseId = $validated['course_id'];

        // 🔹 Lấy thông tin khóa học trực tiếp từ bảng COURSES
        $conn = MySQLConnection::connect();
        $stmt = $conn->prepare("SELECT PRICE, DURATION FROM COURSES WHERE COURSES_ID = ?");
        $stmt->bind_param("i", $courseId);
        $stmt->execute();
        $result = $stmt->get_result();
        $course = $result->fetch_assoc();

        if (!$course) {
            return response()->json([
                'success' => false,
                'message' => 'Khoá học không tồn tại.'
            ], 404);
        }

        $price = $course['PRICE'];
        $duration = $course['DURATION'];
        $expiredAt = date('Y-m-d H:i:s', strtotime("+{$duration} months"));

        $orderId = Order::createOrder($userId, $courseId, $price, $expiredAt);

        if ($orderId) {
            return response()->json([
                'success' => true,
                'message' => 'Tạo đơn hàng thành công. Vui lòng thanh toán để hoàn tất.',
                'order_id' => $orderId
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Không thể tạo đơn hàng.'
        ], 500);
    }


    /**
     * 🔹 THÊM VÀO GIỎ HÀNG
     */
    public function addToCart(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|integer',
            'course_id' => 'required|integer',
        ]);

        $userId = $validated['user_id'];
        $courseId = $validated['course_id'];

        $course = Course::getCourseInfo($courseId);
        if (!$course) {
            return response()->json([
                'success' => false,
                'message' => 'Khoá học không tồn tại.'
            ], 404);
        }

        $added = Cart::addCourseToCart($userId, $courseId);
        if ($added) {
            return response()->json([
                'success' => true,
                'message' => 'Khóa học đã được thêm vào giỏ hàng.'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Không thể thêm khóa học vào giỏ hàng.'
        ], 500);
    }

    /**
     * 🔹 THANH TOÁN TỪ GIỎ HÀNG
     * - Nhận danh sách course_id người dùng chọn trong giỏ
     */
    public function checkoutFromCart(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|integer',
            'course_ids' => 'required|array',
            'course_ids.*' => 'integer'
        ]);

        $userId = $validated['user_id'];
        $courseIds = $validated['course_ids'];

        $conn = MySQLConnection::connect();

        try {
            $conn->begin_transaction();

            $total = 0;
            $expiredAt = date('Y-m-d H:i:s', strtotime('+1 year'));

            // 1️⃣ Tạo order trước
            $stmtOrder = $conn->prepare("
                INSERT INTO ORDERS (USER_ID, TOTAL_PRICE, PAYMENT_STATUS, CREATED_AT)
                VALUES (?, 0, 'pending', NOW())
            ");
            $stmtOrder->bind_param("i", $userId);
            $stmtOrder->execute();
            $orderId = $conn->insert_id;

            // 2️⃣ Lặp qua từng khóa học
            $stmtCourse = $conn->prepare("SELECT PRICE, DURATION FROM COURSES WHERE COURSES_ID = ?");
            $stmtItem = $conn->prepare("
                INSERT INTO ORDER_ITEM (COURSES_ID, ORDERS_ID, UNIT_PRICE, EXPIRED_AT)
                VALUES (?, ?, ?, ?)
            ");

            foreach ($courseIds as $courseId) {
                $stmtCourse->bind_param("i", $courseId);
                $stmtCourse->execute();
                $result = $stmtCourse->get_result();
                $course = $result->fetch_assoc();

                if ($course) {
                    $price = $course['PRICE'];
                    $duration = $course['DURATION'];
                    $total += $price;

                    // ⚙️ Tính hạn theo duration
                    $expiredAt = date('Y-m-d H:i:s', strtotime("+{$duration} months"));

                    $stmtItem->bind_param("iids", $courseId, $orderId, $price, $expiredAt);
                    $stmtItem->execute();

                    Cart::removeFromCart($userId, $courseId);
                }
            }

            // 4️⃣ Cập nhật tổng tiền đơn hàng
            $stmtUpdate = $conn->prepare("UPDATE ORDERS SET TOTAL_PRICE = ? WHERE ORDERS_ID = ?");
            $stmtUpdate->bind_param("di", $total, $orderId);
            $stmtUpdate->execute();

            $conn->commit();

            return response()->json([
                'success' => true,
                'message' => 'Tạo đơn hàng thành công từ giỏ hàng.',
                'order_id' => $orderId
            ]);
        } catch (\Exception $e) {
            $conn->rollback();
            error_log("Checkout failed: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Thanh toán thất bại.'], 500);
        }
    }

    /**
     * 🔹 XOÁ 1 HOẶC NHIỀU KHÓA HỌC KHỎI GIỎ HÀNG
     */
    public function removeFromCart(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|integer',
            'course_ids' => 'required|array',
            'course_ids.*' => 'integer'
        ]);

        $userId = $validated['user_id'];
        $courseIds = $validated['course_ids'];

        $conn = \App\Database\MySQLConnection::connect();

        try {
            $conn->begin_transaction();

            $cartId = \App\Models\Cart::getOrCreateCart($userId);

            // Tạo chuỗi ?,?,? tương ứng số lượng course_ids
            $in = str_repeat('?,', count($courseIds) - 1) . '?';
            $types = str_repeat('i', count($courseIds) + 1);
            $params = array_merge([$cartId], $courseIds);

            $query = "DELETE FROM CART_ITEM WHERE CART_ID = ? AND COURSES_ID IN ($in)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();

            $conn->commit();

            return response()->json([
                'success' => true,
                'message' => 'Đã xoá khoá học khỏi giỏ hàng.'
            ]);
        } catch (\Exception $e) {
            $conn->rollback();
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi xoá khoá học: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Lấy giỏ hàng người dùng
     */
    public function getCart(Request $request)
    {
        $userId = $request->input('user_id');
        $items = Cart::getCartItems($userId);
        return response()->json(['success' => true, 'data' => $items]);
    }

    /**
     * Lấy danh sách đơn hàng người dùng
     */
    public function getOrders(Request $request)
    {
        $userId = $request->input('user_id');
        $orders = Order::getUserOrders($userId);
        return response()->json(['success' => true, 'data' => $orders]);
    }
}
