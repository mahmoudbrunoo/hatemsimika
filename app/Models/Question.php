<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    public const TYPE_MCQ = 'mcq';
    public const TYPE_ESSAY = 'essay';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['points' => 'decimal:2'];
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class)->orderBy('position');
    }

    public function correctOption(): ?QuestionOption
    {
        return $this->options->firstWhere('is_correct', true);
    }

    public function isMcq(): bool
    {
        return $this->type === self::TYPE_MCQ;
    }
}
