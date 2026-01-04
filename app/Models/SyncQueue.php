<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SyncQueue extends Model
{
    protected $fillable = [
        'student_id',
        'entity_type',
        'entity_id',
        'action',
        'payload',
        'status',
        'retry_count',
        'error_message',
        'synced_at',
    ];

    protected $casts = [
        'payload' => 'json',
        'synced_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
