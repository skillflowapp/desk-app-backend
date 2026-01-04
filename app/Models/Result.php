<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Result extends Model
{
    protected $fillable = [
        'exam_session_id',
        'exam_id',
        'student_id',
        'total_marks',
        'obtained_marks',
        'percentage',
        'is_passed',
        'status',
        'question_scores',
        'ai_feedback',
        'teacher_remarks',
        'graded_at',
        'published_at',
    ];

    protected $casts = [
        'question_scores' => 'json',
        'ai_feedback' => 'json',
        'is_passed' => 'boolean',
        'graded_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    public function examSession(): BelongsTo
    {
        return $this->belongsTo(ExamSession::class);
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function calculatePercentage(): float
    {
        if ($this->total_marks == 0) {
            return 0;
        }
        return ($this->obtained_marks / $this->total_marks) * 100;
    }

    public function checkPassed(): bool
    {
        return $this->obtained_marks >= $this->exam->passing_marks;
    }
}
