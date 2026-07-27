<?php

namespace App\Contracts;

use App\Models\Order;

/**
 * واجهة بوابات الدفع — جاهزة لتركيب Paymob (كروت / محافظ / فوري)
 * بدون تغيير في منطق الطلبات.
 */
interface PaymentGatewayInterface
{
    /** معرف البوابة (wallet, manual, paymob...) */
    public function key(): string;

    /** بدء عملية الدفع، ويرجع رابط توجيه أو null للدفع اليدوي */
    public function initiate(Order $order): ?string;

    /** معالجة الويب هوك / التحقق من الدفع */
    public function handleCallback(array $payload): void;
}
