<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoaiSua;

use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        // Lấy data từ database qua Model LoaiSua
        $loaiSua = LoaiSua::getAll();

        return response()->json([
            'pageTitle' => 'Danh sách loại sữa',
            'data' => $loaiSua,
            'total' => count($loaiSua),
        ]);
    }

    public function create()
    {
        return response()->json([
            'pageTitle' => 'Thêm sản phẩm',
        ]);
    }

    public function store(Request $request)
    {
        return response()->json([
            'message' => 'Thêm sản phẩm thành công',
            'data' => $request->all(),
        ]);
    }

    public function edit($id)
    {
        $product = ['id' => $id, 'title' => 'Product ' . $id, 'price' => 100000, 'status' => 'active'];

        return response()->json([
            'pageTitle' => 'Sửa sản phẩm',
            'product' => $product,
        ]);
    }

    public function update(Request $request, $id)
    {
        return response()->json([
            'message' => 'Cập nhật sản phẩm thành công',
            'id' => $id,
            'data' => $request->all(),
        ]);
    }

    public function destroy($id)
    {
        return response()->json([
            'message' => 'Xóa sản phẩm thành công',
            'id' => $id,
        ]);
    }
}

