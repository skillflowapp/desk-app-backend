<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamSession extends Model
{
    protected $fillable = [
        'exam_id',
        'student_id',
        'exam_code_id',
        'started_at',
        'submitted_at',
        'status',
        'device_fingerprint',
        'ip_address',
        'meta_data',
        'flagged_for_review',
        'flag_reason',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
        'meta_data' => 'json',
        'flagged_for_review' => 'boolean',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function examCode(): BelongsTo
    {
        return $this->belongsTo(ExamCode::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(StudentAnswer::class);
    }

    public function result()
    {
        return $this->hasOne(Result::class);
    }

    public function isExpired(): bool
    {
        $duration = $this->exam->duration_minutes * 60;
        return $this->started_at->addSeconds($duration)->isPast();
    }

    public function timeRemainingSeconds(): int
    {
        $duration = $this->exam->duration_minutes * 60;
        $elapsed = now()->diffInSeconds($this->started_at);
        return max(0, $duration - $elapsed);
    }
}
