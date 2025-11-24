<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'full_name'         => 'required|string|max:255',
            'username'          => 'required|string|max:255|unique:user,username',
            'email'             => 'required|email|max:255|unique:user,email',
            'phone'             => 'required|string|max:15|unique:user,phone',
            'password'          => 'required|min:6|confirmed',
        ]);

        $user = User::create([
            'role_id'       => 3,
            'full_name'     => $validated['full_name'],
            'username'      => $validated['username'],
            'email'         => $validated['email'],
            'phone'         => $validated['phone'],
            'password_hash' => Hash::make($validated['password']),
            'status'        => 'active', 
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        return response()->json([
            'message' => 'Đăng ký thành công',
            'user'    => $user
        ], 201);
    }
}
