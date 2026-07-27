<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyActivity extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['activity_date' => 'date'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function track(int $userId, string $field, int $amount = 1): void
    {
        $row = static::firstOrCreate(
            ['user_id' => $userId, 'activity_date' => now()->toDateString()],
        );
        $row->increment($field, $amount);
    }
}
