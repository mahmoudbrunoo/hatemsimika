@extends('layouts.admin')

@section('title', 'لوحة التحكم — الإدارة')
@section('page-title', 'لوحة التحكم')

@section('page')
    {{-- بطاقات الإحصائيات --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="stat-card">
            <span class="stat-value">{{ number_format($stats['students']) }}</span>
            <span class="stat-label">إجمالي الطلاب</span>
        </div>

        <div class="stat-card">
            <span class="stat-value">{{ number_format($stats['pending']) }}</span>
            <span class="stat-label">حسابات قيد المراجعة</span>
        </div>

        <div class="stat-card">
            <span class="stat-value">{{ egp($stats['revenue']) }}</span>
            <span class="stat-label">إجمالي الإيرادات</span>
        </div>

        <div class="stat-card">
            <span class="stat-value">{{ number_format($stats['pending_orders']) }}</span>
            <span class="stat-label">إيصالات دفع بانتظار المراجعة</span>
        </div>

        <div class="stat-card">
            <span class="stat-value">{{ number_format($stats['active_sessions']) }}</span>
            <span class="stat-label">جلسات نشطة الآن (آخر 15 دقيقة)</span>
        </div>

        <div class="stat-card">
            <span class="stat-value">{{ $stats['completion_rate'] }}<span class="text-base font-bold">%</span></span>
            <span class="stat-label">نسبة إكمال مشاهدة الفيديوهات</span>
        </div>

        <div class="stat-card">
            <span class="stat-value">{{ $stats['avg_score'] }}<span class="text-base font-bold">%</span></span>
            <span class="stat-label">متوسط درجات الامتحانات</span>
        </div>
    </div>

    {{-- روابط سريعة --}}
    <div class="mt-6 flex flex-wrap gap-3">
        <a href="{{ route('admin.users.index', ['status' => \App\Models\User::STATUS_PENDING]) }}" class="btn-secondary">
            مراجعة الحسابات الجديدة
            @if ($stats['pending'] > 0)
                <span class="badge-amber">{{ number_format($stats['pending']) }}</span>
            @endif
        </a>
        <a href="{{ route('admin.orders.index') }}" class="btn-secondary">
            مراجعة إيصالات الدفع
            @if ($stats['pending_orders'] > 0)
                <span class="badge-amber">{{ number_format($stats['pending_orders']) }}</span>
            @endif
        </a>
    </div>

    {{-- الرسوم البيانية --}}
    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <div class="card-pad">
            <h2 class="mb-4 font-extrabold text-slate-900 dark:text-white">إيرادات آخر 14 يوم</h2>
            <div class="h-64">
                <canvas data-chart="line" data-series='@json($revenueSeries)' data-label="الإيرادات (جنيه)" data-color="#800033"></canvas>
            </div>
        </div>

        <div class="card-pad">
            <h2 class="mb-4 font-extrabold text-slate-900 dark:text-white">تسجيلات الطلاب آخر 14 يوم</h2>
            <div class="h-64">
                <canvas data-chart="line" data-series='@json($signupSeries)' data-label="تسجيلات" data-color="#9c6a82"></canvas>
            </div>
        </div>
    </div>
@endsection
