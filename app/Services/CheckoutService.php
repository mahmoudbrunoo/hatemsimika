<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Coupon;
use App\Models\Course;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CheckoutService
{
    public function __construct(
        protected WalletService $wallet,
        protected EnrollmentService $enrollment,
    ) {
    }

    /** إنشاء طلب شراء كورس أو كتاب */
    public function createOrder(User $user, Course|Book $item, string $method, ?Coupon $coupon = null, array $extra = []): Order
    {
        $price = $item->effectivePrice();
        $discount = 0.0;

        if ($coupon !== null) {
            if (! $coupon->isUsable()) {
                throw new RuntimeException('كود الخصم غير صالح أو منتهي');
            }
            $discount = $coupon->discountFor($price);
        }

        $total = round($price - $discount, 2);

        return DB::transaction(function () use ($user, $item, $method, $coupon, $price, $discount, $total, $extra) {
            $order = Order::create([
                'number' => Order::generateNumber(),
                'user_id' => $user->id,
                'subtotal' => $price,
                'discount' => $discount,
                'total' => $total,
                'coupon_id' => $coupon?->id,
                'payment_method' => $method,
                'status' => Order::STATUS_PENDING,
                'receipt_path' => $extra['receipt_path'] ?? null,
                'sender_phone' => $extra['sender_phone'] ?? null,
            ]);

            $order->items()->create([
                'purchasable_type' => $item->getMorphClass(),
                'purchasable_id' => $item->getKey(),
                'title' => $item->title,
                'price' => $total,
            ]);

            if ($coupon !== null) {
                $coupon->increment('used_count');
            }

            // الدفع بالمحفظة يتم فوراً
            if ($method === 'wallet') {
                $this->wallet->debit($user, $total, 'purchase', 'تم استخدام الرصيد في الاشتراك بالكورس', $order);
                $this->markPaid($order);
            }

            return $order->refresh();
        });
    }

    /** تأكيد الدفع (يدوي بعد مراجعة الإيصال أو فوري من البوابة/المحفظة) */
    public function markPaid(Order $order, ?User $reviewer = null): void
    {
        if ($order->status === Order::STATUS_PAID) {
            return;
        }

        $order->update([
            'status' => Order::STATUS_PAID,
            'paid_at' => now(),
            'reviewed_by' => $reviewer?->id,
        ]);

        foreach ($order->items as $orderItem) {
            if ($orderItem->purchasable instanceof Course) {
                $this->enrollment->enroll($order->user, $orderItem->purchasable);
            }
        }
    }

    public function reject(Order $order, User $reviewer, ?string $note = null): void
    {
        $order->update([
            'status' => Order::STATUS_REJECTED,
            'reviewed_by' => $reviewer->id,
            'admin_note' => $note,
        ]);
    }
}
