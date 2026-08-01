@extends('layouts.admin')

@section('title', 'الشات بوت التفاعلي — لوحة التحكم')
@section('page-title', 'الشات بوت التفاعلي')

@section('page')
    {{-- إضافة خيار جديد --}}
    <div id="add-option" class="card-pad mb-6">
        <h2 class="mb-1 text-lg font-extrabold text-slate-900 dark:text-white">إضافة خيار جديد</h2>
        <p class="mb-4 text-sm font-semibold text-slate-500 dark:text-slate-400">
            الطالب بيدوس على الأزرار دي في الشات بوت — الخيار ممكن يكون سؤال رئيسي أو فرعي جوه خيار تاني،
            وممكن تحط له رد نصي و/أو زر برابط (واتساب، فيسبوك، ...) و/أو خيارات فرعية بلا حدود.
        </p>
        <form method="POST" action="{{ route('admin.chatbot.store') }}" class="grid gap-4 sm:grid-cols-2">
            @csrf
            <div>
                <label for="label" class="label">نص الزر</label>
                <input id="label" name="label" type="text" maxlength="120" value="{{ old('label') }}"
                       class="input" placeholder="مثال: إزاي أشترك في كورس؟" required>
                @error('label')<p class="error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="parent_id" class="label">مكان الخيار</label>
                <select id="parent_id" name="parent_id" class="input">
                    <option value="">— خيار رئيسي في القائمة الأساسية —</option>
                    @foreach ($flat as $row)
                        <option value="{{ $row['option']->id }}" @selected((int) old('parent_id', request('parent')) === $row['option']->id)>
                            {{ str_repeat('— ', $row['depth']) }}{{ $row['option']->label }}
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs font-medium text-slate-400">اختار خيار موجود علشان يبقى الجديد فرعي جواه</p>
                @error('parent_id')<p class="error">{{ $message }}</p>@enderror
            </div>
            <div class="sm:col-span-2">
                <label for="response" class="label">الرد (اختياري)</label>
                <textarea id="response" name="response" rows="3" class="input"
                          placeholder="الرد اللي هيظهر للطالب أول ما يدوس على الزر">{{ old('response') }}</textarea>
                <p class="mt-1 text-xs font-medium text-slate-400" dir="ltr">
                    يدعم HTML للروابط: &lt;a href="https://wa.me/20100..."&gt;كلمنا واتساب&lt;/a&gt;
                </p>
                @error('response')<p class="error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="link_url" class="label">رابط الزر (اختياري)</label>
                <input id="link_url" name="link_url" type="url" maxlength="500" value="{{ old('link_url') }}"
                       class="input" dir="ltr" placeholder="https://wa.me/20100... أو رابط فيسبوك">
                @error('link_url')<p class="error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="link_label" class="label">نص زر الرابط</label>
                <input id="link_label" name="link_label" type="text" maxlength="120" value="{{ old('link_label') }}"
                       class="input" placeholder="مثال: كلمنا على واتساب">
                @error('link_label')<p class="error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="position" class="label">ترتيب الظهور</label>
                <input id="position" name="position" type="number" min="0" value="{{ old('position', 0) }}"
                       class="input" dir="ltr">
                @error('position')<p class="error">{{ $message }}</p>@enderror
            </div>
            <div class="flex items-center gap-4 self-end">
                <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', '1') === '1')
                           class="size-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500/40">
                    ظاهر للطلاب
                </label>
                <button type="submit" class="btn-primary">إضافة الخيار</button>
            </div>
        </form>
    </div>

    {{-- شجرة الخيارات --}}
    @if (($grouped[0] ?? collect())->isEmpty())
        <div class="card-pad text-center text-slate-500 dark:text-slate-400">
            لا توجد خيارات بعد — أضف أول سؤال رئيسي من الفورم اللي فوق وهيظهر فوراً في الشات بوت.
        </div>
    @else
        <div class="space-y-3">
            @foreach ($grouped[0] as $option)
                @include('admin.chatbot._node', ['option' => $option, 'depth' => 0])
            @endforeach
        </div>
    @endif
@endsection
