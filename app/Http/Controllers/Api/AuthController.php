<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\AuditLog;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController
{
    /**
     * Register a new user with a role
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:teacher,student',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Assign role
        $role = \App\Models\Role::where('name', $validated['role'])->first();
        $user->roles()->attach($role);

        AuditLog::log(
            userId: $user->id,
            action: 'user_registered',
            entityType: 'user',
            entityId: $user->id,
            newValues: ['email' => $user->email, 'role' => $validated['role']]
        );

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
        ], 201);
    }

    /**
     * Login user and return auth token
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            AuditLog::log(
                userId: null,
                action: 'login_failed',
                status: 'failed',
                errorMessage: 'Invalid credentials'
            );

            throw ValidationException::withMessages([
                'email' => 'The provided credentials are incorrect.',
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        AuditLog::log(
            userId: $user->id,
            action: 'login',
            entityType: 'user',
            entityId: $user->id,
            newValues: ['logged_in' => true]
        );

        return response()->json([
            'message' => 'Login successful',
            'user' => $user->load('roles'),
            'token' => $token,
            'token_type' => 'Bearer',
        ], 200);
    }

    /**
     * Get authenticated user
     */
    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user()->load('roles'),
        ], 200);
    }

    /**
     * Logout user - revoke token
     */
    public function logout(Request $request)
    {
        AuditLog::log(
            userId: $request->user()->id,
            action: 'logout',
            entityType: 'user',
            entityId: $request->user()->id
        );

        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ], 200);
    }

    /**
     * Refresh token
     */
    public function refresh(Request $request)
    {
        $user = $request->user();
        
        // Revoke old token
        $user->currentAccessToken()->delete();
        
        // Create new token
        $newToken = $user->createToken('auth_token')->plainTextToken;

        AuditLog::log(
            userId: $user->id,
            action: 'token_refresh'
        );

        return response()->json([
            'message' => 'Token refreshed',
            'token' => $newToken,
            'token_type' => 'Bearer',
        ], 200);
    }

    /**
     * Send OTP for email verification
     */
    public function sendEmailVerificationOtp(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|exists:users',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if ($user->email_verified_at) {
            return response()->json([
                'message' => 'Email is already verified',
            ], 400);
        }

        $otpService = app(OtpService::class);
        $otpService->sendEmailVerificationOtp($user);

        AuditLog::log(
            userId: $user->id,
            action: 'email_verification_otp_sent',
            entityType: 'otp',
            newValues: ['email' => $user->email]
        );

        return response()->json([
            'message' => 'OTP sent to your email',
            'email' => $user->email,
        ], 200);
    }

    /**
     * Verify email with OTP
     */
    public function verifyEmailOtp(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|exists:users',
            'otp_code' => 'required|string|size:6',
        ]);

        $user = User::where('email', $validated['email'])->first();

        try {
            $otpService = app(OtpService::class);
            $otpService->verifyOtp($user, $validated['otp_code'], 'email_verification');

            // Mark email as verified
            $user->update(['email_verified_at' => now()]);
            $otpService->markOtpAsUsed($user, 'email_verification');

            AuditLog::log(
                userId: $user->id,
                action: 'email_verified',
                entityType: 'user',
                entityId: $user->id,
                newValues: ['email_verified_at' => now()]
            );

            return response()->json([
                'message' => 'Email verified successfully',
                'user' => $user,
            ], 200);
        } catch (\Exception $e) {
            AuditLog::log(
                userId: $user->id,
                action: 'email_verification_failed',
                status: 'failed',
                errorMessage: $e->getMessage()
            );

            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Send OTP for password reset
     */
    public function sendPasswordResetOtp(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|exists:users',
        ]);

        $user = User::where('email', $validated['email'])->first();

        $otpService = app(OtpService::class);
        $otpService->sendPasswordResetOtp($user);

        AuditLog::log(
            userId: $user->id,
            action: 'password_reset_otp_sent',
            entityType: 'otp',
            newValues: ['email' => $user->email]
        );

        return response()->json([
            'message' => 'Password reset OTP sent to your email',
            'email' => $user->email,
        ], 200);
    }

    /**
     * Reset password with OTP
     */
    public function resetPasswordWithOtp(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|exists:users',
            'otp_code' => 'required|string|size:6',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::where('email', $validated['email'])->first();

        try {
            $otpService = app(OtpService::class);
            $otpService->verifyOtp($user, $validated['otp_code'], 'password_reset');

            // Update password
            $user->update(['password' => Hash::make($validated['new_password'])]);
            $otpService->markOtpAsUsed($user, 'password_reset');

            AuditLog::log(
                userId: $user->id,
                action: 'password_reset',
                entityType: 'user',
                entityId: $user->id,
                newValues: ['password_changed' => true]
            );

            return response()->json([
                'message' => 'Password reset successfully',
            ], 200);
        } catch (\Exception $e) {
            AuditLog::log(
                userId: $user->id,
                action: 'password_reset_failed',
                status: 'failed',
                errorMessage: $e->getMessage()
            );

            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Check OTP validity without verification
     */
    public function checkOtpValidity(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|exists:users',
            'type' => 'required|in:email_verification,password_reset',
        ]);

        $user = User::where('email', $validated['email'])->first();

        $otpService = app(OtpService::class);
        $remainingTime = $otpService->getOtpExpiryTime($user, $validated['type']);

        if ($remainingTime === null) {
            return response()->json([
                'message' => 'No valid OTP found',
                'is_valid' => false,
            ], 200);
        }

        return response()->json([
            'message' => 'OTP is valid',
            'is_valid' => true,
            'expires_in_seconds' => $remainingTime,
        ], 200);
    }
}
