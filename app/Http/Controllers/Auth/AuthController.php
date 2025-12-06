<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Admin\BaseAPIController;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use App\Services\AuthService;
use App\Exceptions\ApiException;
use App\Models\Cart;

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

            $response = [
                'message' => 'Đăng nhập thành công',
                'token' => $token,
                'user' => [
                    'id' => $user->user_id,
                    'email' => $user->email,
                    'fullName' => $user->full_name,
                    'phone' => $user->phone,
                    'roleId' => $user->role_id,
                ],
            ];

            if ($user->role_id == 3) {
                $cart = Cart::where('user_id', $user->user_id)->first();
                $response['cartId'] = $cart ? $cart->cart_id : null;
            }

            return $this->ok($response);
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
        
        // Tạo cart nếu chưa có, hoặc lấy cart hiện có
        $cart = Cart::firstOrCreate(['user_id' => $user->user_id]);
        
        return $this->ok([
            'user' => [
                'id' => $user->user_id,
                'fullName' => $user->full_name,
                'email' => $user->email,
            ],
            'cartId' => $cart->cart_id,
        ]);
    }
    public function forgot(ForgotPasswordRequest $request)
    {
        $email = $request->string('email')->toString();

        Password::sendResetLink(['email' => $email]);

        return $this->ok([
            'message' => 'Password reset link sent to your email.',
        ]);
    }

    public function reset(ResetPasswordRequest $request)
    {
        $status = Password::reset(
            [
                'email' => $request->string('email')->toString(),
                'password' => $request->string('password')->toString(),
                'password_confirmation' => $request->string('password_confirmation')->toString(),
                'token' => $request->string('token')->toString(),
            ],
            function ($user, $password) {
                $user->forceFill([
                    'password_hash' => bcrypt($password),
                ]);

                if (isset($user->remember_token)) {
                    $user->setRememberToken(Str::random(60));
                }

                $user->save();

                try {
                    $user->tokens()?->delete();
                } catch (\Throwable $e) {
                    // ignore
                }
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return $this->fail('Invalid or expired reset token.', 400, 'INVALID_RESET_TOKEN');
        }

        $response = $this->ok([
            'message' => 'Password has been reset successfully.',
        ]);

        return $response->withCookie(cookie()->forget('auth_token'));
    }
}

