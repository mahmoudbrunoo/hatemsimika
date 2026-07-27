@extends('layouts.student')

@section('title', 'الاشتراكات — ' . setting('site.name', 'منصة حاتم سميكة'))
@section('page-title', 'الاشتراكات')

@section('page')
    @if ($orders->isEmpty())
        <div class="card-pad text-center">
            <div class="mx-auto grid size-14 place-items-center rounded-2xl bg-brand-50 text-2xl dark:bg-brand-500/10">🗂️</div>
            <h2 class="mt-4 text-lg font-extrabold text-slate-900 dark:text-white">مفيش اشتراكات لحد دلوقتي</h2>
            <p class="mt-2 text-sm font-semibold text-slate-500 dark:text-slate-400">
                أول ما تشترك في كورس أو تشتري كتاب هتلاقي تفاصيل اشتراكك هنا.
            </p>
            <a href="{{ route('courses.index') }}" class="btn-primary mt-5">تصفح الكورسات</a>
        </div>
    @else
        @php
            // اشتراكات الطالب مفهرسة بالكورس عشان نعرض بداية ونهاية كل اشتراك
            $enrollmentsByCourse = auth()->user()->enrollments()->get()->keyBy('course_id');
        @endphp

        <div class="table-box">
            <table class="table">
                <thead>
                    <tr>
                        <th>رقم الطلب</th>
                        <th>الكورس / المنتج</th>
                        <th>بداية الاشتراك</th>
                        <th>نهاية الاشتراك</th>
                        <th>الحالة</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orders as $order)
                        @foreach ($order->items as $item)
                            @php
                                $isCourse = $item->purchasable instanceof \App\Models\Course;
                                $enrollment = $isCourse ? $enrollmentsByCourse->get($item->purchasable_id) : null;
                            @endphp
                            <tr>
                                <td class="font-semibold" dir="ltr">#{{ $order->number }}</td>
                                <td>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $item->title }}</span>
                                    <span class="block text-xs text-slate-400">{{ $isCourse ? 'كورس' : 'كتاب' }}</span>
                                </td>
                                <td>{{ ($order->paid_at ?? $order->created_at)->format('Y/m/d') }}</td>
                                <td>
                                    @if ($enrollment?->expires_at)
                                        {{ $enrollment->expires_at->format('Y/m/d') }}
                                    @elseif ($enrollment)
                                        <span class="text-slate-400">بدون نهاية</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    @if ($enrollment)
                                        @if ($enrollment->isActive())
                                            <span class="badge-green">ساري</span>
                                        @elseif ($enrollment->status === 'revoked')
                                            <span class="badge-red">ملغي</span>
                                        @else
                                            <span class="badge-gray">منتهي</span>
                                        @endif
                                    @else
                                        @php
                                            $orderBadge = match ($order->status) {
                                                \App\Models\Order::STATUS_PAID => 'badge-green',
                                                \App\Models\Order::STATUS_PENDING => 'badge-amber',
                                                \App\Models\Order::STATUS_REFUNDED => 'badge-gray',
                                                default => 'badge-red',
                                            };
                                        @endphp
                                        <span class="{{ $orderBadge }}">{{ $order->statusLabel() }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $orders->links() }}</div>
    @endif
@endsection
