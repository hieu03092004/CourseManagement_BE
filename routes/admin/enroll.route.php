<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\EnrollmentController;

/*
|--------------------------------------------------------------------------
| Routes: Enroll / Register Course
|--------------------------------------------------------------------------
|
| Các API xử lý việc người dùng đăng ký khoá học:
| 
| - POST /enroll/buy-now       → Mua ngay (tạo order & order_item)
| - POST /enroll/add-to-cart   → Thêm vào giỏ hàng
| - POST /enroll/checkout-cart → Thanh toán các khoá học trong giỏ hàng
| - GET  /enroll/cart          → Lấy danh sách giỏ hàng người dùng
| - GET  /enroll/orders        → Lấy danh sách đơn hàng người dùng
|
*/

Route::post('/buy-now', [EnrollmentController::class, 'buyNow'])->name('enroll.buyNow');
Route::post('/add-to-cart', [EnrollmentController::class, 'addToCart'])->name('enroll.addToCart');
Route::post('/checkout-cart', [EnrollmentController::class, 'checkoutFromCart'])->name('enroll.checkoutCart');

Route::get('/cart', [EnrollmentController::class, 'getCart'])->name('enroll.cart');
Route::get('/orders', [EnrollmentController::class, 'getOrders'])->name('enroll.orders');
Route::post('/remove-from-cart', [EnrollmentController::class, 'removeFromCart']);