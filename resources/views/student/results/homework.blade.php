@extends('layouts.student')

@section('title', 'نتائج الواجبات — ' . setting('site.name', 'منصة حاتم سميكة'))
@section('page-title', 'نتائج الواجب')

@section('page')
    @if ($submissions->isEmpty())
        <div class="card-pad text-center">
            <div class="mx-auto grid size-14 place-items-center rounded-2xl bg-brand-50 text-2xl dark:bg-brand-500/10">📒</div>
            <h2 class="mt-4 text-lg font-extrabold text-slate-900 dark:text-white">لسه مسلمتش أي واجب</h2>
            <p class="mt-2 text-sm font-semibold text-slate-500 dark:text-slate-400">
                حل واجبات المحاضرات عشان تتابع مستواك — ونتيجتك هتظهر هنا أول ما المصحح يخلص.
            </p>
            <a href="{{ route('student.courses') }}" class="btn-primary mt-5">روح لكورساتي</a>
        </div>
    @else
        <div class="table-box">
            <table class="table">
                <thead>
                    <tr>
                        <th>المحاضرة</th>
                        <th>الواجب</th>
                        <th>تاريخ التسليم</th>
                        <th>الحالة</th>
                        <th>الدرجة</th>
                        <th>ملاحظات المصحح</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($submissions as $submission)
                        <tr>
                            <td class="font-bold text-slate-900 dark:text-white">
                                {{ $submission->assignment->lecture->title ?? '—' }}
                            </td>
                            <td>{{ $submission->assignment->title ?? '—' }}</td>
                            <td class="text-slate-500 dark:text-slate-400">{{ $submission->created_at->translatedFormat('d M Y — h:i a') }}</td>
                            <td>
                                @if ($submission->status === 'graded')
                                    <span class="badge-green">تم التصحيح</span>
                                @else
                                    <span class="badge-amber">في انتظار المراجعة</span>
                                @endif
                            </td>
                            <td class="font-semibold">
                                @if ($submission->status === 'graded' && $submission->score !== null)
                                    <span dir="ltr">{{ $submission->score + 0 }} / {{ $submission->assignment->max_score ?? 100 }}</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="max-w-xs">
                                @if ($submission->feedback)
                                    <p class="whitespace-pre-line text-xs font-medium text-slate-600 dark:text-slate-300">{{ $submission->feedback }}</p>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $submissions->links() }}</div>
    @endif
@endsection
