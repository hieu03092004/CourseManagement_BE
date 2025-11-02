<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index()
    {
        $accounts = [
            ['id' => 1, 'fullName' => 'Admin User', 'email' => 'admin@example.com', 'status' => 'active'],
            ['id' => 2, 'fullName' => 'Editor User', 'email' => 'editor@example.com', 'status' => 'active'],
            ['id' => 3, 'fullName' => 'Test User', 'email' => 'test@example.com', 'status' => 'inactive'],
        ];

        return response()->json([
            'pageTitle' => 'Danh sách tài khoản',
            'accounts' => $accounts,
        ]);
    }

    public function create()
    {
        return response()->json([
            'pageTitle' => 'Thêm tài khoản',
        ]);
    }

    public function store(Request $request)
    {
        return response()->json([
            'message' => 'Thêm tài khoản thành công',
            'data' => $request->all(),
        ]);
    }

    public function edit($id)
    {
        $account = ['id' => $id, 'fullName' => 'User ' . $id, 'email' => 'user' . $id . '@example.com', 'status' => 'active'];

        return response()->json([
            'pageTitle' => 'Sửa tài khoản',
            'account' => $account,
        ]);
    }

    public function update(Request $request, $id)
    {
        return response()->json([
            'message' => 'Cập nhật tài khoản thành công',
            'id' => $id,
            'data' => $request->all(),
        ]);
    }

    public function destroy($id)
    {
        return response()->json([
            'message' => 'Xóa tài khoản thành công',
            'id' => $id,
        ]);
    }
}

