@extends('layouts.admin')

@section('title', 'الامتحانات — الإدارة')
@section('page-title', 'الامتحانات')

@section('page-actions')
    <a href="{{ route('admin.exams.create') }}" class="btn-primary">إضافة امتحان</a>
@endsection

@section('page')
    {{-- فلترة حسب النوع --}}
    <form method="GET" action="{{ route('admin.exams.index') }}" class="card-pad mb-6">
        <div class="grid gap-4 sm:grid-cols-[16rem_auto]">
            <div>
                <label for="type" class="label">نوع الامتحان</label>
                <select id="type" name="type" class="input">
                    <option value="">كل الأنواع</option>
                    @foreach ([\App\Models\Exam::TYPE_QUIZ, \App\Models\Exam::TYPE_SHAMEL, \App\Models\Exam::TYPE_EVALUATION] as $value)
                        <option value="{{ $value }}" @selected($type === $value)>{{ \App\Models\Exam::TYPES[$value] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="self-end">
                <button type="submit" class="btn-secondary">فلترة</button>
            </div>
        </div>
    </form>

    <div class="table-box">
        <table class="table">
            <thead>
                <tr>
                    <th>اسم الامتحان</th>
                    <th>النوع</th>
                    <th>مرتبط بـ</th>
                    <th>الأسئلة</th>
                    <th>المدة</th>
                    <th>نسبة النجاح</th>
                    <th>الحالة</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($exams as $exam)
                    <tr>
                        <td class="font-bold text-slate-900 dark:text-white">{{ $exam->title }}</td>
                        <td>
                            @if ($exam->type === \App\Models\Exam::TYPE_QUIZ)
                                <span class="badge-sky">{{ $exam->typeLabel() }}</span>
                            @elseif ($exam->type === \App\Models\Exam::TYPE_SHAMEL)
                                <span class="badge-amber">{{ $exam->typeLabel() }}</span>
                            @else
                                <span class="badge-green">{{ $exam->typeLabel() }}</span>
                            @endif
                        </td>
                        <td>
                            @if ($exam->lecture)
                                <span class="block text-xs font-bold text-slate-400">محاضرة</span>
                                {{ $exam->lecture->title }}
                                <span class="block text-xs text-slate-400">{{ $exam->lecture->course?->title }}</span>
                            @elseif ($exam->course)
                                <span class="block text-xs font-bold text-slate-400">كورس</span>
                                {{ $exam->course->title }}
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        <td>{{ number_format($exam->questions_count) }}</td>
                        <td>{{ $exam->duration_minutes }} دقيقة</td>
                        <td>{{ $exam->passingPercent() }}%</td>
                        <td>
                            @if ($exam->is_published)
                                <span class="badge-green">منشور</span>
                            @else
                                <span class="badge-gray">مسودة</span>
                            @endif
                            @if ($exam->window_opens_at || $exam->window_closes_at)
                                <span class="mt-1 block text-xs font-semibold text-slate-500 dark:text-slate-400">
                                    @if ($exam->window_opens_at)
                                        يفتح: {{ $exam->window_opens_at->format('Y/m/d h:i A') }}
                                    @endif
                                    @if ($exam->window_closes_at)
                                        <span class="block">يغلق: {{ $exam->window_closes_at->format('Y/m/d h:i A') }}</span>
                                    @endif
                                </span>
                            @endif
                        </td>
                        <td>
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('admin.questions.index', $exam) }}" class="btn-secondary btn-sm">الأسئلة</a>
                                <a href="{{ route('admin.exams.edit', $exam) }}" class="btn-secondary btn-sm">تعديل</a>
                                <form method="POST" action="{{ route('admin.exams.destroy', $exam) }}"
                                      onsubmit="return confirm('هل أنت متأكد من حذف الامتحان وكل أسئلته ومحاولات الطلاب عليه؟')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-danger btn-sm">حذف</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-slate-500 dark:text-slate-400">
                            لا توجد امتحانات بعد —
                            <a href="{{ route('admin.exams.create') }}" class="font-bold text-brand-600 hover:underline dark:text-brand-400">أضف أول امتحان</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $exams->links() }}
    </div>
@endsection
