<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerifyEmailApiLink extends Notification
{
    use Queueable;

    public function __construct(private readonly string $verificationUrl)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Xác thực địa chỉ email')
            ->line('Vui lòng nhấn nút bên dưới để xác thực email.')
            ->action('Xác thực email', $this->verificationUrl)
            ->line('Nếu bạn không tạo tài khoản, hãy bỏ qua email này.');
    }
}
