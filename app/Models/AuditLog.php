<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['meta' => 'array'];
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function target(): MorphTo
    {
        return $this->morphTo();
    }

    /** تسجيل حدث في سجل التدقيق */
    public static function record(string $action, ?Model $target = null, array $meta = [], ?int $actorId = null): self
    {
        return static::create([
            'actor_id' => $actorId ?? auth()->id(),
            'action' => $action,
            'target_type' => $target?->getMorphClass(),
            'target_id' => $target?->getKey(),
            'meta' => $meta ?: null,
            'ip' => request()->ip(),
            'user_agent' => (string) request()->userAgent(),
        ]);
    }
}
