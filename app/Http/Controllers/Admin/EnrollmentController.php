<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Course;

class EnrollmentController extends Controller
{
    /**
     * Đăng ký một khóa học (tạo đơn hàng)
     */
    public function enroll(Request $request)
    {
        $validated = $request->validate([
            'USER_ID' => 'required|integer',
            'COURSES_ID' => 'required|integer'
        ]);

        // Lấy thông tin khóa học
        $course = Course::getCourseInfo($validated['COURSES_ID']);
        if (!$course) {
            return response()->json([
                'success' => false,
                'message' => 'Khóa học không tồn tại'
            ], 404);
        }

        $price = $course['RATING_AVG'] ?? 0; // backup nếu getCourseInfo không chứa PRICE
        // Bạn có thể thay dòng trên bằng truy vấn trực tiếp trong Course model: SELECT PRICE FROM COURSES WHERE COURSES_ID = ?

        // Để an toàn hơn:
        $conn = \App\Database\MySQLConnection::connect();
        $stmt = $conn->prepare("SELECT PRICE FROM COURSES WHERE COURSES_ID = ?");
        $stmt->bind_param("i", $validated['COURSES_ID']);
        $stmt->execute();
        $priceRow = $stmt->get_result()->fetch_assoc();
        $price = $priceRow['PRICE'] ?? 0;

        // 1️⃣ Tạo đơn hàng mới
        $orderId = Order::createOrder($validated['USER_ID'], $price);
        if (!$orderId) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể tạo đơn hàng. Vui lòng thử lại.'
            ], 500);
        }

        // 2️⃣ Thêm khóa học vào đơn hàng
        $addItem = OrderItem::addCourseToOrder($orderId, $validated['COURSES_ID'], $price);

        if (!$addItem) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể thêm khóa học vào đơn hàng.'
            ], 500);
        }

        // ✅ Trả về phản hồi
        return response()->json([
            'success' => true,
            'message' => 'Đăng ký khóa học thành công, vui lòng tiến hành thanh toán.',
            'data' => [
                'order_id' => $orderId,
                'course_id' => $validated['COURSES_ID'],
                'price' => $price,
                'payment_status' => 'pending'
            ]
        ]);
    }

    /**
     * Xác nhận thanh toán thành công
     */
    public function confirmPayment(Request $request)
    {
        $validated = $request->validate([
            'ORDERS_ID' => 'required|integer',
            'COURSES_ID' => 'required|integer'
        ]);

        // Cập nhật trạng thái thanh toán
        $orderUpdated = Order::updatePaymentStatus($validated['ORDERS_ID'], 'paid');
        $itemActivated = OrderItem::activateCourse($validated['ORDERS_ID'], $validated['COURSES_ID']);

        if ($orderUpdated && $itemActivated) {
            return response()->json([
                'success' => true,
                'message' => 'Thanh toán thành công! Khóa học đã được kích hoạt.'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Không thể cập nhật trạng thái thanh toán.'
        ], 500);
    }

    /**
     * Lấy danh sách đơn hàng và khóa học của người dùng
     */
    public function getUserOrders($userId)
    {
        $orders = Order::getOrdersByUser($userId);
        $data = [];

        foreach ($orders as $order) {
            $items = OrderItem::getItemsByOrder($order['ORDERS_ID']);
            $data[] = [
                'order' => $order,
                'items' => $items
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
}
