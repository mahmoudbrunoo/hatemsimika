@extends('layouts.admin')

@section('title', 'محاضرات: ' . $course->title . ' — الإدارة')
@section('page-title', 'محاضرات الكورس')

@section('page-actions')
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('admin.courses.edit', $course) }}" class="btn-secondary btn-sm">تعديل الكورس</a>
        <a href="{{ route('admin.courses.index') }}" class="btn-secondary btn-sm">رجوع للكورسات</a>
    </div>
@endsection

@section('page')
    {{-- بطاقة بيانات الكورس --}}
    <div class="card-pad mb-6 flex flex-wrap items-center gap-5">
        @if ($course->thumbnail_path)
            <img src="{{ \Illuminate\Support\Facades\Storage::url($course->thumbnail_path) }}" alt="{{ $course->title }}"
                 class="h-16 w-24 rounded-xl object-cover">
        @else
            <div class="grid h-16 w-24 place-items-center rounded-xl bg-slate-100 text-2xl dark:bg-slate-800">📚</div>
        @endif
        <div class="min-w-0 flex-1">
            <h2 class="text-lg font-extrabold text-slate-900 dark:text-white">{{ $course->title }}</h2>
            <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">
                {{ $course->yearLabel() }} — {{ $course->categoryLabel() }} — {{ egp($course->effectivePrice()) }}
            </p>
        </div>
        <div>
            @if ($course->is_published)
                <span class="badge-green">منشور</span>
            @else
                <span class="badge-gray">مسودة</span>
            @endif
        </div>
    </div>

    {{-- إضافة محاضرة جديدة --}}
    <div class="card-pad mb-6">
        <h2 class="mb-4 font-extrabold text-slate-900 dark:text-white">إضافة محاضرة جديدة</h2>
        <form method="POST" action="{{ route('admin.lectures.store', $course) }}" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @csrf

            <div class="sm:col-span-2">
                <label for="title" class="label">اسم المحاضرة</label>
                <input id="title" name="title" type="text" value="{{ old('title') }}" class="input"
                       placeholder="مثال: المحاضرة الأولى — مقدمة" required>
                @error('title')<p class="error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="position" class="label">الترتيب (اختياري)</label>
                <input id="position" name="position" type="number" min="0" value="{{ old('position') }}"
                       class="input" dir="ltr" placeholder="تلقائي">
                @error('position')<p class="error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="passing_percent" class="label">نسبة النجاح % (اختياري)</label>
                <input id="passing_percent" name="passing_percent" type="number" min="1" max="100"
                       value="{{ old('passing_percent') }}" class="input" dir="ltr" placeholder="يورث من الكورس">
                @error('passing_percent')<p class="error">{{ $message }}</p>@enderror
            </div>

            <div class="sm:col-span-2 lg:col-span-4">
                <label for="description" class="label">وصف المحاضرة (اختياري)</label>
                <textarea id="description" name="description" rows="2" class="input">{{ old('description') }}</textarea>
                @error('description')<p class="error">{{ $message }}</p>@enderror
            </div>

            <div class="flex flex-wrap items-center gap-6 sm:col-span-2 lg:col-span-3">
                <label class="flex cursor-pointer items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300">
                    <input type="hidden" name="is_published" value="0">
                    <input type="checkbox" name="is_published" value="1"
                           class="size-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500/40 dark:border-slate-700 dark:bg-slate-950"
                           @checked(old('is_published', true))>
                    منشورة
                </label>
                <label class="flex cursor-pointer items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300">
                    <input type="checkbox" name="is_free_preview" value="1"
                           class="size-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500/40 dark:border-slate-700 dark:bg-slate-950"
                           @checked(old('is_free_preview'))>
                    معاينة مجانية
                </label>
            </div>

            <div class="self-end">
                <button type="submit" class="btn-primary w-full">إضافة المحاضرة</button>
            </div>
        </form>
    </div>

    {{-- جدول المحاضرات --}}
    <div class="table-box">
        <table class="table">
            <thead>
                <tr>
                    <th>الترتيب</th>
                    <th>اسم المحاضرة</th>
                    <th>المحتوى</th>
                    <th>الحالة</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($lectures as $lecture)
                    <tr>
                        <td class="font-bold">{{ $lecture->position }}</td>
                        <td class="font-bold text-slate-900 dark:text-white">{{ $lecture->title }}</td>
                        <td>
                            <div class="flex flex-wrap gap-1">
                                <span class="badge-sky">{{ number_format($lecture->videos_count) }} فيديو</span>
                                <span class="badge-gray">{{ number_format($lecture->attachments_count) }} ملف</span>
                                @if ($lecture->assignment)
                                    <span class="badge-green">واجب</span>
                                @endif
                                @if ($lecture->exam)
                                    <span class="badge-amber">امتحان</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="flex flex-wrap gap-1">
                                @if ($lecture->is_published)
                                    <span class="badge-green">منشورة</span>
                                @else
                                    <span class="badge-gray">مسودة</span>
                                @endif
                                @if ($lecture->is_free_preview)
                                    <span class="badge-sky">معاينة مجانية</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('admin.lectures.edit', $lecture) }}" class="btn-secondary btn-sm">إدارة المحتوى</a>
                                <form method="POST" action="{{ route('admin.lectures.destroy', $lecture) }}"
                                      onsubmit="return confirm('هل أنت متأكد من حذف المحاضرة وكل محتواها؟')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-danger btn-sm">حذف</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-slate-500 dark:text-slate-400">لا توجد محاضرات بعد — أضف أول محاضرة من النموذج بالأعلى.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
