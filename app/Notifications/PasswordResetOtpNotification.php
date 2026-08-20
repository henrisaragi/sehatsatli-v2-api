<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordResetOtpNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly string $otp, private readonly int $expiresInMinutes)
    {
    }

    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Kode OTP Reset Password - SehatSatli')
            ->greeting('Halo ' . ($notifiable->name ?? '') . ',')
            ->line('Gunakan kode OTP berikut untuk mereset password akun SehatSatli Anda.')
            ->line(new \Illuminate\Support\HtmlString('<h2 style="letter-spacing:4px">' . $this->otp . '</h2>'))
            ->line("Kode berlaku selama {$this->expiresInMinutes} menit.")
            ->line('Abaikan email ini jika Anda tidak meminta reset password.');
    }
}
