<?php

namespace App\Services\Payments;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Order;
use RuntimeException;

/**
 * تكامل Paymob — البنية جاهزة (Architecture Ready).
 * عند التفعيل: ضع مفاتيح PAYMOB_API_KEY / PAYMOB_INTEGRATION_ID في .env
 * وأكمل استدعاءات الـ API في initiate/handleCallback.
 */
class PaymobGateway implements PaymentGatewayInterface
{
    public function key(): string
    {
        return 'paymob';
    }

    public function initiate(Order $order): ?string
    {
        if (! config('services.paymob.api_key')) {
            throw new RuntimeException('بوابة Paymob غير مفعلة بعد — استخدم الدفع اليدوي أو المحفظة');
        }

        // 1) auth token  2) order registration  3) payment key  4) iframe URL
        // تُستكمل عند استلام بيانات حساب Paymob.
        throw new RuntimeException('تكامل Paymob قيد التفعيل');
    }

    public function handleCallback(array $payload): void
    {
        // التحقق من HMAC ثم تحديث الطلب إلى PAID
    }
}
