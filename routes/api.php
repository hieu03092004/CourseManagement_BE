<?php

use App\Http\Controllers\Payment\ZaloPayController;
use Illuminate\Support\Facades\Route;

Route::prefix('payments/zalopay')->group(function () {
    // Tạo order mới
    Route::post('/create', [ZaloPayController::class, 'createOrder']);

    // Query order status
    Route::get('/query', [ZaloPayController::class, 'queryOrder']);

    // Return URL từ ZaloPay
    Route::get('/return', [ZaloPayController::class, 'returnUrl'])
        ->name('zalopay.return');


    Route::get('/order-status/{app_trans_id}', [ZaloPayController::class, 'getOrderStatus']);
});
