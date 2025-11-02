<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MyAccountController extends Controller
{
    public function index()
    {
        $account = [
            'id' => 1,
            'fullName' => 'Admin User',
            'email' => 'admin@example.com',
            'phone' => '0123456789',
            'avatar' => '/images/default-avatar.png',
        ];

        return response()->json([
            'pageTitle' => 'Thông tin cá nhân',
            'account' => $account,
        ]);
    }

    public function update(Request $request)
    {
        return response()->json([
            'message' => 'Cập nhật thông tin thành công',
            'data' => $request->all(),
        ]);
    }
}

