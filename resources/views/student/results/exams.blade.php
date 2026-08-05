@extends('layouts.student')

@section('title', $title . ' — ' . setting('site.name', 'منصة حاتم سميكة'))
@section('page-title', $title)

@section('page')
    @php
        $typeBadges = [
            \App\Models\Exam::TYPE_QUIZ => 'badge-sky',
            \App\Models\Exam::TYPE_SHAMEL => 'badge-amber',
            \App\Models\Exam::TYPE_EVALUATION => 'badge-gray',
            \App\Models\Exam::TYPE_PERSONAL => 'badge-green',
        ];
    @endphp

    @if ($attempts->isEmpty())
        <div class="card-pad text-center">
            <div class="mx-auto grid size-14 place-items-center rounded-2xl bg-brand-50 text-2xl dark:bg-brand-500/10">📝</div>
            <h2 class="mt-4 text-lg font-extrabold text-slate-900 dark:text-white">لسه مفيش نتائج</h2>
            <p class="mt-2 text-sm font-semibold text-slate-500 dark:text-slate-400">
                أول ما تحل امتحان هتلاقي نتيجتك هنا فوراً.
            </p>
            <a href="{{ route('student.courses') }}" class="btn-primary mt-5">روح لكورساتي</a>
        </div>
    @else
        <div x-data="{ finalizeUrl: null }">
            <div class="table-box">
                <table class="table">
                    <thead>
                        <tr>
                            <th>الامتحان</th>
                            <th>النوع</th>
                            <th>الدرجة</th>
                            <th>النسبة</th>
                            <th>تاريخ التسليم</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($attempts as $attempt)
                            @php $passing = $attempt->exam->passingPercent(); @endphp
                            <tr>
                                <td class="font-bold text-slate-900 dark:text-white">{{ $attempt->exam->title }}</td>
                                <td>
                                    <span class="{{ $typeBadges[$attempt->exam->type] ?? 'badge-gray' }}">{{ $attempt->exam->typeLabel() }}</span>
                                </td>

                                @if ($attempt->isSubmitted())
                                    <td class="font-semibold" dir="ltr">{{ $attempt->score + 0 }} / {{ $attempt->total + 0 }}</td>
                                    <td>
                                        <span class="{{ $attempt->percent >= $passing ? 'badge-green' : 'badge-red' }}" dir="ltr">{{ $attempt->percent + 0 }}%</span>
                                        @unless ($attempt->fully_graded)
                                            <span class="badge-amber mr-1">قيد تصحيح المقالي</span>
                                        @endunless
                                    </td>
                                    <td class="text-slate-500 dark:text-slate-400">{{ $attempt->submitted_at->translatedFormat('d M Y — h:i a') }}</td>
                                    <td>
                                        <a href="{{ route('student.exams.result', $attempt) }}" class="btn-secondary btn-sm">عرض النتيجة</a>
                                    </td>
                                @else
                                    {{-- محاولة جارية: العداد شغال من وقت البدء حتى لو الطالب برة الامتحان --}}
                                    <td class="font-semibold text-slate-400 dark:text-slate-500" dir="ltr">—</td>
                                    <td><span class="badge-amber">جاري الحل</span></td>
                                    <td class="text-slate-500 dark:text-slate-400">
                                        بدأت {{ $attempt->started_at->translatedFormat('d M Y — h:i a') }}
                                        <span class="badge-sky mr-1" dir="ltr">باقي {{ intdiv($attempt->remainingSeconds(), 60) }}:{{ str_pad($attempt->remainingSeconds() % 60, 2, '0', STR_PAD_LEFT) }}</span>
                                    </td>
                                    <td>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <a href="{{ route('student.exams.take', $attempt) }}" class="btn-primary btn-sm">استكمال الاختبار</a>
                                            <button type="button" @click="finalizeUrl = '{{ route('student.exams.finalize', $attempt) }}'"
                                                    class="btn-danger btn-sm">إنهاء وتسليم الاختبار</button>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $attempts->links() }}</div>

            {{-- تأكيد التسليم النهائي بآخر إجابات محفوظة --}}
            <div x-show="finalizeUrl" x-cloak @keydown.escape.window="finalizeUrl = null"
                 class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/60" @click="finalizeUrl = null"></div>
                <div class="relative w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 text-center shadow-xl dark:border-night-700 dark:bg-night-850"
                     role="alertdialog" aria-modal="true" aria-label="تأكيد تسليم الاختبار">
                    <div class="text-4xl">📤</div>
                    <h3 class="mt-3 text-lg font-extrabold text-slate-900 dark:text-white">إنهاء وتسليم الاختبار</h3>
                    <p class="mt-2 text-sm font-semibold leading-7 text-slate-600 dark:text-slate-300">
                        هيتم تسليم الاختبار وحساب نتيجتك فوراً بآخر إجابات محفوظة، ومش هتقدر تعدل بعدها. متأكد؟
                    </p>
                    <div class="mt-5 flex items-center justify-center gap-3">
                        <form method="POST" :action="finalizeUrl">
                            @csrf
                            <button type="submit" class="btn-danger">تأكيد التسليم</button>
                        </form>
                        <button type="button" @click="finalizeUrl = null" class="btn-secondary">إلغاء</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
