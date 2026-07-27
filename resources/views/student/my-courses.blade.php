@extends('layouts.student')

@section('title', 'كورساتي — ' . setting('site.name', 'منصة حاتم سميكة'))
@section('page-title', 'كورساتي')

@section('page')
    {{-- فلاتر الاشتراكات --}}
    <div class="mb-6 flex flex-wrap gap-2">
        @foreach (['all' => 'كل الاشتراكات', 'materials' => 'موادي (الترمات)', 'courses' => 'كورساتي (الشهري)'] as $key => $label)
            <a href="{{ route('student.courses', ['filter' => $key]) }}"
               class="{{ $filter === $key ? 'btn-primary' : 'btn-secondary' }} btn-sm">{{ $label }}</a>
        @endforeach
    </div>

    @if ($enrollments->isEmpty())
        <div class="card-pad text-center">
            <div class="mx-auto grid size-14 place-items-center rounded-2xl bg-brand-50 text-2xl dark:bg-brand-500/10">📚</div>
            <h2 class="mt-4 text-lg font-extrabold text-slate-900 dark:text-white">لسه مشتركتش في أي كورس</h2>
            <p class="mt-2 text-sm font-semibold text-slate-500 dark:text-slate-400">
                تصفح الكورسات المتاحة لصفك واشترك وابدأ مذاكرتك من النهاردة.
            </p>
            <a href="{{ route('courses.index') }}" class="btn-primary mt-5">تصفح الكورسات</a>
        </div>
    @else
        <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($enrollments as $enrollment)
                @php
                    $course = $enrollment->course;
                    $total = $course->totalVideos();
                    $done = auth()->user()->videoViews()
                        ->where('completed', true)
                        ->whereHas('video.lecture', fn ($q) => $q->where('course_id', $course->id))
                        ->count();
                    $percent = $total > 0 ? (int) round($done / $total * 100) : 0;
                @endphp

                <div class="card flex flex-col overflow-hidden">
                    <div class="relative aspect-video bg-slate-100 dark:bg-slate-800">
                        @if ($course->thumbnail_path)
                            <img src="{{ Storage::url($course->thumbnail_path) }}" alt="{{ $course->title }}" class="size-full object-cover">
                        @else
                            <div class="grid size-full place-items-center bg-gradient-to-br from-brand-500 to-brand-800 text-4xl font-black text-white/90">
                                {{ mb_substr($course->title, 0, 1) }}
                            </div>
                        @endif
                        <span class="absolute right-3 top-3 badge-sky">{{ $course->yearLabel() }}</span>
                    </div>

                    <div class="flex flex-1 flex-col gap-3 p-4">
                        <div>
                            <span class="badge-gray">{{ $course->categoryLabel() }}</span>
                            <h3 class="mt-2 font-extrabold text-slate-900 dark:text-white">{{ $course->title }}</h3>
                        </div>

                        {{-- شريط التقدم --}}
                        <div>
                            <div class="mb-1 flex items-center justify-between text-xs font-bold">
                                <span class="text-slate-500 dark:text-slate-400">خلصت {{ $done }} من {{ $total }} فيديو</span>
                                <span class="text-brand-600 dark:text-brand-400">{{ $percent }}%</span>
                            </div>
                            <div class="h-2 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                <div class="h-full rounded-full bg-brand-600" style="width: {{ $percent }}%"></div>
                            </div>
                        </div>

                        <div class="mt-auto flex items-center justify-between gap-3 border-t border-slate-100 pt-3 dark:border-slate-800">
                            <span class="text-xs font-bold text-slate-500 dark:text-slate-400">
                                @if ($enrollment->expires_at)
                                    ينتهي {{ $enrollment->expires_at->format('Y/m/d') }}
                                @else
                                    اشتراك دائم
                                @endif
                            </span>
                            <a href="{{ route('student.learn.course', $course) }}" class="btn-primary btn-sm">استمر في التعلم</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection
