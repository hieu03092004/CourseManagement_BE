<?php
namespace App\Services;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use App\Notifications\VerifyEmailApiLink;
use App\Exceptions\ApiException;
use App\Models\User;

class AuthService
{
    public function login(string $email, string $password): array
    {
        $user = User::where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password_hash)) {
            throw new ApiException('Thông tin đăng nhập không chính xác', 401, 'INVALID_CREDENTIALS');
        }

        if ($user->status !== 'active') {
            throw new ApiException('Tài khoản chưa được kích hoạt', 403, 'ACCOUNT_INACTIVE');
        }

        $token = $user->createToken('member-api')->plainTextToken;

        return [$user, $token];
    }
    public function register(
        string $fullName,
        string $userName,
        string $email,
        string $phone,
        string $passwordPlain
    ): User {
        if (User::where('email', $email)->exists()) {
            throw new ApiException('Email already taken', 422, 'EMAIL_TAKEN');
        }

        $user = User::create([
            'role_id' => 3, // ví dụ role member
            'full_name' => $fullName,
            'username' => $userName,
            'email' => $email,
            'phone' => $phone,
            'password_hash' => Hash::make($passwordPlain),
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Gửi mail verify
        try {
            $verificationUrl = URL::temporarySignedRoute(
                'api.email.verify',
                now()->addMinutes(60),
                ['id' => $user->user_id] // chú ý dùng khóa chính user_id
            );

            $user->notify(new VerifyEmailApiLink($verificationUrl));
        } catch (\Throwable $e) {
            // không chặn đăng ký nếu gửi mail lỗi (log nếu muốn)
        }

        return $user;
    }
}