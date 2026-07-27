<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LectureProgress extends Model
{
    /** الجدول: الاسم الافتراضي المستنتج يكون lecture_progress بينما الهجرة أنشأت lecture_progresses */
    protected $table = 'lecture_progresses';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'homework_submitted' => 'boolean',
            'exam_passed' => 'boolean',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lecture(): BelongsTo
    {
        return $this->belongsTo(Lecture::class);
    }
}
