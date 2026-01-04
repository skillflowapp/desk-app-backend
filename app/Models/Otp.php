<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Otp extends Model
{
    protected $fillable = [
        'user_id',
        'email',
        'code',
        'type',
        'expires_at',
        'is_used',
        'used_at',
        'attempts',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
        'is_used' => 'boolean',
    ];

    /**
     * Get the user that owns this OTP
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if OTP is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if OTP is valid (not expired, not used, not too many attempts)
     */
    public function isValid(): bool
    {
        return !$this->isExpired() && !$this->is_used && $this->attempts < 5;
    }

    /**
     * Verify OTP code
     */
    public function verify(string $code): bool
    {
        if (!$this->isValid()) {
            return false;
        }

        if ($this->code !== $code) {
            $this->increment('attempts');
            return false;
        }

        return true;
    }

    /**
     * Mark OTP as used
     */
    public function markAsUsed(): void
    {
        $this->update([
            'is_used' => true,
            'used_at' => now(),
        ]);
    }

    /**
     * Scope to get only email verification OTPs
     */
    public function scopeEmailVerification($query)
    {
        return $query->where('type', 'email_verification');
    }

    /**
     * Scope to get only password reset OTPs
     */
    public function scopePasswordReset($query)
    {
        return $query->where('type', 'password_reset');
    }

    /**
     * Scope to get valid OTPs
     */
    public function scopeValid($query)
    {
        return $query->where('is_used', false)
            ->where('attempts', '<', 5)
            ->where('expires_at', '>', now());
    }
}
