ZaloPayController::callback
Đọc raw body JSON từ ZaloPay.
Gửi sang ZaloPayService::verifyCallback($rawBody):
Kiểm tra MAC với key2 để đảm bảo không bị giả mạo.
Nếu OK, giải mã data JSON bên trong, log app_trans_id, zp_trans_id, amount
tôi mún nếu ok thì các các bản ghi của Table `cart_item` (
  `cart_id` int(11) NOT NULL,
  `courses_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
voi cac courses_id nhan duoc se bi xoa di
Tiep theo la insert Tao 1 bản ghi mới trong 
TABLE `orders` (
  `orders_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_price` decimal(12,2) NOT NULL,
  `payment_status` varchar(10) NOT NULL DEFAULT 'pending',
  `payment_time` datetime DEFAULT NULL,
  `cancel_reason` text DEFAULT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
Sau khi tạo xong bản ghi mới trong TABLE `orders`
thì tạo mới các bản ghi trong  TABLE `order_item` (
  `courses_id` int(11) NOT NULL,
  `orders_id` int(11) NOT NULL,
  `unit_price` decimal(12,2) NOT NULL,
  `expired_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
với expired_at bằng thời gian hiện tại + attribute duration của Table courses(được tính lưu là tính số giây bạn hãy xử lý giúp tôi nhé)
Structure Table courses
CREATE TABLE `courses` (
  `courses_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `target` text DEFAULT NULL,
  `result` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `updated_at` datetime NOT NULL,
  `price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `type` varchar(10) NOT NULL,
  `created_at` datetime NOT NULL,
  `discount_percent` decimal(5,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;