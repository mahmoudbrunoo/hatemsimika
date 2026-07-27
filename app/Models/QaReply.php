<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QaReply extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['is_official_answer' => 'boolean'];
    }

    public function thread(): BelongsTo
    {
        return $this->belongsTo(QaThread::class, 'qa_thread_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
