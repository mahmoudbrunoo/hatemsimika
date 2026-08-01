<?php

namespace App\Models;

use App\Casts\DateOnly;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class DailyActivity extends Model
{
    /** الأعمدة المسموح تتبعها عبر track() */
    public const FIELDS = ['videos_watched', 'quizzes_completed', 'seconds_spent'];

    protected $guarded = [];

    protected function casts(): array
    {
        return ['activity_date' => DateOnly::class];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * تسجيل نشاط اليوم بعملية ذرية واحدة (upsert) —
     * آمنة ضد تكرار الاستدعاء في نفس اليوم وضد الطلبات المتزامنة.
     */
    public static function track(int $userId, string $field, int $amount = 1): void
    {
        if (! in_array($field, self::FIELDS, true)) {
            throw new InvalidArgumentException("عمود نشاط غير معروف: {$field}");
        }

        static::upsert(
            [['user_id' => $userId, 'activity_date' => now()->toDateString(), $field => $amount]],
            ['user_id', 'activity_date'],
            [$field => DB::raw("{$field} + {$amount}")],
        );
    }
}
