<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamQuestion extends Model
{
    protected $fillable = [
        'exam_id',
        'question_number',
        'type',
        'question_text',
        'options',
        'correct_answer',
        'model_answer',
        'marks',
        'explanation',
        'order',
    ];

    protected $casts = [
        'options' => 'json',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(StudentAnswer::class);
    }
}
