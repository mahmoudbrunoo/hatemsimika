{{-- صفحة المحاضرة: فيديوهات ← ملفات ← واجب ← امتحان (بالترتيب الإجباري) + أسئلة الدرس --}}
@extends('layouts.student')

@section('title', $lecture->title . ' — ' . $course->title)

@section('page-title', $lecture->title)

@section('page')
    {{-- رجوع + حالة المحاضرة --}}
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('student.learn.course', $course) }}" class="btn-secondary btn-sm">← الرجوع لمحاضرات الكورس</a>
        @if ($progress->exam_passed)
            <span class="badge-green">✔ خلصت المحاضرة دي بنجاح</span>
        @elseif ($progress->homework_submitted)
            <span class="badge-amber">الواجب اتسلم — فاضل الامتحان</span>
        @endif
    </div>

    @if ($lecture->description)
        <div class="card-pad mb-6">
            <p class="whitespace-pre-line leading-8 text-slate-600 dark:text-slate-300">{{ $lecture->description }}</p>
        </div>
    @endif

    <div class="space-y-6">

        {{-- (أ) الفيديوهات --}}
        <section class="card-pad">
            <h2 class="mb-4 text-lg font-extrabold text-slate-900 dark:text-white">🎬 الفيديوهات</h2>

            <div class="space-y-3">
                @forelse ($videos as $video)
                    @php $watched = $views->get($video->id)?->completed; @endphp
                    <div class="flex flex-col gap-3 rounded-xl border border-slate-300/60 p-4 dark:border-slate-800 sm:flex-row sm:items-center">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="font-bold text-slate-900 dark:text-white">{{ $video->title }}</h3>
                                @if ($watched)
                                    <span class="badge-green">✔ اتفرجت عليه</span>
                                @endif
                            </div>
                            <p class="mt-1 text-xs font-bold text-slate-500 dark:text-slate-400">⏱️ المدة: <span dir="ltr">{{ $video->durationHuman() }}</span></p>
                        </div>
                        <a href="{{ route('student.learn.video', [$course, $video]) }}" class="btn-primary btn-sm shrink-0">
                            {{ $watched ? 'اتفرج تاني' : 'شاهد الفيديو' }}
                        </a>
                    </div>
                @empty
                    <p class="text-center text-sm text-slate-500 dark:text-slate-400">لسه مفيش فيديوهات في المحاضرة دي.</p>
                @endforelse
            </div>
        </section>

        {{-- (ب) الملفات والمذكرات --}}
        @if ($attachments->isNotEmpty())
            <section class="card-pad">
                <h2 class="mb-4 text-lg font-extrabold text-slate-900 dark:text-white">📄 الملفات والمذكرات</h2>

                <div class="space-y-3">
                    @foreach ($attachments as $attachment)
                        <div class="flex items-center gap-3 rounded-xl border border-slate-300/60 p-4 dark:border-slate-800">
                            <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-rose-50 text-lg dark:bg-rose-500/10">📕</span>
                            <div class="min-w-0 flex-1">
                                <h3 class="truncate font-bold text-slate-900 dark:text-white">{{ $attachment->title }}</h3>
                                <p class="text-xs font-bold uppercase text-slate-400">{{ $attachment->type }}</p>
                            </div>
                            <a href="{{ route('student.learn.attachment', [$course, $attachment]) }}" target="_blank" class="btn-secondary btn-sm shrink-0">تحميل</a>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- (ج) الواجب --}}
        @if ($assignment)
            <section class="card-pad">
                <h2 class="mb-4 text-lg font-extrabold text-slate-900 dark:text-white">📝 الواجب</h2>

                <div class="flex flex-col gap-3 rounded-xl border border-slate-300/60 p-4 dark:border-slate-800 sm:flex-row sm:items-center">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="font-bold text-slate-900 dark:text-white">{{ $assignment->title }}</h3>

                            @if ($submission)
                                @if ($submission->status === 'graded')
                                    <span class="badge-green">تم التصحيح — {{ rtrim(rtrim(number_format((float) $submission->score, 2), '0'), '.') }} / {{ $assignment->max_score }}</span>
                                @else
                                    <span class="badge-green">✔ تم التسليم</span>
                                    <span class="badge-amber">في انتظار التصحيح</span>
                                @endif
                            @endif
                        </div>

                        @if (! $submission)
                            <p class="mt-1 text-xs font-bold text-amber-600 dark:text-amber-400">لازم تسلم الواجب الأول عشان يفتح الامتحان.</p>
                        @elseif ($submission->status === 'graded' && $submission->feedback)
                            <p class="mt-1 text-xs font-semibold text-slate-500 dark:text-slate-400">ملاحظات المصحح: {{ $submission->feedback }}</p>
                        @endif
                    </div>

                    <a href="{{ route('student.homework.show', $assignment) }}"
                       class="{{ $submission ? 'btn-secondary' : 'btn-primary' }} btn-sm shrink-0">
                        {{ $submission ? 'شوف تسليمك' : 'حل الواجب' }}
                    </a>
                </div>
            </section>
        @endif

        {{-- (د) الامتحان --}}
        @if ($exam)
            <section class="card-pad {{ $examUnlocked ? '' : 'opacity-60' }}">
                <h2 class="mb-4 text-lg font-extrabold text-slate-900 dark:text-white">🏁 امتحان المحاضرة</h2>

                <div class="flex flex-col gap-3 rounded-xl border border-slate-300/60 p-4 dark:border-slate-800 sm:flex-row sm:items-center">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="font-bold text-slate-900 dark:text-white">{{ $exam->title }}</h3>
                            @if ($progress->exam_passed)
                                <span class="badge-green">✔ ناجح</span>
                            @endif
                            @unless ($examUnlocked)
                                <span class="badge-gray">🔒 مقفول</span>
                            @endunless
                        </div>
                        <p class="mt-1 text-xs font-bold text-slate-500 dark:text-slate-400">
                            ⏱️ {{ $exam->duration_minutes }} دقيقة — نسبة النجاح المطلوبة {{ $exam->passingPercent() }}%
                        </p>
                        @unless ($examUnlocked)
                            <p class="mt-1 text-xs font-bold text-amber-600 dark:text-amber-400">سلّم الواجب الأول عشان يفتح الامتحان</p>
                        @endunless
                    </div>

                    @if ($examUnlocked)
                        <a href="{{ route('student.exams.show', $exam) }}" class="btn-primary btn-sm shrink-0">ادخل الامتحان</a>
                    @else
                        <span class="btn-secondary btn-sm shrink-0 cursor-not-allowed opacity-60">🔒 ادخل الامتحان</span>
                    @endif
                </div>
            </section>
        @endif

        {{-- أسئلة الدرس --}}
        @php
            $qaThreads = $lecture->qaThreads()
                ->with(['user', 'replies.user'])
                ->where(fn ($q) => $q->where('status', \App\Models\QaThread::STATUS_APPROVED)->orWhere('user_id', auth()->id()))
                ->latest()
                ->take(20)
                ->get();
        @endphp

        <section id="qa" class="card-pad">
            <h2 class="mb-1 text-lg font-extrabold text-slate-900 dark:text-white">💬 اسأل عن الدرس</h2>
            <p class="mb-5 text-sm font-semibold text-slate-500 dark:text-slate-400">
                سؤالك بيظهر لباقي الطلاب بعد مراجعته — والموضوع بيتقفل بعد إجابة المدرس.
            </p>

            <div class="space-y-4">
                @forelse ($qaThreads as $thread)
                    <div class="rounded-xl border border-slate-300/60 p-4 dark:border-slate-800 {{ $thread->status === \App\Models\QaThread::STATUS_PENDING ? 'opacity-80' : '' }}">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-sm font-extrabold text-slate-900 dark:text-white">{{ $thread->user?->shortName() ?? 'طالب' }}</span>
                            <span class="text-xs font-semibold text-slate-400">{{ $thread->created_at->diffForHumans() }}</span>

                            @if ($thread->status === \App\Models\QaThread::STATUS_PENDING)
                                <span class="badge-amber">قيد المراجعة</span>
                            @elseif ($thread->status === \App\Models\QaThread::STATUS_REJECTED)
                                <span class="badge-red">مرفوض</span>
                            @elseif ($thread->answered_at || $thread->replies->where('is_official_answer', true)->isNotEmpty())
                                <span class="badge-green">تم الرد</span>
                            @endif
                        </div>

                        <p class="mt-2 whitespace-pre-line text-sm leading-7 text-slate-700 dark:text-slate-300">{{ $thread->body }}</p>

                        @if ($thread->image_path)
                            <img src="{{ $thread->image_path }}" alt="صورة السؤال" class="mt-3 max-h-64 rounded-xl border border-slate-300/60 dark:border-slate-800">
                        @endif

                        {{-- الردود --}}
                        @foreach ($thread->replies as $reply)
                            <div class="mt-3 rounded-xl {{ $reply->is_official_answer ? 'bg-brand-50 dark:bg-brand-500/10' : 'bg-slate-50 dark:bg-slate-800/50' }} p-3.5">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-xs font-extrabold text-slate-900 dark:text-white">{{ $reply->user?->shortName() ?? '—' }}</span>
                                    @if ($reply->is_official_answer)
                                        <span class="badge-sky">إجابة المدرس</span>
                                    @endif
                                </div>
                                <p class="mt-1.5 whitespace-pre-line text-sm leading-7 text-slate-700 dark:text-slate-300">{{ $reply->body }}</p>
                            </div>
                        @endforeach

                        @if ($thread->is_locked)
                            <p class="mt-3 text-xs font-bold text-slate-400">🔒 الموضوع مقفول بعد إجابة المدرس</p>
                        @endif
                    </div>
                @empty
                    <p class="rounded-xl border border-dashed border-slate-300 p-5 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400">
                        لسه مفيش أسئلة على الدرس ده — كن أول واحد يسأل!
                    </p>
                @endforelse
            </div>

            {{-- نموذج سؤال جديد --}}
            <form method="POST" action="{{ route('student.qa.store', $lecture) }}" enctype="multipart/form-data" class="mt-6 border-t border-slate-300/60 pt-5 dark:border-slate-800">
                @csrf

                <label for="qa-body" class="label">سؤالك</label>
                <textarea id="qa-body" name="body" rows="3" required minlength="5" maxlength="5000"
                          class="input" placeholder="اكتب سؤالك عن الدرس هنا...">{{ old('body') }}</textarea>
                @error('body') <p class="error">{{ $message }}</p> @enderror

                <label for="qa-image" class="label mt-4">صورة توضيحية (اختياري)</label>
                <input id="qa-image" type="file" name="image" accept="image/jpeg,image/png,image/webp"
                       class="input file:ml-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-1.5 file:text-xs file:font-bold file:text-brand-700 dark:file:bg-brand-500/10 dark:file:text-brand-300">
                @error('image') <p class="error">{{ $message }}</p> @enderror

                <button type="submit" class="btn-primary mt-4">إرسال السؤال</button>
            </form>
        </section>
    </div>
@endsection
