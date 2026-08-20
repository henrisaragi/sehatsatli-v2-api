<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordWithOtpRequest;
use App\Http\Requests\Auth\SendPasswordResetOtpRequest;
use App\Http\Requests\Auth\VerifyPasswordResetOtpRequest;
use App\Models\User;
use App\Services\PasswordResetOtpService;
use Illuminate\Http\JsonResponse;

class PasswordResetOtpController extends Controller
{
    public function __construct(private readonly PasswordResetOtpService $otpService)
    {
    }

    /**
     * Kirim kode OTP reset password ke email user.
     */
    public function sendOtp(SendPasswordResetOtpRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->firstOrFail();

        $this->otpService->send($user);

        return response()->json([
            'success' => true,
            'message' => 'Kode OTP telah dikirim ke email Anda',
        ]);
    }

    /**
     * Verifikasi kode OTP tanpa mengganti password (opsional, untuk UX cek OTP dulu).
     */
    public function verifyOtp(VerifyPasswordResetOtpRequest $request): JsonResponse
    {
        $valid = $this->otpService->verify($request->email, $request->otp);

        if (!$valid) {
            return response()->json([
                'success' => false,
                'message' => 'Kode OTP tidak valid atau sudah kedaluwarsa',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Kode OTP valid',
        ]);
    }

    /**
     * Reset password menggunakan kode OTP.
     */
    public function reset(ResetPasswordWithOtpRequest $request): JsonResponse
    {
        $reset = $this->otpService->reset($request->email, $request->otp, $request->password);

        if (!$reset) {
            return response()->json([
                'success' => false,
                'message' => 'Kode OTP tidak valid atau sudah kedaluwarsa',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil direset',
        ]);
    }
}
