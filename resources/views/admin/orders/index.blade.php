@extends('layouts.admin')

@section('title', 'الطلبات والإيصالات — لوحة التحكم')
@section('page-title', 'الطلبات والإيصالات')

@section('page')
    {{-- فلاتر البحث والحالة --}}
    <form method="GET" action="{{ route('admin.orders.index') }}" class="card-pad mb-6 flex flex-wrap items-end gap-3">
        <div class="min-w-48 flex-1">
            <label for="q" class="label">بحث</label>
            <input id="q" name="q" type="text" value="{{ $search }}" class="input"
                   placeholder="رقم الطلب أو اسم الطالب أو الموبايل">
        </div>
        <div>
            <label for="status" class="label">الحالة</label>
            <select id="status" name="status" class="input">
                <option value="">كل الحالات</option>
                @foreach (\App\Models\Order::STATUSES as $value => $label)
                    <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn-primary">تصفية</button>
        @if ($search || $status)
            <a href="{{ route('admin.orders.index') }}" class="btn-secondary">مسح الفلاتر</a>
        @endif
    </form>

    @if ($orders->isEmpty())
        <div class="card-pad text-center text-slate-500 dark:text-slate-400">لا توجد طلبات مطابقة.</div>
    @else
        <div class="table-box">
            <table class="table">
                <thead>
                    <tr>
                        <th>رقم الطلب</th>
                        <th>الطالب</th>
                        <th>المحتوى</th>
                        <th>الإجمالي</th>
                        <th>طريقة الدفع</th>
                        <th>الإيصال</th>
                        <th>التاريخ</th>
                        <th>الحالة</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orders as $order)
                        <tr>
                            <td class="font-mono font-bold" dir="ltr">#{{ $order->number }}</td>
                            <td>
                                <p class="font-bold text-slate-900 dark:text-white">{{ $order->user->name }}</p>
                                <p class="text-xs font-semibold text-slate-400" dir="ltr">{{ $order->user->phone }}</p>
                            </td>
                            <td>
                                <ul class="space-y-0.5 text-xs font-semibold">
                                    @foreach ($order->items as $item)
                                        <li>{{ $item->title }} @if ($item->quantity > 1) × {{ $item->quantity }} @endif</li>
                                    @endforeach
                                </ul>
                                @if ($order->coupon)
                                    <span class="badge-sky mt-1">كوبون: {{ $order->coupon->code }}</span>
                                @endif
                            </td>
                            <td class="font-bold">
                                {{ egp($order->total) }}
                                @if ((float) $order->discount > 0)
                                    <p class="text-xs font-semibold text-emerald-600 dark:text-emerald-400">خصم {{ egp($order->discount) }}</p>
                                @endif
                            </td>
                            <td>
                                <span class="badge-gray">{{ $order->paymentMethodLabel() }}</span>
                                @if ($order->sender_phone)
                                    <p class="mt-1 text-xs font-semibold text-slate-400" dir="ltr">من: {{ $order->sender_phone }}</p>
                                @endif
                            </td>
                            <td>
                                @if ($order->receipt_path)
                                    <a href="{{ $order->receipt_path }}" target="_blank" title="فتح الإيصال بالحجم الكامل">
                                        <img src="{{ $order->receipt_path }}"
                                             alt="إيصال التحويل" class="h-14 w-14 rounded-lg border border-slate-300 object-cover dark:border-slate-700">
                                    </a>
                                @else
                                    <span class="text-xs text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="text-xs font-semibold text-slate-500">{{ $order->created_at->format('Y/m/d H:i') }}</td>
                            <td>
                                @if ($order->status === \App\Models\Order::STATUS_PENDING)
                                    <span class="badge-amber">{{ $order->statusLabel() }}</span>
                                @elseif ($order->status === \App\Models\Order::STATUS_PAID)
                                    <span class="badge-green">{{ $order->statusLabel() }}</span>
                                @elseif (in_array($order->status, [\App\Models\Order::STATUS_FAILED, \App\Models\Order::STATUS_REJECTED]))
                                    <span class="badge-red">{{ $order->statusLabel() }}</span>
                                @else
                                    <span class="badge-gray">{{ $order->statusLabel() }}</span>
                                @endif
                                @if ($order->admin_note)
                                    <p class="mt-1 max-w-40 text-xs font-semibold text-slate-400">{{ $order->admin_note }}</p>
                                @endif
                            </td>
                            <td>
                                @if ($order->status === \App\Models\Order::STATUS_PENDING)
                                    <div x-data="{ rejecting: false }" class="space-y-2">
                                        <div x-show="!rejecting" class="flex gap-2">
                                            <form method="POST" action="{{ route('admin.orders.approve', $order) }}"
                                                  onsubmit="return confirm('اعتماد الطلب #{{ $order->number }} وتفعيل المحتوى للطالب؟')">
                                                @csrf
                                                <button type="submit" class="btn-success btn-sm">قبول وتفعيل</button>
                                            </form>
                                            <button type="button" @click="rejecting = true" class="btn-danger btn-sm">رفض</button>
                                        </div>
                                        <form x-show="rejecting" x-cloak method="POST" action="{{ route('admin.orders.reject', $order) }}" class="flex w-56 flex-col gap-2">
                                            @csrf
                                            <input type="text" name="note" maxlength="500" class="input" placeholder="سبب الرفض (يظهر للطالب)" required>
                                            <div class="flex gap-2">
                                                <button type="submit" class="btn-danger btn-sm"
                                                        onclick="return confirm('تأكيد رفض الطلب؟')">تأكيد الرفض</button>
                                                <button type="button" @click="rejecting = false" class="btn-secondary btn-sm">إلغاء</button>
                                            </div>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $orders->links() }}</div>
    @endif
@endsection
