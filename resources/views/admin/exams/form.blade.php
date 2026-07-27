@extends('layouts.admin')

@section('title', ($exam->exists ? 'تعديل امتحان' : 'إضافة امتحان') . ' — الإدارة')
@section('page-title', $exam->exists ? 'تعديل امتحان: ' . $exam->title : 'إضافة امتحان جديد')

@section('page-actions')
    <a href="{{ route('admin.exams.index') }}" class="btn-secondary btn-sm">رجوع للامتحانات</a>
@endsection

@section('page')
    <form method="POST"
          action="{{ $exam->exists ? route('admin.exams.update', $exam) : route('admin.exams.store') }}"
          class="card-pad grid max-w-3xl gap-5 sm:grid-cols-2">
        @csrf
        @if ($exam->exists)
            @method('PUT')
        @endif

        <div class="sm:col-span-2">
            <label for="title" class="label">اسم الامتحان</label>
            <input id="title" name="title" type="text" value="{{ old('title', $exam->title) }}" class="input"
                   placeholder="مثال: كويز المحاضرة الأولى" required>
            @error('title')<p class="error">{{ $message }}</p>@enderror
        </div>

        <div class="sm:col-span-2">
            <label for="description" class="label">وصف الامتحان (اختياري)</label>
            <textarea id="description" name="description" rows="3" class="input">{{ old('description', $exam->description) }}</textarea>
            @error('description')<p class="error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="type" class="label">نوع الامتحان</label>
            <select id="type" name="type" class="input" required>
                <option value="" disabled @selected(!old('type', $exam->type))>اختر النوع</option>
                @foreach ([\App\Models\Exam::TYPE_QUIZ, \App\Models\Exam::TYPE_SHAMEL, \App\Models\Exam::TYPE_EVALUATION] as $value)
                    <option value="{{ $value }}" @selected(old('type', $exam->type) === $value)>{{ \App\Models\Exam::TYPES[$value] }}</option>
                @endforeach
            </select>
            @error('type')<p class="error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="lecture_id" class="label">ربط بمحاضرة (للكويزات — اختياري)</label>
            <select id="lecture_id" name="lecture_id" class="input">
                <option value="">بدون محاضرة</option>
                @foreach ($lectures->groupBy('course_id') as $courseLectures)
                    <optgroup label="{{ $courseLectures->first()->course?->title ?? 'بدون كورس' }}">
                        @foreach ($courseLectures as $lecture)
                            <option value="{{ $lecture->id }}" @selected((int) old('lecture_id', $exam->lecture_id) === $lecture->id)>
                                {{ $lecture->title }}
                            </option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
            @error('lecture_id')<p class="error">{{ $message }}</p>@enderror
        </div>

        <div class="sm:col-span-2">
            <label for="course_id" class="label">ربط بكورس (للشوامل واختبارات التقييم — اختياري)</label>
            <select id="course_id" name="course_id" class="input">
                <option value="">بدون كورس</option>
                @foreach ($courses as $course)
                    <option value="{{ $course->id }}" @selected((int) old('course_id', $exam->course_id) === $course->id)>
                        {{ $course->yearLabel() }} — {{ $course->title }}
                    </option>
                @endforeach
            </select>
            @error('course_id')<p class="error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="duration_minutes" class="label">المدة بالدقائق</label>
            <input id="duration_minutes" name="duration_minutes" type="number" min="1" max="600"
                   value="{{ old('duration_minutes', $exam->duration_minutes ?? 30) }}" class="input" dir="ltr" required>
            @error('duration_minutes')<p class="error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="passing_percent" class="label">نسبة النجاح % (اختياري)</label>
            <input id="passing_percent" name="passing_percent" type="number" min="1" max="100"
                   value="{{ old('passing_percent', $exam->passing_percent) }}" class="input" dir="ltr"
                   placeholder="يورث من المحاضرة/الكورس">
            @error('passing_percent')<p class="error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="max_attempts" class="label">عدد المحاولات المسموح بها</label>
            <input id="max_attempts" name="max_attempts" type="number" min="1" max="100"
                   value="{{ old('max_attempts', $exam->max_attempts ?? 1) }}" class="input" dir="ltr" required>
            @error('max_attempts')<p class="error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="answers_policy" class="label">سياسة إظهار الإجابات النموذجية</label>
            <select id="answers_policy" name="answers_policy" class="input" required>
                <option value="instant" @selected(old('answers_policy', $exam->answers_policy ?? 'instant') === 'instant')>فور التسليم</option>
                <option value="after_window" @selected(old('answers_policy', $exam->answers_policy) === 'after_window')>بعد غلق نافذة الامتحان (لمنع الغش في الشوامل)</option>
            </select>
            @error('answers_policy')<p class="error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="window_opens_at" class="label">نافذة الامتحان تفتح في (اختياري)</label>
            <input id="window_opens_at" name="window_opens_at" type="datetime-local"
                   value="{{ old('window_opens_at', $exam->window_opens_at?->format('Y-m-d\TH:i')) }}" class="input" dir="ltr">
            @error('window_opens_at')<p class="error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="window_closes_at" class="label">نافذة الامتحان تغلق في (اختياري)</label>
            <input id="window_closes_at" name="window_closes_at" type="datetime-local"
                   value="{{ old('window_closes_at', $exam->window_closes_at?->format('Y-m-d\TH:i')) }}" class="input" dir="ltr">
            @error('window_closes_at')<p class="error">{{ $message }}</p>@enderror
        </div>

        <div class="flex flex-wrap items-center gap-6 sm:col-span-2">
            <label class="flex cursor-pointer items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300">
                <input type="checkbox" name="hints_enabled" value="1"
                       class="size-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500/40 dark:border-slate-700 dark:bg-slate-950"
                       @checked(old('hints_enabled', $exam->hints_enabled))>
                تفعيل التلميحات أثناء الحل
            </label>
            <label class="flex cursor-pointer items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300">
                <input type="hidden" name="is_published" value="0">
                <input type="checkbox" name="is_published" value="1"
                       class="size-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500/40 dark:border-slate-700 dark:bg-slate-950"
                       @checked(old('is_published', $exam->is_published ?? true))>
                منشور للطلاب
            </label>
        </div>

        <div class="flex gap-3 sm:col-span-2">
            <button type="submit" class="btn-primary">{{ $exam->exists ? 'حفظ الامتحان' : 'إنشاء الامتحان' }}</button>
            <a href="{{ route('admin.exams.index') }}" class="btn-secondary">إلغاء</a>
        </div>
    </form>
@endsection
