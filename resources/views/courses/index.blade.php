@extends('layouts.app')

@section('title', 'الكورسات')

@section('content')
    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6">
        <h1 class="text-3xl font-black text-slate-900 dark:text-white">كل الكورسات</h1>

        {{-- فلاتر الصف والتصنيف --}}
        <div class="mt-6 flex flex-wrap items-center gap-2">
            <a href="{{ route('courses.index') }}" class="{{ $year === null ? 'btn-primary' : 'btn-secondary' }} btn-sm">كل الصفوف</a>
            @foreach (\App\Models\User::YEARS as $y => $label)
                <a href="{{ route('courses.index', ['year' => $y]) }}" class="{{ $year === $y ? 'btn-primary' : 'btn-secondary' }} btn-sm">{{ $label }}</a>
            @endforeach
        </div>

        @if ($year !== null)
            <div class="mt-3 flex flex-wrap items-center gap-2">
                <a href="{{ route('courses.index', ['year' => $year]) }}" class="{{ $category === null ? 'btn-primary' : 'btn-secondary' }} btn-sm">الكل</a>
                @foreach (\App\Models\Course::CATEGORIES_BY_YEAR[$year] ?? [] as $cat)
                    <a href="{{ route('courses.index', ['year' => $year, 'category' => $cat]) }}"
                       class="{{ $category === $cat ? 'btn-primary' : 'btn-secondary' }} btn-sm">
                        {{ \App\Models\Course::CATEGORIES[$cat] }}
                    </a>
                @endforeach
            </div>
        @endif

        @if ($courses->isEmpty())
            <div class="card-pad mt-8 text-center text-slate-500 dark:text-slate-400">لا توجد كورسات مطابقة للفلتر الحالي.</div>
        @else
            <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($courses as $course)
                    @include('partials.course-card', ['course' => $course])
                @endforeach
            </div>

            <div class="mt-8">{{ $courses->links() }}</div>
        @endif
    </section>
@endsection
