<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    public const STATUS_PENDING = 'PENDING';
    public const STATUS_PAID = 'PAID';
    public const STATUS_FAILED = 'FAILED';
    public const STATUS_REJECTED = 'REJECTED';
    public const STATUS_REFUNDED = 'REFUNDED';

    public const STATUSES = [
        self::STATUS_PENDING => 'قيد المراجعة',
        self::STATUS_PAID => 'مدفوع',
        self::STATUS_FAILED => 'فشل',
        self::STATUS_REJECTED => 'مرفوض',
        self::STATUS_REFUNDED => 'مسترد',
    ];

    public const PAYMENT_METHODS = [
        'wallet' => 'المحفظة',
        'manual_vodafone' => 'فودافون كاش',
        'manual_instapay' => 'انستاباي',
        'center_code' => 'كود سنتر',
        'paymob_card' => 'بطاقة بنكية',
        'paymob_wallet' => 'محفظة إلكترونية',
        'fawry' => 'فوري',
    ];

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function paymentMethodLabel(): string
    {
        return self::PAYMENT_METHODS[$this->payment_method] ?? $this->payment_method;
    }

    public static function generateNumber(): string
    {
        do {
            $number = (string) random_int(1000000, 99999999);
        } while (static::where('number', $number)->exists());

        return $number;
    }
}
