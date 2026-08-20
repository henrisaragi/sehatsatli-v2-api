<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PasswordResetOtp;
use App\Models\User;
use App\Notifications\PasswordResetOtpNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PasswordResetOtpService
{
    private const OTP_LENGTH = 6;
    private const EXPIRES_IN_MINUTES = 5;
    private const MAX_VERIFY_ATTEMPTS = 5;

    public function send(User $user): void
    {
        $otp = (string) random_int(10 ** (self::OTP_LENGTH - 1), (10 ** self::OTP_LENGTH) - 1);

        PasswordResetOtp::where('email', $user->email)->delete();

        PasswordResetOtp::create([
            'email' => $user->email,
            'otp' => Hash::make($otp),
            'expires_at' => now()->addMinutes(self::EXPIRES_IN_MINUTES),
        ]);

        $user->notify(new PasswordResetOtpNotification($otp, self::EXPIRES_IN_MINUTES));
    }

    public function verify(string $email, string $otp): bool
    {
        $record = PasswordResetOtp::where('email', $email)->first();

        if (!$record || $record->expires_at->isPast() || $record->attempts >= self::MAX_VERIFY_ATTEMPTS) {
            return false;
        }

        if (!Hash::check($otp, $record->otp)) {
            $record->increment('attempts');

            return false;
        }

        $record->update(['verified_at' => now()]);

        return true;
    }

    public function reset(string $email, string $otp, string $password): bool
    {
        if (!$this->verify($email, $otp)) {
            return false;
        }

        return DB::transaction(function () use ($email, $password) {
            User::where('email', $email)->update([
                'password' => Hash::make($password),
                'reset_password' => 0,
            ]);

            PasswordResetOtp::where('email', $email)->delete();

            return true;
        });
    }
}
