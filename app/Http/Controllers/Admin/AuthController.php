<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login()
    {
        return response()->json([
            'pageTitle' => 'Đăng nhập trang admin',
        ]);
    }

    public function loginPost(Request $request)
    {
        // TODO: Implement real authentication
        return response()->json([
            'message' => 'Đăng nhập thành công',
            'token' => 'fake_token_' . time(),
            'user' => [
                'id' => 1,
                'email' => $request->input('email'),
                'fullName' => 'Admin User',
            ],
        ]);
    }

    public function logout()
    {
        return response()->json([
            'message' => 'Đăng xuất thành công',
        ]);
    }
}

