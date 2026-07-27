<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Lecture extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'is_free_preview' => 'boolean',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function videos(): HasMany
    {
        return $this->hasMany(Video::class)->orderBy('position');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class)->orderBy('position');
    }

    public function assignment(): HasOne
    {
        return $this->hasOne(Assignment::class);
    }

    public function exam(): HasOne
    {
        return $this->hasOne(Exam::class);
    }

    public function qaThreads(): HasMany
    {
        return $this->hasMany(QaThread::class);
    }

    public function passingPercent(): int
    {
        return (int) ($this->passing_percent ?? $this->course->passing_percent ?? 60);
    }

    /** المحاضرة السابقة حسب الترتيب */
    public function previous(): ?self
    {
        return $this->course->lectures()
            ->where('is_published', true)
            ->where('position', '<', $this->position)
            ->orderByDesc('position')
            ->first();
    }
}
