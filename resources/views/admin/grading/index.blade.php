@extends('layouts.admin')

@section('title', 'التصحيح — لوحة التحكم')
@section('page-title', 'التصحيح')

@section('page')
    <div class="space-y-8">

        {{-- (أ) واجبات في انتظار التصحيح --}}
        <section>
            <div class="mb-4 flex items-center gap-3">
                <h2 class="text-lg font-extrabold text-slate-900 dark:text-white">واجبات في انتظار التصحيح</h2>
                <span class="badge-amber">{{ $homework->total() }}</span>
            </div>

            @if ($homework->isEmpty())
                <div class="card-pad text-center text-slate-500 dark:text-slate-400">مفيش حاجة مستنية تصحيح 🎉</div>
            @else
                <div class="space-y-4">
                    @foreach ($homework as $submission)
                        <div class="card-pad">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <p class="font-extrabold text-slate-900 dark:text-white">{{ $submission->user->name }}</p>
                                    <p class="mt-0.5 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                        {{ $submission->assignment->lecture->course->title ?? '' }}
                                        — {{ $submission->assignment->lecture->title ?? '' }}
                                        · واجب: {{ $submission->assignment->title }}
                                    </p>
                                </div>
                                <div class="text-left">
                                    <span class="badge-amber">في انتظار التصحيح</span>
                                    <p class="mt-1 text-xs font-semibold text-slate-400">سُلّم في {{ $submission->created_at->format('Y/m/d H:i') }}</p>
                                </div>
                            </div>

                            {{-- إجابة الطالب --}}
                            <div class="mt-4 grid gap-4 lg:grid-cols-2">
                                <div class="rounded-xl bg-slate-50 p-4 dark:bg-slate-950/50">
                                    <p class="mb-2 text-xs font-bold text-slate-400">إجابة الطالب</p>
                                    @if ($submission->answer_text)
                                        <p class="whitespace-pre-line text-sm leading-7 text-slate-700 dark:text-slate-300">{{ $submission->answer_text }}</p>
                                    @endif
                                    @if ($submission->file_path)
                                        <a href="{{ \Illuminate\Support\Facades\Storage::url($submission->file_path) }}" target="_blank" class="mt-2 inline-block">
                                            <img src="{{ \Illuminate\Support\Facades\Storage::url($submission->file_path) }}"
                                                 alt="حل الواجب" class="max-h-48 rounded-xl border border-slate-200 object-cover dark:border-slate-700">
                                            <span class="mt-1 block text-xs font-bold text-brand-600 dark:text-brand-400">اضغط لفتح الصورة بالحجم الكامل</span>
                                        </a>
                                    @endif
                                    @if (! $submission->answer_text && ! $submission->file_path)
                                        <p class="text-sm text-slate-400">لا توجد إجابة مرفقة.</p>
                                    @endif
                                </div>

                                {{-- نموذج رصد الدرجة --}}
                                <form method="POST" action="{{ route('admin.grading.homework', $submission) }}" class="grid content-start gap-3">
                                    @csrf
                                    <div>
                                        <label for="score-{{ $submission->id }}" class="label">
                                            الدرجة (من {{ $submission->assignment->max_score }})
                                        </label>
                                        <input id="score-{{ $submission->id }}" name="score" type="number" dir="ltr"
                                               min="0" max="{{ $submission->assignment->max_score }}" step="0.5"
                                               value="{{ old('score') }}" class="input" required>
                                    </div>
                                    <div>
                                        <label for="feedback-{{ $submission->id }}" class="label">ملاحظات للطالب (اختياري)</label>
                                        <textarea id="feedback-{{ $submission->id }}" name="feedback" rows="2" maxlength="1000"
                                                  class="input" placeholder="مثال: حل ممتاز — راجع السؤال الأخير">{{ old('feedback') }}</textarea>
                                    </div>
                                    <button type="submit" class="btn-success">اعتماد الدرجة</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4">{{ $homework->appends(request()->except('hw_page'))->links() }}</div>
            @endif
        </section>

        {{-- (ب) أسئلة مقالية في انتظار التصحيح --}}
        <section>
            <div class="mb-4 flex items-center gap-3">
                <h2 class="text-lg font-extrabold text-slate-900 dark:text-white">أسئلة مقالية في انتظار التصحيح</h2>
                <span class="badge-amber">{{ $essays->total() }}</span>
            </div>

            @if ($essays->isEmpty())
                <div class="card-pad text-center text-slate-500 dark:text-slate-400">مفيش حاجة مستنية تصحيح 🎉</div>
            @else
                <div class="space-y-4">
                    @foreach ($essays as $answer)
                        <div class="card-pad">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <p class="font-extrabold text-slate-900 dark:text-white">{{ $answer->attempt->user->name }}</p>
                                    <p class="mt-0.5 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                        امتحان: {{ $answer->attempt->exam->title }}
                                    </p>
                                </div>
                                <p class="text-xs font-semibold text-slate-400">سُلّم في {{ $answer->attempt->submitted_at?->format('Y/m/d H:i') }}</p>
                            </div>

                            <div class="mt-4 rounded-xl bg-brand-50/60 p-4 dark:bg-brand-500/5">
                                <p class="mb-1 text-xs font-bold text-slate-400">نص السؤال ({{ rtrim(rtrim(number_format((float) $answer->question->points, 2), '0'), '.') }} درجة)</p>
                                <p class="text-sm font-semibold leading-7 text-slate-800 dark:text-slate-200">{{ $answer->question->body }}</p>
                            </div>

                            <div class="mt-3 grid gap-4 lg:grid-cols-2">
                                <div class="rounded-xl bg-slate-50 p-4 dark:bg-slate-950/50">
                                    <p class="mb-2 text-xs font-bold text-slate-400">إجابة الطالب</p>
                                    @if ($answer->essay_text)
                                        <p class="whitespace-pre-line text-sm leading-7 text-slate-700 dark:text-slate-300">{{ $answer->essay_text }}</p>
                                    @endif
                                    @if ($answer->essay_image)
                                        <a href="{{ \Illuminate\Support\Facades\Storage::url($answer->essay_image) }}" target="_blank" class="mt-2 inline-block">
                                            <img src="{{ \Illuminate\Support\Facades\Storage::url($answer->essay_image) }}"
                                                 alt="إجابة الطالب" class="max-h-48 rounded-xl border border-slate-200 object-cover dark:border-slate-700">
                                            <span class="mt-1 block text-xs font-bold text-brand-600 dark:text-brand-400">اضغط لفتح الصورة بالحجم الكامل</span>
                                        </a>
                                    @endif
                                    @if (! $answer->essay_text && ! $answer->essay_image)
                                        <p class="text-sm text-slate-400">الطالب سلّم من غير إجابة.</p>
                                    @endif
                                </div>

                                <form method="POST" action="{{ route('admin.grading.essay', $answer) }}" class="grid content-start gap-3">
                                    @csrf
                                    <div>
                                        <label for="points-{{ $answer->id }}" class="label">
                                            الدرجة (من {{ rtrim(rtrim(number_format((float) $answer->question->points, 2), '0'), '.') }})
                                        </label>
                                        <input id="points-{{ $answer->id }}" name="points" type="number" dir="ltr"
                                               min="0" max="{{ (float) $answer->question->points }}" step="0.25"
                                               value="{{ old('points') }}" class="input" required>
                                        <p class="mt-1 text-xs font-medium text-slate-400">نتيجة المحاولة هتتحسب تلقائياً بعد الاعتماد.</p>
                                    </div>
                                    <button type="submit" class="btn-success">اعتماد الدرجة</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4">{{ $essays->appends(request()->except('essay_page'))->links() }}</div>
            @endif
        </section>
    </div>
@endsection
