<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'price' => 'decimal:2',
            'discount_price' => 'decimal:2',
        ];
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
}
