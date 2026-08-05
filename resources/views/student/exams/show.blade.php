@extends('layouts.student')

@section('title', $exam->title . ' — ' . setting('site.name', 'منصة حاتم سميكة'))
@section('page-title', $exam->title)

@section('page')
    @php
        $typeBadges = [
            \App\Models\Exam::TYPE_QUIZ => 'badge-sky',
            \App\Models\Exam::TYPE_SHAMEL => 'badge-amber',
            \App\Models\Exam::TYPE_EVALUATION => 'badge-gray',
            \App\Models\Exam::TYPE_PERSONAL => 'badge-green',
        ];
        $usedAttempts = $attempts->count();
        $windowOpen = $exam->isWindowOpen();
        $canStart = $windowOpen && $usedAttempts < $exam->max_attempts;
    @endphp

    {{-- بطاقة تفاصيل الامتحان --}}
    <div class="card-pad">
        <div class="flex flex-wrap items-center gap-2">
            <span class="{{ $typeBadges[$exam->type] ?? 'badge-gray' }}">{{ $exam->typeLabel() }}</span>
            @if ($exam->hints_enabled)
                <span class="badge-amber">💡 التلميحات متاحة</span>
            @endif
            @unless ($windowOpen)
                <span class="badge-red">الامتحان غير متاح حالياً</span>
            @endunless
        </div>

        @if ($exam->description)
            <p class="mt-4 whitespace-pre-line text-sm font-semibold leading-7 text-slate-600 dark:text-slate-300">{{ $exam->description }}</p>
        @endif

        <div class="mt-5 grid grid-cols-2 gap-4 sm:grid-cols-4">
            <div class="stat-card">
                <span class="stat-value">{{ $questionsCount }}</span>
                <span class="stat-label">سؤال</span>
            </div>
            <div class="stat-card">
                <span class="stat-value">{{ $exam->duration_minutes }}</span>
                <span class="stat-label">دقيقة</span>
            </div>
            <div class="stat-card">
                <span class="stat-value" dir="ltr">{{ $exam->passingPercent() }}%</span>
                <span class="stat-label">نسبة النجاح</span>
            </div>
            <div class="stat-card">
                <span class="stat-value" dir="ltr">{{ $usedAttempts }} / {{ $exam->max_attempts }}</span>
                <span class="stat-label">المحاولات المستخدمة</span>
            </div>
        </div>

        @if ($exam->window_opens_at || $exam->window_closes_at)
            <div class="mt-4 rounded-xl border border-slate-300 bg-slate-50 p-4 text-sm font-semibold text-slate-600 dark:border-slate-700 dark:bg-slate-800/50 dark:text-slate-300">
                @if ($exam->window_opens_at)
                    <p>🕐 الامتحان يفتح: {{ $exam->window_opens_at->translatedFormat('d M Y — h:i a') }}</p>
                @endif
                @if ($exam->window_closes_at)
                    <p class="mt-1">🔒 الامتحان يقفل: {{ $exam->window_closes_at->translatedFormat('d M Y — h:i a') }}</p>
                @endif
            </div>
        @endif
    </div>

    {{-- تعليمات الامتحان --}}
    <div class="card-pad mt-6">
        <h2 class="text-lg font-extrabold text-slate-900 dark:text-white">📋 تعليمات مهمة قبل ما تبدأ</h2>
        <ul class="mt-3 list-inside list-disc space-y-2 text-sm font-semibold leading-6 text-slate-600 dark:text-slate-300">
            <li>المؤقت بيبدأ أول ما تدوس "ابدأ الامتحان" — وبيفضل شغال حتى لو قفلت الصفحة أو النت فصل.</li>
            <li>لو خرجت من الصفحة تقدر ترجع تكمل من مكانك طالما الوقت لسه موجود.</li>
            <li>لما الوقت يخلص، إجاباتك بتتسلم تلقائياً بالشكل اللي وصلت له.</li>
            <li>لا يمكن الرجوع أو تعديل الإجابات بعد التسليم — راجع كويس قبل ما تدوس تسليم.</li>
            <li>الأسئلة الاختيارية بتتصحح فوراً، والمقالية بيصححها المصحح وتظهر درجتها بعدين.</li>
            @if ($exam->answers_policy !== 'instant')
                <li>الإجابات النموذجية هتظهر بعد انتهاء فترة الامتحان — عشان العدل بين كل الطلاب.</li>
            @endif
        </ul>
    </div>

    {{-- محاولة جارية: استكمال أو إنهاء وتسليم --}}
    @if ($openAttempt)
        <div class="card-pad mt-6" x-data="{ confirmingFinalize: false }">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-extrabold text-slate-900 dark:text-white">⏳ عندك محاولة جارية</h2>
                    <p class="mt-2 text-sm font-semibold text-slate-600 dark:text-slate-300">
                        العداد شغال من ساعة ما بدأت ومش بيقف —
                        باقي من الزمن:
                        <span class="badge-amber" dir="ltr">{{ intdiv($openAttempt->remainingSeconds(), 60) }}:{{ str_pad($openAttempt->remainingSeconds() % 60, 2, '0', STR_PAD_LEFT) }}</span>
                    </p>
                    <p class="mt-1 text-xs font-medium text-slate-400 dark:text-slate-500">إجاباتك المحفوظة هترجع زي ما سيبتها بالظبط.</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <a href="{{ route('student.exams.take', $openAttempt) }}" class="btn-primary">استكمال الاختبار</a>
                    <button type="button" @click="confirmingFinalize = true" class="btn-danger">إنهاء وتسليم الاختبار</button>
                </div>
            </div>

            {{-- تأكيد التسليم النهائي بآخر إجابات محفوظة --}}
            <div x-show="confirmingFinalize" x-cloak @keydown.escape.window="confirmingFinalize = false"
                 class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/60" @click="confirmingFinalize = false"></div>
                <div class="relative w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 text-center shadow-xl dark:border-night-700 dark:bg-night-850"
                     role="alertdialog" aria-modal="true" aria-label="تأكيد تسليم الاختبار">
                    <div class="text-4xl">📤</div>
                    <h3 class="mt-3 text-lg font-extrabold text-slate-900 dark:text-white">إنهاء وتسليم الاختبار</h3>
                    <p class="mt-2 text-sm font-semibold leading-7 text-slate-600 dark:text-slate-300">
                        هيتم تسليم الاختبار وحساب نتيجتك فوراً بآخر إجابات محفوظة، ومش هتقدر تعدل بعدها. متأكد؟
                    </p>
                    <div class="mt-5 flex items-center justify-center gap-3">
                        <form method="POST" action="{{ route('student.exams.finalize', $openAttempt) }}">
                            @csrf
                            <button type="submit" class="btn-danger">تأكيد التسليم</button>
                        </form>
                        <button type="button" @click="confirmingFinalize = false" class="btn-secondary">إلغاء</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- بدء الامتحان --}}
    <div class="card-pad mt-6 text-center">
        @if ($openAttempt)
            <p class="text-sm font-bold text-slate-600 dark:text-slate-300">
                عندك محاولة جارية فوق — كمّلها أو سلّمها قبل ما تبدأ محاولة جديدة.
            </p>
        @elseif ($canStart)
            <form method="POST" action="{{ route('student.exams.start', $exam) }}">
                @csrf
                <button type="submit" class="btn-primary px-10 py-3 text-base">🚀 ابدأ الامتحان</button>
            </form>
            <p class="mt-3 text-xs font-medium text-slate-400 dark:text-slate-500">
                لو عندك محاولة جارية بالفعل، هترجع تكمل من مكانك.
            </p>
        @elseif (! $windowOpen)
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm font-bold text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300">
                الامتحان غير متاح حالياً — تابع مواعيد الفتح والقفل فوق.
            </div>
        @else
            <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm font-bold text-rose-700 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-300">
                استنفدت كل المحاولات المسموح بها في الامتحان ده.
            </div>
            @if ($attempts->isNotEmpty())
                <a href="{{ route('student.exams.result', $attempts->first()) }}" class="btn-secondary mt-4">شوف نتيجة آخر محاولة</a>
            @endif
        @endif
    </div>

    {{-- المحاولات السابقة --}}
    @if ($attempts->isNotEmpty())
        <div class="mt-6">
            <h2 class="mb-3 text-lg font-extrabold text-slate-900 dark:text-white">محاولاتك السابقة</h2>
            <div class="table-box">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>الدرجة</th>
                            <th>النسبة</th>
                            <th>الحالة</th>
                            <th>تاريخ التسليم</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($attempts as $attempt)
                            <tr>
                                <td class="font-bold">{{ $attempts->count() - $loop->index }}</td>
                                <td class="font-semibold" dir="ltr">{{ $attempt->score + 0 }} / {{ $attempt->total + 0 }}</td>
                                <td><span class="{{ $attempt->passed ? 'badge-green' : 'badge-red' }}" dir="ltr">{{ $attempt->percent + 0 }}%</span></td>
                                <td>
                                    @if ($attempt->passed)
                                        <span class="badge-green">ناجح</span>
                                    @else
                                        <span class="badge-red">لم يجتز</span>
                                    @endif
                                    @unless ($attempt->fully_graded)
                                        <span class="badge-amber mr-1">قيد تصحيح المقالي</span>
                                    @endunless
                                </td>
                                <td class="text-slate-500 dark:text-slate-400">{{ $attempt->submitted_at->translatedFormat('d M Y — h:i a') }}</td>
                                <td><a href="{{ route('student.exams.result', $attempt) }}" class="btn-secondary btn-sm">التفاصيل</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
