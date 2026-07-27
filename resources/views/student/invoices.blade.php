@extends('layouts.student')

@section('title', 'الفواتير — ' . setting('site.name', 'منصة حاتم سميكة'))
@section('page-title', 'الفواتير')

@section('page')
    @if ($orders->isEmpty())
        <div class="card-pad text-center">
            <div class="mx-auto grid size-14 place-items-center rounded-2xl bg-brand-50 text-2xl dark:bg-brand-500/10">🧾</div>
            <h2 class="mt-4 text-lg font-extrabold text-slate-900 dark:text-white">مفيش فواتير لحد دلوقتي</h2>
            <p class="mt-2 text-sm font-semibold text-slate-500 dark:text-slate-400">
                كل عمليات الشراء بتاعتك هتتسجل هنا بحالتها وتفاصيلها.
            </p>
            <a href="{{ route('courses.index') }}" class="btn-primary mt-5">تصفح الكورسات</a>
        </div>
    @else
        <div class="table-box">
            <table class="table">
                <thead>
                    <tr>
                        <th>رقم الفاتورة</th>
                        <th>التاريخ</th>
                        <th>المحتوى</th>
                        <th>الإجمالي</th>
                        <th>طريقة الدفع</th>
                        <th>الحالة</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orders as $order)
                        <tr>
                            <td class="font-semibold" dir="ltr">#{{ $order->number }}</td>
                            <td>
                                <span class="font-semibold">{{ $order->created_at->format('Y/m/d') }}</span>
                                <span class="block text-xs text-slate-400">{{ $order->created_at->diffForHumans() }}</span>
                            </td>
                            <td class="max-w-64">
                                <span class="block truncate font-semibold text-slate-800 dark:text-slate-200">
                                    {{ $order->items->pluck('title')->join('، ') ?: '—' }}
                                </span>
                                @if ($order->coupon)
                                    <span class="text-xs text-emerald-600 dark:text-emerald-400">كوبون: {{ $order->coupon->code }} (خصم {{ egp($order->discount) }})</span>
                                @endif
                            </td>
                            <td class="font-bold text-slate-900 dark:text-white">{{ egp($order->total) }}</td>
                            <td class="text-slate-500 dark:text-slate-400">{{ $order->paymentMethodLabel() }}</td>
                            <td>
                                @php
                                    $badge = match ($order->status) {
                                        \App\Models\Order::STATUS_PAID => 'badge-green',
                                        \App\Models\Order::STATUS_PENDING => 'badge-amber',
                                        \App\Models\Order::STATUS_REFUNDED => 'badge-gray',
                                        default => 'badge-red', // FAILED | REJECTED
                                    };
                                @endphp
                                <span class="{{ $badge }}">{{ $order->statusLabel() }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $orders->links() }}</div>
    @endif
@endsection
