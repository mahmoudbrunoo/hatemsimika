<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Order;
use App\Services\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** مراجعة الطلبات وإيصالات الدفع اليدوي (فودافون كاش / انستاباي) */
class OrdersController extends Controller
{
    public function __construct(protected CheckoutService $checkout)
    {
    }

    public function index(Request $request): View
    {
        $status = $request->string('status')->toString() ?: null;
        $search = $request->string('q')->toString() ?: null;

        $orders = Order::query()
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($search, fn ($q) => $q
                ->where('number', 'like', "%{$search}%")
                ->orWhereHas('user', fn ($qq) => $qq
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")))
            ->with(['user', 'items', 'coupon'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.orders.index', compact('orders', 'status', 'search'));
    }

    /** اعتماد الإيصال => دفع الطلب وفتح الكورس تلقائياً */
    public function approve(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->status === Order::STATUS_PENDING, 400, 'الطلب ليس قيد المراجعة');

        $this->checkout->markPaid($order, $request->user());

        AuditLog::record('order.approve', $order, ['number' => $order->number, 'total' => (string) $order->total]);

        return back()->with('status', 'تم اعتماد الطلب رقم ' . $order->number . ' وتفعيل المحتوى.');
    }

    public function reject(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->status === Order::STATUS_PENDING, 400, 'الطلب ليس قيد المراجعة');

        $data = $request->validate(
            ['note' => ['required', 'string', 'max:500']],
            [],
            ['note' => 'سبب الرفض'],
        );

        $this->checkout->reject($order, $request->user(), $data['note']);

        AuditLog::record('order.reject', $order, ['number' => $order->number, 'note' => $data['note']]);

        return back()->with('status', 'تم رفض الطلب.');
    }
}
