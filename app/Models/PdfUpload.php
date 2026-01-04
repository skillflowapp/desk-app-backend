<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PdfUpload extends Model
{
    protected $fillable = [
        'user_id',
        'filename',
        'storage_path',
        'pages',
        'extracted_text',
        'ocr_processed',
        'status',
        'ocr_error',
    ];

    protected $casts = [
        'ocr_processed' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class);
    }
}
