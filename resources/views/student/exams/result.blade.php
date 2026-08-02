@extends('layouts.student')

@section('title', 'نتيجة ' . $exam->title . ' — ' . setting('site.name', 'منصة حاتم سميكة'))
@section('page-title', 'نتيجة: ' . $exam->title)

@section('page')
    {{-- ملخص النتيجة --}}
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <div class="stat-card">
            <span class="stat-value" dir="ltr">{{ $attempt->score + 0 }} / {{ $attempt->total + 0 }}</span>
            <span class="stat-label">الدرجة</span>
        </div>
        <div class="stat-card">
            <span class="stat-value" dir="ltr">{{ $attempt->percent + 0 }}%</span>
            <span class="stat-label">النسبة المئوية</span>
        </div>
        <div class="stat-card">
            <span>
                @if ($attempt->passed)
                    <span class="badge-green text-sm">ناجح 🎉</span>
                @else
                    <span class="badge-red text-sm">لم تجتز</span>
                @endif
            </span>
            <span class="stat-label">الحالة — نسبة النجاح {{ $exam->passingPercent() }}%</span>
        </div>
        <div class="stat-card">
            <span class="stat-value text-base">{{ $attempt->submitted_at->translatedFormat('d M Y') }}</span>
            <span class="stat-label">سلمت الساعة {{ $attempt->submitted_at->translatedFormat('h:i a') }}</span>
        </div>
    </div>

    {{-- المقالي لسه بيتصحح --}}
    @unless ($attempt->fully_graded)
        <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-bold text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300">
            ⏳ في أسئلة مقالية لسه بتتصحح — درجتك النهائية هتتحدث أول ما التصحيح يخلص.
        </div>
    @endunless

    @if (! $showAnswers)
        {{-- سياسة إخفاء الإجابات النموذجية لحد ما نافذة الامتحان تقفل --}}
        <div class="card-pad mt-6 text-center">
            <div class="mx-auto grid size-14 place-items-center rounded-2xl bg-amber-50 text-2xl dark:bg-amber-500/10">🔒</div>
            <h2 class="mt-4 text-lg font-extrabold text-slate-900 dark:text-white">النتيجة ظهرت — والإجابات النموذجية مستنية شوية</h2>
            <p class="mt-2 text-sm font-semibold leading-6 text-slate-500 dark:text-slate-400">
                عشان العدل بين كل الطلاب، الإجابات النموذجية ومراجعة الأسئلة هتظهر بعد انتهاء فترة الامتحان.
                @if ($exam->window_closes_at)
                    <br>ارجع تاني بعد: {{ $exam->window_closes_at->translatedFormat('d M Y — h:i a') }}
                @endif
            </p>
        </div>
    @else
        {{-- مراجعة الأسئلة سؤال بسؤال --}}
        <h2 class="mb-3 mt-8 text-lg font-extrabold text-slate-900 dark:text-white">مراجعة إجاباتك</h2>

        <div class="space-y-4">
            @foreach ($answers as $answer)
                @continue($answer->question === null)
                @php
                    $question = $answer->question;
                    $correctOption = $question->correctOption();
                @endphp

                <div class="card-pad">
                    {{-- رأس السؤال --}}
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="grid size-8 shrink-0 place-items-center rounded-xl bg-slate-800 text-sm font-black text-white dark:bg-slate-700">{{ $loop->iteration }}</span>
                        @if ($answer->is_correct === true)
                            <span class="badge-green">إجابة صحيحة ✔</span>
                        @elseif ($answer->is_correct === false)
                            <span class="badge-red">إجابة خاطئة ✘</span>
                        @else
                            <span class="badge-amber">قيد التصحيح</span>
                        @endif
                        <span class="badge-gray mr-auto" dir="ltr">{{ $answer->points_awarded + 0 }} / {{ $question->points + 0 }}</span>
                    </div>

                    {{-- نص السؤال والوسائط --}}
                    <p class="mt-4 whitespace-pre-line font-bold leading-8 text-slate-900 dark:text-white">{{ $question->body }}</p>

                    @if ($question->image_path)
                        <img src="{{ Storage::url($question->image_path) }}" alt="صورة السؤال"
                             class="mt-3 max-h-80 rounded-xl border border-slate-300 object-contain dark:border-slate-700">
                    @endif

                    @if ($question->audio_path)
                        <audio controls class="mt-3 w-full" src="{{ Storage::url($question->audio_path) }}"></audio>
                    @endif

                    @if ($question->isMcq())
                        {{-- الاختيارات: الصحيح أخضر واختيارك الغلط أحمر --}}
                        <div class="mt-4 space-y-2">
                            @foreach ($question->options as $option)
                                @php
                                    $isChosen = $answer->question_option_id === $option->id;
                                    $classes = 'border-slate-300 dark:border-slate-700';
                                    if ($option->is_correct) {
                                        $classes = 'border-emerald-300 bg-emerald-50 dark:border-emerald-500/40 dark:bg-emerald-500/10';
                                    } elseif ($isChosen) {
                                        $classes = 'border-rose-300 bg-rose-50 dark:border-rose-500/40 dark:bg-rose-500/10';
                                    }
                                @endphp
                                <div class="flex items-start gap-3 rounded-xl border px-3.5 py-2.5 text-sm {{ $classes }}">
                                    <div class="min-w-0 flex-1">
                                        @if ($option->body)
                                            <p class="font-semibold leading-7 text-slate-800 dark:text-slate-200">{{ $option->body }}</p>
                                        @endif
                                        @if ($option->image_path)
                                            <img src="{{ Storage::url($option->image_path) }}" alt="" class="mt-2 max-h-40 rounded-lg object-contain">
                                        @endif
                                        @if ($option->audio_path)
                                            <audio controls class="mt-2 w-full" src="{{ Storage::url($option->audio_path) }}"></audio>
                                        @endif
                                    </div>
                                    <div class="flex shrink-0 flex-col items-end gap-1">
                                        @if ($option->is_correct)
                                            <span class="badge-green">الإجابة الصحيحة</span>
                                        @endif
                                        @if ($isChosen)
                                            <span class="{{ $option->is_correct ? 'badge-green' : 'badge-red' }}">إجابتك</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if ($answer->question_option_id === null)
                            <p class="mt-3 rounded-xl bg-slate-50 px-3.5 py-2.5 text-sm font-bold text-slate-500 dark:bg-slate-800/50 dark:text-slate-400">
                                سبت السؤال ده من غير إجابة.
                            </p>
                        @endif
                    @else
                        {{-- إجابة السؤال المقالي --}}
                        <div class="mt-4 rounded-xl border border-slate-300 bg-slate-50 p-4 dark:border-slate-700 dark:bg-slate-800/50">
                            <p class="mb-2 text-xs font-bold text-slate-400 dark:text-slate-500">إجابتك:</p>
                            @if ($answer->essay_text)
                                <p class="whitespace-pre-line text-sm font-semibold leading-7 text-slate-700 dark:text-slate-200">{{ $answer->essay_text }}</p>
                            @endif
                            @if ($answer->essay_image)
                                <a href="{{ Storage::url($answer->essay_image) }}" target="_blank" rel="noopener">
                                    <img src="{{ Storage::url($answer->essay_image) }}" alt="صورة حلك"
                                         class="mt-3 max-h-72 rounded-lg border border-slate-300 object-contain dark:border-slate-700">
                                </a>
                            @endif
                            @if (! $answer->essay_text && ! $answer->essay_image)
                                <p class="text-sm font-bold text-slate-500 dark:text-slate-400">سبت السؤال ده من غير إجابة.</p>
                            @endif
                        </div>
                    @endif

                    {{-- شرح الإجابة --}}
                    @if ($question->explanation || $question->explanation_image || $question->explanation_video_url)
                        <div class="mt-4 rounded-xl border border-brand-200 bg-brand-50 p-4 text-sm dark:border-brand-500/30 dark:bg-brand-500/10">
                            <p class="mb-2 text-xs font-black text-brand-700 dark:text-brand-300">💡 شرح الإجابة</p>
                            @if ($question->explanation)
                                <p class="whitespace-pre-line font-semibold leading-7 text-slate-700 dark:text-slate-200">{{ $question->explanation }}</p>
                            @endif
                            @if ($question->explanation_image)
                                <img src="{{ Storage::url($question->explanation_image) }}" alt="شرح بالصورة" class="mt-3 max-h-72 rounded-lg object-contain">
                            @endif
                            @if ($question->explanation_video_url)
                                <a href="{{ $question->explanation_video_url }}" target="_blank" rel="noopener" class="btn-secondary btn-sm mt-3">🎬 اتفرج على فيديو الشرح</a>
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    {{-- روابط سريعة --}}
    <div class="mt-6 flex flex-wrap justify-center gap-3">
        <a href="{{ route('student.results.exams') }}" class="btn-secondary">كل نتائجي</a>
        @if (! $attempt->passed && $exam->type !== \App\Models\Exam::TYPE_PERSONAL)
            <a href="{{ route('student.exams.show', $exam) }}" class="btn-primary">جرب تاني 💪</a>
        @endif
        <a href="{{ route('student.personal') }}" class="btn-secondary">اختبر أخطاءك 🎯</a>
    </div>
@endsection
