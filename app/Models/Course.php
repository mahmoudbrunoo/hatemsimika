<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    public const CATEGORY_SEMESTER_1 = 'semester_1';
    public const CATEGORY_SEMESTER_2 = 'semester_2';
    public const CATEGORY_FULL_BUNDLE = 'full_bundle';
    public const CATEGORY_MONTHLY = 'monthly';
    public const CATEGORY_THREE_MONTHS = 'three_months';

    public const CATEGORIES = [
        self::CATEGORY_SEMESTER_1 => 'الترم الأول',
        self::CATEGORY_SEMESTER_2 => 'الترم الثاني',
        self::CATEGORY_FULL_BUNDLE => 'باقة الترم كامل',
        self::CATEGORY_MONTHLY => 'اشتراك شهري',
        self::CATEGORY_THREE_MONTHS => 'باقة 3 شهور',
    ];

    /** التصنيفات المتاحة لكل صف دراسي */
    public const CATEGORIES_BY_YEAR = [
        1 => [self::CATEGORY_SEMESTER_1, self::CATEGORY_SEMESTER_2, self::CATEGORY_FULL_BUNDLE],
        2 => [self::CATEGORY_MONTHLY, self::CATEGORY_THREE_MONTHS],
        3 => [self::CATEGORY_MONTHLY, self::CATEGORY_THREE_MONTHS],
    ];

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
            'price' => 'decimal:2',
            'discount_price' => 'decimal:2',
        ];
    }

    public function lectures(): HasMany
    {
        return $this->hasMany(Lecture::class)->orderBy('position');
    }

    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'enrollments')->withPivot(['status', 'expires_at']);
    }

    public function scopePublished($q)
    {
        return $q->where('is_published', true);
    }

    public function scopeForYear($q, int $year)
    {
        return $q->where('academic_year', $year);
    }

    public function effectivePrice(): float
    {
        return (float) ($this->discount_price ?? $this->price);
    }

    public function discountPercent(): ?int
    {
        if ($this->discount_price === null || (float) $this->price <= 0) {
            return null;
        }

        return (int) round((1 - ((float) $this->discount_price / (float) $this->price)) * 100);
    }

    public function totalVideos(): int
    {
        return Video::whereIn('lecture_id', $this->lectures()->pluck('id'))->count();
    }

    public function totalDurationSeconds(): int
    {
        return (int) Video::whereIn('lecture_id', $this->lectures()->pluck('id'))->sum('duration_seconds');
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }

    public function yearLabel(): string
    {
        return User::YEARS[$this->academic_year] ?? (string) $this->academic_year;
    }
}
