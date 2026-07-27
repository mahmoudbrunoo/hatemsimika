<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exam extends Model
{
    public const TYPE_QUIZ = 'quiz';           // كويز محاضرة
    public const TYPE_SHAMEL = 'shamel';       // امتحان شامل
    public const TYPE_EVALUATION = 'evaluation'; // اختبار تقييم
    public const TYPE_PERSONAL = 'personal';   // امتحان خاص بيك (اختبر أخطاءك)

    public const TYPES = [
        self::TYPE_QUIZ => 'كويز محاضرة',
        self::TYPE_SHAMEL => 'امتحان شامل',
        self::TYPE_EVALUATION => 'اختبار تقييم',
        self::TYPE_PERSONAL => 'امتحان خاص',
    ];

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'hints_enabled' => 'boolean',
            'is_published' => 'boolean',
            'window_opens_at' => 'datetime',
            'window_closes_at' => 'datetime',
        ];
    }

    public function lecture(): BelongsTo
    {
        return $this->belongsTo(Lecture::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('position');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }

    public function passingPercent(): int
    {
        return (int) ($this->passing_percent
            ?? $this->lecture?->passingPercent()
            ?? $this->course?->passing_percent
            ?? 60);
    }

    public function totalPoints(): float
    {
        return (float) $this->questions()->sum('points');
    }

    /** هل نافذة الامتحان مفتوحة حالياً؟ */
    public function isWindowOpen(): bool
    {
        $now = now();

        return ($this->window_opens_at === null || $now->gte($this->window_opens_at))
            && ($this->window_closes_at === null || $now->lte($this->window_closes_at));
    }

    /** هل يسمح بعرض الإجابات النموذجية الآن؟ (منع الغش في الشوامل) */
    public function canShowModelAnswers(): bool
    {
        if ($this->answers_policy === 'instant') {
            return true;
        }

        return $this->window_closes_at !== null && now()->gt($this->window_closes_at);
    }

    public function attemptsFor(User $user)
    {
        return $this->attempts()->where('user_id', $user->id);
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }
}
