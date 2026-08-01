<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * تاريخ بدون وقت: يُخزَّن في القاعدة بصيغة Y-m-d بالضبط.
 * (الـ cast الافتراضي 'date' يخزن بلاحقة 00:00:00 فتفشل مطابقة السلاسل النصية في SQLite)
 */
class DateOnly implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?Carbon
    {
        return $value === null ? null : Carbon::parse($value)->startOfDay();
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        return $value === null ? null : Carbon::parse($value)->format('Y-m-d');
    }
}
