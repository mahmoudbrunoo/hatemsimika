{{-- صفحة الواجب: التفاصيل + التسليم أو ملخص التسليم السابق --}}
@extends('layouts.student')

@section('title', 'الواجب — ' . $assignment->title)

@section('page-title', 'الواجب — ' . $assignment->title)

@section('page')
    <div class="mb-6">
        <a href="{{ route('student.learn.lecture', [$course, $lecture]) }}" class="btn-secondary btn-sm">
            ← الرجوع للمحاضرة: {{ $lecture->title }}
        </a>
    </div>

    {{-- بطاقة الواجب --}}
    <div class="card-pad">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-lg font-extrabold text-slate-900 dark:text-white">📝 {{ $assignment->title }}</h2>
            <span class="badge-sky">الدرجة النهائية: {{ $assignment->max_score }}</span>
        </div>

        @if ($assignment->description)
            <p class="mt-4 whitespace-pre-line text-sm leading-8 text-slate-600 dark:text-slate-300">{{ $assignment->description }}</p>
        @endif

        @if ($assignment->file_path)
            <a href="{{ Storage::url($assignment->file_path) }}" target="_blank" class="btn-secondary mt-4">
                📕 تحميل ملف الواجب
            </a>
        @endif
    </div>

    @if ($submission)
        {{-- ملخص التسليم --}}
        <div class="card-pad mt-6">
            <div class="flex flex-wrap items-center gap-2">
                <h2 class="text-lg font-extrabold text-slate-900 dark:text-white">تسليمك</h2>

                @if ($submission->status === 'graded')
                    <span class="badge-green">تم التصحيح</span>
                @else
                    <span class="badge-amber">في انتظار التصحيح</span>
                @endif
            </div>

            <p class="mt-2 text-xs font-bold text-slate-500 dark:text-slate-400">
                تاريخ التسليم: {{ $submission->created_at->format('Y/m/d — h:i A') }}
            </p>

            @if ($submission->status === 'graded')
                <div class="mt-4 flex flex-wrap items-center gap-4 rounded-xl bg-emerald-50 p-4 dark:bg-emerald-500/10">
                    <span class="text-2xl font-black text-emerald-700 dark:text-emerald-300">
                        {{ rtrim(rtrim(number_format((float) $submission->score, 2), '0'), '.') }} / {{ $assignment->max_score }}
                    </span>
                    <span class="text-sm font-bold text-emerald-700 dark:text-emerald-300">درجتك في الواجب</span>
                </div>

                @if ($submission->feedback)
                    <div class="mt-4 rounded-xl bg-slate-50 p-4 dark:bg-slate-800/50">
                        <h3 class="text-sm font-extrabold text-slate-900 dark:text-white">ملاحظات المصحح</h3>
                        <p class="mt-1.5 whitespace-pre-line text-sm leading-7 text-slate-600 dark:text-slate-300">{{ $submission->feedback }}</p>
                    </div>
                @endif
            @endif

            @if ($submission->answer_text)
                <div class="mt-4 border-t border-slate-100 pt-4 dark:border-slate-800">
                    <h3 class="text-sm font-extrabold text-slate-900 dark:text-white">إجابتك المكتوبة</h3>
                    <p class="mt-1.5 whitespace-pre-line text-sm leading-7 text-slate-600 dark:text-slate-300">{{ $submission->answer_text }}</p>
                </div>
            @endif

            @if ($submission->file_path)
                @php $isImage = in_array(strtolower(pathinfo($submission->file_path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp']); @endphp
                <div class="mt-4 border-t border-slate-100 pt-4 dark:border-slate-800">
                    <h3 class="text-sm font-extrabold text-slate-900 dark:text-white">الملف المرفوع</h3>
                    @if ($isImage)
                        <a href="{{ Storage::url($submission->file_path) }}" target="_blank">
                            <img src="{{ Storage::url($submission->file_path) }}" alt="صورة الحل" class="mt-2 max-h-72 rounded-xl border border-slate-100 dark:border-slate-800">
                        </a>
                    @else
                        <a href="{{ Storage::url($submission->file_path) }}" target="_blank" class="btn-secondary btn-sm mt-2">📄 عرض الملف المرفوع</a>
                    @endif
                </div>
            @endif
        </div>
    @else
        {{-- نموذج التسليم --}}
        <div class="card-pad mt-6">
            <h2 class="text-lg font-extrabold text-slate-900 dark:text-white">سلّم حلك</h2>

            <div class="mt-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-bold text-amber-700 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300">
                لازم تسلم الواجب الأول عشان يفتح الامتحان.
            </div>

            <form method="POST" action="{{ route('student.homework.submit', $assignment) }}" enctype="multipart/form-data" class="mt-5">
                @csrf

                <label for="answer_text" class="label">إجابتك المكتوبة</label>
                <textarea id="answer_text" name="answer_text" rows="6" maxlength="10000" class="input"
                          placeholder="اكتب حل الواجب هنا...">{{ old('answer_text') }}</textarea>
                @error('answer_text') <p class="error">{{ $message }}</p> @enderror

                <label for="file" class="label mt-5">أو ارفع صورة/ملف الحل</label>
                <input id="file" type="file" name="file" accept=".jpg,.jpeg,.png,.webp,.pdf"
                       class="input file:ml-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-1.5 file:text-xs file:font-bold file:text-brand-700 dark:file:bg-brand-500/10 dark:file:text-brand-300">
                <p class="mt-1.5 text-xs font-semibold text-slate-400">
                    JPG / PNG / WEBP / PDF — بحد أقصى 10 ميجا. اكتب إجابتك أو ارفع الملف (واحد منهم على الأقل).
                </p>
                @error('file') <p class="error">{{ $message }}</p> @enderror

                <button type="submit" class="btn-primary mt-5">تسليم الواجب</button>
            </form>
        </div>
    @endif
@endsection
