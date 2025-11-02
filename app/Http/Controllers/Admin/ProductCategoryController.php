<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    public function index()
    {
        $categories = [
            ['id' => 1, 'title' => 'Category 1', 'status' => 'active', 'position' => 1],
            ['id' => 2, 'title' => 'Category 2', 'status' => 'active', 'position' => 2],
            ['id' => 3, 'title' => 'Category 3', 'status' => 'inactive', 'position' => 3],
        ];

        return response()->json([
            'pageTitle' => 'Danh mục sản phẩm',
            'categories' => $categories,
        ]);
    }

    public function create()
    {
        return response()->json([
            'pageTitle' => 'Thêm danh mục',
        ]);
    }

    public function store(Request $request)
    {
        return response()->json([
            'message' => 'Thêm danh mục thành công',
            'data' => $request->all(),
        ]);
    }

    public function edit($id)
    {
        $category = ['id' => $id, 'title' => 'Category ' . $id, 'status' => 'active', 'position' => 1];

        return response()->json([
            'pageTitle' => 'Sửa danh mục',
            'category' => $category,
        ]);
    }

    public function update(Request $request, $id)
    {
        return response()->json([
            'message' => 'Cập nhật danh mục thành công',
            'id' => $id,
            'data' => $request->all(),
        ]);
    }

    public function destroy($id)
    {
        return response()->json([
            'message' => 'Xóa danh mục thành công',
            'id' => $id,
        ]);
    }
}

