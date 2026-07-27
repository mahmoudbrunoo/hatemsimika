{{-- صفحة التعلم: محاضرات الكورس بحالات الفتح/القفل --}}
@extends('layouts.student')

@section('title', $course->title . ' — التعلم')

@section('page-title', 'التعلم — ' . $course->title)

@section('page')
    @php
        $totalLectures = $lectures->count();
        $completedLectures = $lectures->filter(fn ($l) => $l->progress->exam_passed)->count();
        $progressPercent = $totalLectures > 0 ? (int) round($completedLectures / $totalLectures * 100) : 0;
    @endphp

    {{-- بطاقة الكورس --}}
    <div class="card overflow-hidden">
        <div class="flex flex-col sm:flex-row">
            <div class="aspect-video w-full shrink-0 bg-slate-100 dark:bg-slate-800 sm:w-64">
                @if ($course->thumbnail_path)
                    <img src="{{ Storage::url($course->thumbnail_path) }}" alt="{{ $course->title }}" class="size-full object-cover">
                @else
                    <div class="grid size-full place-items-center bg-gradient-to-br from-brand-500 to-brand-800 text-5xl font-black text-white/90">
                        {{ mb_substr($course->title, 0, 1) }}
                    </div>
                @endif
            </div>

            <div class="flex flex-1 flex-col justify-center gap-3 p-5 sm:p-6">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="badge-sky">{{ $course->yearLabel() }}</span>
                    <span class="badge-gray">{{ $course->categoryLabel() }}</span>
                </div>

                <h2 class="text-xl font-black text-slate-900 dark:text-white">{{ $course->title }}</h2>

                @if ($course->description)
                    <p class="line-clamp-2 text-sm leading-7 text-slate-500 dark:text-slate-400">{{ $course->description }}</p>
                @endif

                {{-- شريط التقدم --}}
                <div>
                    <div class="mb-1.5 flex items-center justify-between text-xs font-bold text-slate-500 dark:text-slate-400">
                        <span>خلصت {{ $completedLectures }} من {{ $totalLectures }} محاضرة</span>
                        <span>{{ $progressPercent }}%</span>
                    </div>
                    <div class="h-2.5 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                        <div class="h-full rounded-full bg-brand-600 transition-all" style="width: {{ $progressPercent }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- قائمة المحاضرات بالترتيب --}}
    <h2 class="mb-4 mt-8 text-lg font-extrabold text-slate-900 dark:text-white">المحاضرات</h2>

    <div class="space-y-3">
        @forelse ($lectures as $lecture)
            @php
                $videosCount = $lecture->videos->count();
                $attachmentsCount = $lecture->attachments->count();
                $hasAssignment = $lecture->assignment()->where('is_published', true)->exists();
                $hasExam = $lecture->exam()->where('is_published', true)->exists();
                $requiredPercent = $lecture->previous()?->passingPercent() ?? $course->passing_percent ?? 60;
            @endphp

            @if ($lecture->unlocked)
                {{-- محاضرة مفتوحة --}}
                <div class="card flex flex-col gap-4 p-4 sm:flex-row sm:items-center sm:p-5">
                    <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-brand-50 font-black text-brand-600 dark:bg-brand-500/10 dark:text-brand-300">
                        {{ $loop->iteration }}
                    </span>

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="font-extrabold text-slate-900 dark:text-white">{{ $lecture->title }}</h3>
                            @if ($lecture->progress->exam_passed)
                                <span class="badge-green">✔ مكتملة</span>
                            @elseif ($lecture->progress->homework_submitted)
                                <span class="badge-amber">الواجب اتسلم — فاضل الامتحان</span>
                            @endif
                            @if ($lecture->is_free_preview)
                                <span class="badge-sky">معاينة مجانية</span>
                            @endif
                        </div>

                        @if ($lecture->description)
                            <p class="mt-1 line-clamp-1 text-sm text-slate-500 dark:text-slate-400">{{ $lecture->description }}</p>
                        @endif

                        <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs font-bold text-slate-500 dark:text-slate-400">
                            <span>🎬 {{ $videosCount }} فيديو</span>
                            @if ($attachmentsCount > 0)
                                <span>📄 {{ $attachmentsCount }} ملف</span>
                            @endif
                            @if ($hasAssignment)
                                <span>📝 واجب</span>
                            @endif
                            @if ($hasExam)
                                <span>🏁 امتحان</span>
                            @endif
                        </div>
                    </div>

                    <a href="{{ route('student.learn.lecture', [$course, $lecture]) }}" class="btn-primary shrink-0">ادخل المحاضرة</a>
                </div>
            @else
                {{-- محاضرة مقفولة --}}
                <div class="card flex flex-col gap-4 p-4 opacity-60 sm:flex-row sm:items-center sm:p-5">
                    <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-slate-100 font-black text-slate-400 dark:bg-slate-800">
                        {{ $loop->iteration }}
                    </span>

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="font-extrabold text-slate-700 dark:text-slate-300">{{ $lecture->title }}</h3>
                            <span class="badge-gray">🔒 مقفولة</span>
                        </div>

                        <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs font-bold text-slate-400 dark:text-slate-500">
                            <span>🎬 {{ $videosCount }} فيديو</span>
                            @if ($attachmentsCount > 0)
                                <span>📄 {{ $attachmentsCount }} ملف</span>
                            @endif
                            @if ($hasAssignment)
                                <span>📝 واجب</span>
                            @endif
                            @if ($hasExam)
                                <span>🏁 امتحان</span>
                            @endif
                        </div>

                        <p class="mt-2 text-xs font-bold text-amber-600 dark:text-amber-400">
                            اجتاز امتحان المحاضرة السابقة بنسبة {{ $requiredPercent }}% الأول
                        </p>
                    </div>
                </div>
            @endif
        @empty
            <div class="card-pad text-center text-slate-500 dark:text-slate-400">
                لسه مفيش محاضرات متاحة في الكورس ده — تابعنا قريباً!
            </div>
        @endforelse
    </div>
@endsection
