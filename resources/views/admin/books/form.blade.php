@extends('layouts.admin')

@php($editing = $book->exists)

@section('title', ($editing ? 'تعديل كتاب' : 'إضافة كتاب') . ' — لوحة التحكم')
@section('page-title', $editing ? 'تعديل: ' . $book->title : 'إضافة كتاب جديد')

@section('page-actions')
    <a href="{{ route('admin.books.index') }}" class="btn-secondary">الرجوع للكتب</a>
@endsection

@section('page')
    <div class="card-pad">
        <form method="POST" enctype="multipart/form-data"
              action="{{ $editing ? route('admin.books.update', $book) : route('admin.books.store') }}"
              class="grid gap-5 sm:grid-cols-2">
            @csrf
            @if ($editing)
                @method('PUT')
            @endif

            <div class="sm:col-span-2">
                <label for="title" class="label">اسم الكتاب</label>
                <input id="title" name="title" type="text" maxlength="255" value="{{ old('title', $book->title) }}"
                       class="input" placeholder="مثال: كتاب المراجعة النهائية — الصف الثالث" required>
                @error('title')<p class="error">{{ $message }}</p>@enderror
            </div>

            <div class="sm:col-span-2">
                <label for="description" class="label">الوصف (اختياري)</label>
                <textarea id="description" name="description" rows="4" class="input"
                          placeholder="محتوى الكتاب ومميزاته">{{ old('description', $book->description) }}</textarea>
                @error('description')<p class="error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="price" class="label">السعر (جنيه)</label>
                <input id="price" name="price" type="number" min="0" step="0.5" dir="ltr"
                       value="{{ old('price', $book->price !== null ? (float) $book->price : null) }}" class="input" required>
                @error('price')<p class="error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="discount_price" class="label">السعر بعد الخصم (اختياري)</label>
                <input id="discount_price" name="discount_price" type="number" min="0" step="0.5" dir="ltr"
                       value="{{ old('discount_price', $book->discount_price !== null ? (float) $book->discount_price : null) }}" class="input"
                       placeholder="لازم يكون أقل من السعر الأساسي">
                @error('discount_price')<p class="error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="stock" class="label">الكمية المتاحة (اختياري)</label>
                <input id="stock" name="stock" type="number" min="0" dir="ltr"
                       value="{{ old('stock', $book->stock) }}" class="input" placeholder="اتركه فاضي = غير محدود">
                @error('stock')<p class="error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="academic_year" class="label">الصف الدراسي (اختياري)</label>
                <select id="academic_year" name="academic_year" class="input">
                    <option value="">كل الصفوف</option>
                    @foreach ([1 => 'الصف الأول الثانوي', 2 => 'الصف الثاني الثانوي', 3 => 'الصف الثالث الثانوي'] as $year => $label)
                        <option value="{{ $year }}" @selected((int) old('academic_year', $book->academic_year) === $year)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('academic_year')<p class="error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="position" class="label">ترتيب الظهور</label>
                <input id="position" name="position" type="number" min="0" dir="ltr"
                       value="{{ old('position', $book->position ?? 0) }}" class="input">
                @error('position')<p class="error">{{ $message }}</p>@enderror
            </div>

            <div class="flex items-end">
                <label class="flex items-center gap-2 py-2.5 text-sm font-semibold text-slate-700 dark:text-slate-300">
                    <input type="hidden" name="is_published" value="0">
                    <input type="checkbox" name="is_published" value="1"
                           @checked((bool) old('is_published', $editing ? $book->is_published : true))
                           class="size-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500/40">
                    منشور للطلاب
                </label>
            </div>

            <div x-data="{ preview: null }">
                <label for="cover" class="label">صورة الغلاف</label>
                <input id="cover" name="cover" type="file" accept="image/jpeg,image/png,image/webp"
                       @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null"
                       class="input cursor-pointer p-2 file:ml-3 file:cursor-pointer file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-1.5 file:text-xs file:font-bold file:text-brand-700 dark:file:bg-brand-500/10 dark:file:text-brand-300">
                <p class="mt-1 text-xs font-medium text-slate-400">JPG أو PNG أو WEBP — بحد أقصى 4 ميجا</p>
                @error('cover')<p class="error">{{ $message }}</p>@enderror

                <template x-if="preview">
                    <img :src="preview" alt="معاينة الغلاف" class="mt-3 h-32 w-24 rounded-xl border border-slate-300 object-cover dark:border-slate-700">
                </template>
                @if ($editing && $book->cover_path)
                    <div x-show="!preview" class="mt-3">
                        <p class="mb-1 text-xs font-bold text-slate-400">الغلاف الحالي</p>
                        <img src="{{ $book->cover_path }}"
                             alt="الغلاف الحالي" class="h-32 w-24 rounded-xl border border-slate-300 object-cover dark:border-slate-700">
                    </div>
                @endif
            </div>

            <div>
                <label for="preview_pdf" class="label">ملف المعاينة (PDF اختياري)</label>
                <input id="preview_pdf" name="preview_pdf" type="file" accept="application/pdf"
                       class="input cursor-pointer p-2 file:ml-3 file:cursor-pointer file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-1.5 file:text-xs file:font-bold file:text-brand-700 dark:file:bg-brand-500/10 dark:file:text-brand-300">
                <p class="mt-1 text-xs font-medium text-slate-400">عينة من الكتاب يشوفها الطالب قبل الشراء — بحد أقصى 20 ميجا</p>
                @error('preview_pdf')<p class="error">{{ $message }}</p>@enderror
                @if ($editing && $book->preview_pdf_path)
                    <a href="{{ $book->preview_pdf_path }}" target="_blank"
                       class="mt-2 inline-block text-xs font-bold text-brand-600 hover:text-brand-700 dark:text-brand-400">
                        الملف الحالي (PDF)
                    </a>
                @endif
            </div>

            <div class="flex gap-3 sm:col-span-2">
                <button type="submit" class="btn-primary">{{ $editing ? 'حفظ التعديلات' : 'إضافة الكتاب' }}</button>
                <a href="{{ route('admin.books.index') }}" class="btn-secondary">إلغاء</a>
            </div>
        </form>
    </div>
@endsection
