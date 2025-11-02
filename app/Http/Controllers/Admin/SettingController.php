<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function general()
    {
        $settings = [
            'websiteName' => 'My Website',
            'logo' => '/images/logo.png',
            'phone' => '0123456789',
            'email' => 'contact@example.com',
            'address' => '123 Street, City',
        ];

        return response()->json([
            'pageTitle' => 'Cài đặt chung',
            'settings' => $settings,
        ]);
    }

    public function update(Request $request)
    {
        return response()->json([
            'message' => 'Cập nhật cài đặt thành công',
            'data' => $request->all(),
        ]);
    }
}

