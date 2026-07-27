<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime', 'is_active' => 'boolean', 'value' => 'decimal:2'];
    }

    public function isUsable(): bool
    {
        return $this->is_active
            && ($this->expires_at === null || $this->expires_at->isFuture())
            && ($this->max_uses === null || $this->used_count < $this->max_uses);
    }

    public function discountFor(float $amount): float
    {
        $d = $this->type === 'percent' ? $amount * ((float) $this->value / 100) : (float) $this->value;

        return round(min($d, $amount), 2);
    }
}
