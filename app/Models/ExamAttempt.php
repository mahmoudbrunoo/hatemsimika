<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamAttempt extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'submitted_at' => 'datetime',
            'score' => 'decimal:2',
            'total' => 'decimal:2',
            'percent' => 'decimal:2',
            'passed' => 'boolean',
            'fully_graded' => 'boolean',
        ];
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(AttemptAnswer::class);
    }

    public function isSubmitted(): bool
    {
        return $this->submitted_at !== null;
    }

    /** الوقت المتبقي بالثواني — محسوب من السيرفر دايماً، فالعداد ميقفش أبداً حتى لو الصفحة اتقفلت */
    public function remainingSeconds(): int
    {
        return max(0, (int) now()->diffInSeconds($this->expiresAt(), false));
    }

    /** موعد انتهاء المحاولة = وقت البدء + مدة الامتحان */
    public function expiresAt(): \Illuminate\Support\Carbon
    {
        return $this->started_at->copy()->addMinutes($this->exam->duration_minutes);
    }

    /** محاولة جارية عدّى وقتها ولسه متسلمتش — لازم تتسلم تلقائياً */
    public function isExpired(): bool
    {
        return ! $this->isSubmitted() && $this->remainingSeconds() <= 0;
    }
}
