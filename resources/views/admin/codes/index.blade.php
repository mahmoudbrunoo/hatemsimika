@extends('layouts.admin')

@section('title', 'أكواد السنتر — لوحة التحكم')
@section('page-title', 'أكواد السنتر')

@section('page')
    {{-- توليد دفعة أكواد --}}
    <div class="card-pad mb-6">
        <h2 class="mb-1 text-lg font-extrabold text-slate-900 dark:text-white">توليد دفعة أكواد جديدة</h2>
        <p class="mb-4 text-sm font-semibold text-slate-500 dark:text-slate-400">
            وزّع الأكواد على السنتر — الطالب يشحنها من صفحة "شحن كود".
            الكود إما شحن رصيد للمحفظة أو فتح كورس مباشرة.
        </p>
        <form method="POST" action="{{ route('admin.codes.generate') }}" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @csrf
            <div>
                <label for="count" class="label">عدد الأكواد</label>
                <input id="count" name="count" type="number" min="1" max="500" value="{{ old('count', 50) }}"
                       class="input" dir="ltr" required>
                @error('count')<p class="error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="value" class="label">قيمة الشحن (جنيه)</label>
                <input id="value" name="value" type="number" min="0" step="0.5" value="{{ old('value') }}"
                       class="input" dir="ltr" placeholder="مثال: 100">
                <p class="mt-1 text-xs font-medium text-slate-400">حدد قيمة شحن أو اختر كورس</p>
                @error('value')<p class="error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="course_id" class="label">أو فتح كورس مباشرة (اختياري)</label>
                <select id="course_id" name="course_id" class="input">
                    <option value="">— بدون كورس (شحن رصيد) —</option>
                    @foreach ($courses as $course)
                        <option value="{{ $course->id }}" @selected((int) old('course_id') === $course->id)>{{ $course->title }}</option>
                    @endforeach
                </select>
                @error('course_id')<p class="error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="batch_name" class="label">اسم الدفعة</label>
                <input id="batch_name" name="batch" type="text" maxlength="40" value="{{ old('batch') }}"
                       class="input" placeholder="مثال: سنتر المنصورة - أغسطس" required>
                @error('batch')<p class="error">{{ $message }}</p>@enderror
            </div>
            <div class="sm:col-span-2 lg:col-span-4">
                <button type="submit" class="btn-primary">توليد أكواد</button>
            </div>
        </form>
    </div>

    {{-- فلاتر --}}
    <form method="GET" action="{{ route('admin.codes.index') }}" class="card-pad mb-6 flex flex-wrap items-end gap-3">
        <div>
            <label for="batch_filter" class="label">الدفعة</label>
            <select id="batch_filter" name="batch" class="input">
                <option value="">كل الدفعات</option>
                @foreach ($batches as $b)
                    <option value="{{ $b }}" @selected($batch === $b)>{{ $b }}</option>
                @endforeach
            </select>
        </div>
        <label class="flex items-center gap-2 py-2.5 text-sm font-semibold text-slate-700 dark:text-slate-300">
            <input type="checkbox" name="unused" value="1" @checked($unusedOnly)
                   class="size-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500/40">
            غير المستخدمة فقط
        </label>
        <button type="submit" class="btn-secondary">تصفية</button>
        @if ($batch || $unusedOnly)
            <a href="{{ route('admin.codes.index') }}" class="btn-secondary">مسح الفلاتر</a>
        @endif
    </form>

    {{-- جدول الأكواد --}}
    @if ($codes->isEmpty())
        <div class="card-pad text-center text-slate-500 dark:text-slate-400">لا توجد أكواد مطابقة — ولّد دفعة جديدة من الفورم اللي فوق.</div>
    @else
        <div class="table-box">
            <table class="table">
                <thead>
                    <tr>
                        <th>الكود</th>
                        <th>القيمة</th>
                        <th>الدفعة</th>
                        <th>الحالة</th>
                        <th>تاريخ الإنشاء</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($codes as $code)
                        <tr>
                            <td>
                                <span class="select-all rounded-lg bg-slate-100 px-2 py-1 font-mono text-sm font-bold tracking-wider dark:bg-slate-800" dir="ltr">{{ $code->code }}</span>
                            </td>
                            <td class="font-semibold">
                                @if ($code->course)
                                    <span class="badge-sky">فتح كورس: {{ $code->course->title }}</span>
                                @else
                                    {{ egp($code->value) }}
                                @endif
                            </td>
                            <td class="text-xs font-semibold text-slate-500">{{ $code->batch ?? '—' }}</td>
                            <td>
                                @if ($code->used_by)
                                    <span class="badge-gray">مستخدم</span>
                                    <p class="mt-1 text-xs font-semibold text-slate-400">
                                        {{ $code->usedBy?->name ?? 'طالب محذوف' }} — {{ $code->used_at?->format('Y/m/d H:i') }}
                                    </p>
                                @else
                                    <span class="badge-green">متاح</span>
                                @endif
                            </td>
                            <td class="text-xs font-semibold text-slate-500">{{ $code->created_at->format('Y/m/d H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $codes->links() }}</div>
    @endif
@endsection
