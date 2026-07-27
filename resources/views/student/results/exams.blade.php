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
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $attempts->links() }}</div>
    @endif
@endsection
