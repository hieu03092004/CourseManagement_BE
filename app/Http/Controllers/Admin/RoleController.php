<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        $roles = [
            ['id' => 1, 'title' => 'Admin', 'description' => 'Full access'],
            ['id' => 2, 'title' => 'Editor', 'description' => 'Edit content'],
            ['id' => 3, 'title' => 'Viewer', 'description' => 'View only'],
        ];

        return response()->json([
            'pageTitle' => 'Nhóm quyền',
            'roles' => $roles,
        ]);
    }

    public function create()
    {
        return response()->json([
            'pageTitle' => 'Thêm nhóm quyền',
        ]);
    }

    public function store(Request $request)
    {
        return response()->json([
            'message' => 'Thêm nhóm quyền thành công',
            'data' => $request->all(),
        ]);
    }

    public function edit($id)
    {
        $role = ['id' => $id, 'title' => 'Role ' . $id, 'description' => 'Description'];

        return response()->json([
            'pageTitle' => 'Sửa nhóm quyền',
            'role' => $role,
        ]);
    }

    public function update(Request $request, $id)
    {
        return response()->json([
            'message' => 'Cập nhật nhóm quyền thành công',
            'id' => $id,
            'data' => $request->all(),
        ]);
    }

    public function destroy($id)
    {
        return response()->json([
            'message' => 'Xóa nhóm quyền thành công',
            'id' => $id,
        ]);
    }
}

