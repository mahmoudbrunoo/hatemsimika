@extends('layouts.student')

@section('title', 'لوحة التعلم — ' . setting('site.name', 'منصة حاتم سميكة'))
@section('page-title', 'أهلاً بيك يا ' . auth()->user()->shortName() . ' 👋')

@section('page')
    {{-- الإحصائيات السريعة --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="stat-card">
            <span class="stat-value">{{ $watchedVideos }} <span class="text-base font-bold text-slate-400">/ {{ $totalVideos }}</span></span>
            <span class="stat-label">فيديو خلصته من كورساتك</span>
        </div>
        <div class="stat-card">
            <span class="stat-value">{{ $examsTaken }} <span class="text-base font-bold text-slate-400">/ {{ $examsAvailable }}</span></span>
            <span class="stat-label">امتحان دخلته من المتاح</span>
        </div>
        <div class="stat-card">
            <span class="stat-value">{{ $avgPercent }}%</span>
            <span class="stat-label">متوسط درجاتك في الامتحانات</span>
        </div>
        <div class="stat-card">
            <span class="stat-value">{{ collect($chart)->sum('hours') }}</span>
            <span class="stat-label">ساعة مذاكرة آخر 7 أيام</span>
        </div>
    </div>

    {{-- نشاط المذاكرة الأسبوعي --}}
    <div class="card-pad mt-6">
        <div class="mb-4">
            <h2 class="text-lg font-extrabold text-slate-900 dark:text-white">نشاطك الأسبوعي</h2>
            <p class="mt-0.5 text-sm font-semibold text-slate-500 dark:text-slate-400">فيديوهاتك وكويزاتك وساعات مذاكرتك آخر 7 أيام</p>
        </div>
        <div class="h-72"><canvas data-chart="weekly" data-series='@json($chart)'></canvas></div>
    </div>

    {{-- الكورسات المميزة --}}
    <div class="mt-8">
        <div class="flex items-end justify-between gap-4">
            <div>
                <h2 class="text-lg font-extrabold text-slate-900 dark:text-white">الكورسات المميزة</h2>
                <p class="mt-0.5 text-sm font-semibold text-slate-500 dark:text-slate-400">
                    {{ $showAll ? 'بتعرض كورسات كل الصفوف' : 'مفلترة على ' . auth()->user()->yearLabel() }}
                </p>
            </div>
            @if ($showAll)
                <a href="{{ route('student.dashboard') }}" class="btn-secondary btn-sm shrink-0">عرض صفي فقط</a>
            @else
                <a href="{{ route('student.dashboard', ['all' => 1]) }}" class="btn-secondary btn-sm shrink-0">عرض كل الصفوف</a>
            @endif
        </div>

        @if ($featured->isEmpty())
            <div class="card-pad mt-4 text-center text-slate-500 dark:text-slate-400">
                مفيش كورسات متاحة حالياً — تابعنا قريباً!
            </div>
        @else
            <div class="mt-4 grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($featured as $course)
                    @include('partials.course-card', ['course' => $course])
                @endforeach
            </div>
        @endif
    </div>
@endsection
