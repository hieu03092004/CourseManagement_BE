<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Admin\BaseAPIController;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use App\Services\AuthService;
use App\Exceptions\ApiException;

class AuthController extends BaseAPIController
{
    public function __construct(private readonly AuthService $auth)
    {
    }
    public function register(RegisterRequest $request)
    {
        $user = $this->auth->register(
            $request->string('fullName')->toString(),
            $request->string('userName')->toString(),
            $request->string('email')->toString(),
            $request->string('phone')->toString(),
            $request->string('passwordHash')->toString() // hoặc 'password'
        );

        return $this->ok([
            'message' => 'Đăng ký thành công. Vui lòng kiểm tra email để xác thực tài khoản.',
            'user' => [
                'id' => $user->user_id,
                'fullName' => $user->full_name,
                'email' => $user->email,
            ],
        ]);
    }
    public function login(LoginRequest $request)
    {
        try {
            [$user, $token] = $this->auth->login(
                $request->string('email')->toString(),
                $request->string('password')->toString()
            );

            return $this->ok([
                'message' => 'Đăng nhập thành công',
                'token' => $token,
                'user' => [
                    'id' => $user->user_id,
                    'email' => $user->email,
                    'fullName' => $user->full_name,
                ],
            ]);
        } catch (ApiException $e) {
            return $this->fail(
                $e->getMessage(),
                $e->status,
                $e->codeStr,
                $e->errors
            );
        } catch (\Throwable $e) {
            return $this->fail(
                'An error occurred while logging in',
                500,
                'INTERNAL_ERROR',
                ['message' => $e->getMessage()]
            );
        }
    }
    public function me(Request $request)
    {
        $user = $request->user();
        return $this->ok([
            'user' => [
                'id' => $user->user_id,
                'fullName' => $user->full_name,
                'email' => $user->email,
            ],
        ]);
    }
}

