<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Exam extends Model
{
    protected $fillable = [
        'teacher_id',
        'pdf_upload_id',
        'title',
        'description',
        'duration_minutes',
        'total_marks',
        'passing_marks',
        'ai_prompt',
        'status',
        'show_answers_after',
        'published_at',
        'archived_at',
    ];

    protected $casts = [
        'ai_prompt' => 'json',
        'show_answers_after' => 'boolean',
        'published_at' => 'datetime',
        'archived_at' => 'datetime',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function pdfUpload(): BelongsTo
    {
        return $this->belongsTo(PdfUpload::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(ExamQuestion::class);
    }

    public function codes(): HasMany
    {
        return $this->hasMany(ExamCode::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(ExamSession::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(Result::class);
    }
}
