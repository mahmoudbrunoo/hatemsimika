@extends('layouts.student')

@section('title', 'بيانات الدخول — ' . setting('site.name', 'منصة حاتم سميكة'))
@section('page-title', 'الآمان و تاريخ تسجيل الدخول')

@section('page')
    {{-- ملخص سريع + سياسة الجلسة الواحدة --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div class="stat-card">
            <span class="stat-value">{{ $logoutsToday }}</span>
            <span class="stat-label">مرات الخروج النهاردة</span>
        </div>
        <div class="stat-card">
            <span class="stat-value">{{ $logoutsWeek }}</span>
            <span class="stat-label">مرات الخروج الأسبوع ده</span>
        </div>
        <div class="card-pad flex items-center gap-3 sm:col-span-2 lg:col-span-1">
            <span class="text-2xl">🔒</span>
            <p class="text-sm font-semibold leading-6 text-slate-600 dark:text-slate-300">
                تسجيل الدخول من جهاز جديد يغلق الجلسة القديمة تلقائياً — حسابك بيشتغل على جهاز واحد بس في نفس الوقت.
            </p>
        </div>
    </div>

    {{-- سجل الأجهزة --}}
    <div class="mt-6">
        <h2 class="mb-3 text-lg font-extrabold text-slate-900 dark:text-white">سجل الأجهزة اللي دخلت منها</h2>

        @if ($activities->isEmpty())
            <div class="card-pad text-center text-slate-500 dark:text-slate-400">
                لسه مفيش عمليات دخول متسجلة على حسابك.
            </div>
        @else
            <div class="table-box">
                <table class="table">
                    <thead>
                        <tr>
                            <th>الجهاز</th>
                            <th>المتصفح / النظام</th>
                            <th>عنوان IP</th>
                            <th>وقت الدخول</th>
                            <th>آخر نشاط</th>
                            <th>الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($activities as $activity)
                            @php
                                $isCurrent = $activity->logged_out_at === null
                                    && $activity->session_id === session()->getId();
                                $reasonLabel = match ($activity->logout_reason) {
                                    'manual' => 'خروج يدوي',
                                    'new_device' => 'دخول من جهاز جديد',
                                    'admin' => 'بواسطة الإدارة',
                                    default => null,
                                };
                            @endphp
                            <tr>
                                <td>
                                    <span class="font-semibold text-slate-800 dark:text-slate-200">{{ $activity->device_type ?: '—' }}</span>
                                    @if ($activity->device_name)
                                        <span class="block text-xs text-slate-400">{{ $activity->device_name }}</span>
                                    @endif
                                </td>
                                <td class="text-slate-500 dark:text-slate-400">
                                    {{ $activity->browser ?: '—' }}
                                    @if ($activity->os)
                                        <span class="block text-xs text-slate-400">{{ $activity->os }}</span>
                                    @endif
                                </td>
                                <td dir="ltr" class="font-semibold text-slate-500 dark:text-slate-400">{{ $activity->ip ?: '—' }}</td>
                                <td>
                                    @if ($activity->logged_in_at)
                                        <span class="font-semibold">{{ $activity->logged_in_at->format('Y/m/d') }}</span>
                                        <span class="block text-xs text-slate-400">{{ $activity->logged_in_at->format('h:i A') }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="text-slate-500 dark:text-slate-400">
                                    {{ $activity->last_activity_at?->diffForHumans() ?? '—' }}
                                </td>
                                <td>
                                    @if ($isCurrent)
                                        <span class="badge-green">الجلسة الحالية</span>
                                    @elseif ($activity->logged_out_at === null)
                                        <span class="badge-sky">مفتوحة</span>
                                    @else
                                        <span class="badge-gray">خرجت {{ $activity->logged_out_at->diffForHumans() }}</span>
                                        @if ($reasonLabel)
                                            <span class="mt-1 block text-xs text-slate-400">{{ $reasonLabel }}</span>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $activities->links() }}</div>
        @endif
    </div>
@endsection
