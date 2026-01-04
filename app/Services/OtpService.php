<?php

namespace App\Services;

use App\Models\User;
use App\Models\Otp;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class OtpService
{
    /**
     * Generate and send OTP for email verification
     */
    public function sendEmailVerificationOtp(User $user): Otp
    {
        // Invalidate previous OTPs for this user
        Otp::where('user_id', $user->id)
            ->where('type', 'email_verification')
            ->update(['is_used' => true]);

        // Generate 6-digit OTP
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Create OTP record (valid for 15 minutes)
        $otp = Otp::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'code' => $code,
            'type' => 'email_verification',
            'expires_at' => now()->addMinutes(15),
        ]);

        // Send OTP via email
        $this->sendOtpEmail($user, $code, 'email_verification');

        return $otp;
    }

    /**
     * Generate and send OTP for password reset
     */
    public function sendPasswordResetOtp(User $user): Otp
    {
        // Invalidate previous OTPs for this user
        Otp::where('user_id', $user->id)
            ->where('type', 'password_reset')
            ->update(['is_used' => true]);

        // Generate 6-digit OTP
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Create OTP record (valid for 10 minutes)
        $otp = Otp::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'code' => $code,
            'type' => 'password_reset',
            'expires_at' => now()->addMinutes(10),
        ]);

        // Send OTP via email
        $this->sendOtpEmail($user, $code, 'password_reset');

        return $otp;
    }

    /**
     * Verify OTP code
     */
    public function verifyOtp(User $user, string $code, string $type): bool
    {
        $otp = Otp::where('user_id', $user->id)
            ->where('type', $type)
            ->orderByDesc('created_at')
            ->first();

        if (!$otp) {
            throw new ModelNotFoundException('OTP not found');
        }

        if (!$otp->verify($code)) {
            throw new \Exception('Invalid or expired OTP');
        }

        return true;
    }

    /**
     * Mark OTP as used after successful verification
     */
    public function markOtpAsUsed(User $user, string $type): void
    {
        $otp = Otp::where('user_id', $user->id)
            ->where('type', $type)
            ->where('is_used', false)
            ->latest()
            ->first();

        if ($otp) {
            $otp->markAsUsed();
        }
    }

    /**
     * Send OTP email
     */
    private function sendOtpEmail(User $user, string $code, string $type): void
    {
        $subject = $type === 'email_verification'
            ? 'Verify Your SKILLFLOW Email'
            : 'Reset Your SKILLFLOW Password';

        $htmlContent = view('emails.otp', [
            'user' => $user,
            'code' => $code,
            'type' => $type,
        ])->render();

        \Mail::send([], [], function ($mail) use ($user, $subject, $htmlContent) {
            $mail->to($user->email)
                ->subject($subject)
                ->from(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME'))
                ->html($htmlContent);
        });
    }

    /**
     * Get remaining time for OTP
     */
    public function getOtpExpiryTime(User $user, string $type): ?int
    {
        $otp = Otp::where('user_id', $user->id)
            ->where('type', $type)
            ->where('is_used', false)
            ->latest()
            ->first();

        if (!$otp || $otp->isExpired()) {
            return null;
        }

        return $otp->expires_at->diffInSeconds(now());
    }
}
