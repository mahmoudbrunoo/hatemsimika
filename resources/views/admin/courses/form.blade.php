@extends('layouts.admin')

@section('title', ($course->exists ? 'تعديل كورس' : 'إضافة كورس') . ' — الإدارة')
@section('page-title', $course->exists ? 'تعديل كورس: ' . $course->title : 'إضافة كورس جديد')

@section('page-actions')
    <a href="{{ route('admin.courses.index') }}" class="btn-secondary btn-sm">رجوع للكورسات</a>
@endsection

@section('page')
    <form method="POST"
          action="{{ $course->exists ? route('admin.courses.update', $course) : route('admin.courses.store') }}"
          enctype="multipart/form-data"
          x-data="{ year: '{{ old('academic_year', $course->academic_year ?? '') }}' }"
          class="card-pad grid max-w-3xl gap-5 sm:grid-cols-2">
        @csrf
        @if ($course->exists)
            @method('PUT')
        @endif

        <div class="sm:col-span-2">
            <label for="title" class="label">اسم الكورس</label>
            <input id="title" name="title" type="text" value="{{ old('title', $course->title) }}" class="input"
                   placeholder="مثال: كورس شهر أكتوبر — تالتة ثانوي" required>
            <p class="mt-1 text-xs font-medium text-slate-400 dark:text-slate-500">الرابط (slug) يتولد تلقائياً من الاسم.</p>
            @error('title')<p class="error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="academic_year" class="label">الصف الدراسي</label>
            <select id="academic_year" name="academic_year" class="input" x-model="year" required>
                <option value="" disabled @selected(!old('academic_year', $course->academic_year))>اختر الصف</option>
                @foreach (\App\Models\User::YEARS as $year => $label)
                    <option value="{{ $year }}" @selected((int) old('academic_year', $course->academic_year) === $year)>{{ $label }}</option>
                @endforeach
            </select>
            @error('academic_year')<p class="error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="category" class="label">التصنيف</label>
            <select id="category" name="category" class="input" required>
                <option value="" disabled @selected(!old('category', $course->category))>اختر التصنيف</option>
                @foreach (\App\Models\Course::CATEGORIES as $value => $label)
                    @php
                        $allowedYears = collect(\App\Models\Course::CATEGORIES_BY_YEAR)
                            ->filter(fn ($categories) => in_array($value, $categories, true))
                            ->keys()
                            ->values();
                    @endphp
                    <option value="{{ $value }}"
                            x-show="{{ $allowedYears->toJson() }}.includes(Number(year))"
                            :disabled="!{{ $allowedYears->toJson() }}.includes(Number(year))"
                            @selected(old('category', $course->category) === $value)>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
            <p class="mt-1 text-xs font-medium text-slate-400 dark:text-slate-500" x-show="year === ''">اختر الصف الدراسي أولاً لعرض التصنيفات المتاحة.</p>
            @error('category')<p class="error">{{ $message }}</p>@enderror
        </div>

        <div class="sm:col-span-2">
            <label for="description" class="label">وصف الكورس (اختياري)</label>
            <textarea id="description" name="description" rows="4" class="input"
                      placeholder="وصف مختصر يظهر في صفحة الكورس">{{ old('description', $course->description) }}</textarea>
            @error('description')<p class="error">{{ $message }}</p>@enderror
        </div>

        <div class="sm:col-span-2">
            <label for="syllabus" class="label">تقسيمة المنهج (اختياري)</label>
            <textarea id="syllabus" name="syllabus" rows="4" class="input"
                      placeholder="محتوى المنهج الذي يغطيه الكورس">{{ old('syllabus', $course->syllabus) }}</textarea>
            @error('syllabus')<p class="error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="price" class="label">السعر الأصلي (بالجنيه)</label>
            <input id="price" name="price" type="number" step="0.01" min="0"
                   value="{{ old('price', $course->price) }}" class="input" dir="ltr" required>
            @error('price')<p class="error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="discount_price" class="label">السعر بعد الخصم (اختياري)</label>
            <input id="discount_price" name="discount_price" type="number" step="0.01" min="0"
                   value="{{ old('discount_price', $course->discount_price) }}" class="input" dir="ltr"
                   placeholder="اتركه فارغاً بدون خصم">
            <p class="mt-1 text-xs font-medium text-slate-400 dark:text-slate-500">يجب أن يكون أقل من السعر الأصلي.</p>
            @error('discount_price')<p class="error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="access_days" class="label">مدة صلاحية الاشتراك بالأيام (اختياري)</label>
            <input id="access_days" name="access_days" type="number" min="1"
                   value="{{ old('access_days', $course->access_days) }}" class="input" dir="ltr"
                   placeholder="اتركه فارغاً للاشتراك الدائم">
            @error('access_days')<p class="error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="passing_percent" class="label">نسبة النجاح الافتراضية (%)</label>
            <input id="passing_percent" name="passing_percent" type="number" min="1" max="100"
                   value="{{ old('passing_percent', $course->passing_percent ?? 60) }}" class="input" dir="ltr" required>
            @error('passing_percent')<p class="error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="position" class="label">ترتيب الظهور</label>
            <input id="position" name="position" type="number" min="0"
                   value="{{ old('position', $course->position ?? 0) }}" class="input" dir="ltr">
            @error('position')<p class="error">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="thumbnail" class="label">صورة الغلاف {{ $course->exists ? '(اتركها فارغة للإبقاء على الحالية)' : '(اختياري)' }}</label>
            <input id="thumbnail" name="thumbnail" type="file" accept="image/jpeg,image/png,image/webp" class="input">
            @if ($course->exists && $course->thumbnail_path)
                <img src="{{ \Illuminate\Support\Facades\Storage::url($course->thumbnail_path) }}" alt="غلاف {{ $course->title }}"
                     class="mt-3 h-24 rounded-xl border border-slate-300 object-cover dark:border-slate-800">
            @endif
            @error('thumbnail')<p class="error">{{ $message }}</p>@enderror
        </div>

        <div class="flex flex-wrap items-center gap-6 sm:col-span-2">
            <label class="flex cursor-pointer items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300">
                <input type="checkbox" name="is_published" value="1"
                       class="size-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500/40 dark:border-slate-700 dark:bg-slate-950"
                       @checked(old('is_published', $course->is_published))>
                منشور (ظاهر للطلاب)
            </label>
            <label class="flex cursor-pointer items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300">
                <input type="checkbox" name="is_featured" value="1"
                       class="size-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500/40 dark:border-slate-700 dark:bg-slate-950"
                       @checked(old('is_featured', $course->is_featured))>
                كورس مميز (يظهر في الصفحة الرئيسية)
            </label>
        </div>

        <div class="flex gap-3 sm:col-span-2">
            <button type="submit" class="btn-primary">{{ $course->exists ? 'حفظ التعديلات' : 'إنشاء الكورس' }}</button>
            <a href="{{ route('admin.courses.index') }}" class="btn-secondary">إلغاء</a>
        </div>
    </form>
@endsection
