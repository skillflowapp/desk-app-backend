<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentAnswer extends Model
{
    protected $fillable = [
        'exam_session_id',
        'exam_question_id',
        'answer_text',
        'selected_option',
        'answered_at',
        'time_spent_seconds',
        'is_final',
    ];

    protected $casts = [
        'answered_at' => 'datetime',
        'is_final' => 'boolean',
    ];

    public function examSession(): BelongsTo
    {
        return $this->belongsTo(ExamSession::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(ExamQuestion::class, 'exam_question_id');
    }
}
