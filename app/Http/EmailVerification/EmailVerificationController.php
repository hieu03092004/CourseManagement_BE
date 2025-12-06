<?php

namespace App\Http\EmailVerification;

use App\Http\Controllers\Admin\BaseAPIController;
use App\Http\Requests\EmailVerification\ResendRequest;
use App\Models\User;
use App\Notifications\VerifyEmailApiLink;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Cookie;

class EmailVerificationController extends BaseAPIController
{
    public function verify(Request $request, int $id)
    {
        $user = User::find($id);
        $frontend = env('FRONTEND_URL', config('app.url'));
        $redirectToFrontend = function (array $params = []) use ($frontend) {
            $url = rtrim($frontend, '/') . '/email-verified';
            if (!empty($params)) {
                $url .= '?' . http_build_query($params);
            }
            return redirect()->away($url);
        };

        if (!$user) {
            if ($request->isMethod('get')) {
                return $redirectToFrontend(['status' => 'error','reason' => 'user_not_found']);
            }
            return $this->fail('User not found.', 404, 'USER_NOT_FOUND');
        }

        if (!URL::hasValidSignature($request)) {
            if ($request->isMethod('get')) {
                return $redirectToFrontend(['status' => 'error','reason' => 'invalid_or_expired_link']);
            }
            return $this->fail('Invalid or expired verification link.', 400, 'INVALID_VERIFICATION_LINK');
        }

        if ($user->email_verified_at) {
            if ($request->isMethod('get')) {
                return $redirectToFrontend(['status' => 'success']);
            }
            return $this->ok(['message' => 'Email verified successfully.']);
        }

        $user->email_verified_at = now();
        $user->status = 'active';
        $user->save();

        event(new Verified($user));

        // Tạo token để FE auto login sau khi verify
        $token = null;
        try {
            $token = $user->createToken('web')->plainTextToken;
        } catch (\Throwable $e) {
            $token = null;
        }

        if ($request->isMethod('get')) {
            $params = ['status' => 'success'];
            if ($token) {
                $params['token'] = $token;
            }
            return $redirectToFrontend($params);
        }

        $data = ['message' => 'Email verified successfully.'];
        if ($token) {
            $data['token'] = $token;
        }
        return $this->ok($data);
    }

    public function resend(Request $request)
    {
        $email = $request->string('email')->toString();
        $user = User::where('email', $email)->first();

        if ($user && !$user->email_verified_at) {
            $verificationUrl = URL::temporarySignedRoute(
                'api.email.verify',
                now()->addMinutes(60),
                ['id' => $user->user_id]
            );

            $user->notify(new VerifyEmailApiLink($verificationUrl));
        }

        return $this->ok([
            'message' => 'Verification link resent successfully.',
        ]);
    }
}
