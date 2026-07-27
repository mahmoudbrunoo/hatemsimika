@extends('layouts.admin')

@section('title', 'الأسئلة الشائعة — لوحة التحكم')
@section('page-title', 'الأسئلة الشائعة')

@section('page')
    {{-- إضافة سؤال جديد --}}
    <div class="card-pad mb-6">
        <h2 class="mb-1 text-lg font-extrabold text-slate-900 dark:text-white">إضافة سؤال جديد</h2>
        <p class="mb-4 text-sm font-semibold text-slate-500 dark:text-slate-400">
            الأسئلة دي بيرد بيها الشات بوت على الطلاب — وبتظهر كمان في صفحة المساعدة.
        </p>
        <form method="POST" action="{{ route('admin.faqs.store') }}" class="grid gap-4 sm:grid-cols-2">
            @csrf
            <div class="sm:col-span-2">
                <label for="question" class="label">السؤال</label>
                <input id="question" name="question" type="text" maxlength="255" value="{{ old('question') }}"
                       class="input" placeholder="مثال: إزاي أشحن المحفظة؟" required>
                @error('question')<p class="error">{{ $message }}</p>@enderror
            </div>
            <div class="sm:col-span-2">
                <label for="answer" class="label">الإجابة</label>
                <textarea id="answer" name="answer" rows="3" class="input"
                          placeholder="اكتب الإجابة الكاملة اللي هتظهر للطالب" required>{{ old('answer') }}</textarea>
                @error('answer')<p class="error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="keywords" class="label">كلمات مفتاحية للبوت (اختياري)</label>
                <input id="keywords" name="keywords" type="text" maxlength="255" value="{{ old('keywords') }}"
                       class="input" placeholder="مثال: شحن, محفظة, فلوس, رصيد">
                <p class="mt-1 text-xs font-medium text-slate-400">افصل الكلمات بفاصلة — بتساعد البوت يلاقي السؤال</p>
                @error('keywords')<p class="error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="position" class="label">ترتيب الظهور</label>
                <input id="position" name="position" type="number" min="0" value="{{ old('position', 0) }}"
                       class="input" dir="ltr">
                @error('position')<p class="error">{{ $message }}</p>@enderror
            </div>
            <div class="flex items-center gap-4 sm:col-span-2">
                <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', '1') === '1')
                           class="size-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500/40">
                    ظاهر للطلاب
                </label>
                <button type="submit" class="btn-primary">إضافة السؤال</button>
            </div>
        </form>
    </div>

    {{-- قائمة الأسئلة --}}
    @if ($faqs->isEmpty())
        <div class="card-pad text-center text-slate-500 dark:text-slate-400">لا توجد أسئلة شائعة بعد — أضف أول سؤال من الفورم اللي فوق.</div>
    @else
        <div class="space-y-4">
            @foreach ($faqs as $faq)
                <div class="card-pad" x-data="{ edit: false }">
                    <div x-show="!edit">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <p class="font-extrabold text-slate-900 dark:text-white">{{ $faq->question }}</p>
                                <p class="mt-2 whitespace-pre-line text-sm leading-7 text-slate-600 dark:text-slate-300">{{ $faq->answer }}</p>
                                @if ($faq->keywords)
                                    <p class="mt-2 text-xs font-semibold text-slate-400">كلمات مفتاحية: {{ $faq->keywords }}</p>
                                @endif
                            </div>
                            <div class="flex shrink-0 flex-col items-end gap-2">
                                @if ($faq->is_active)
                                    <span class="badge-green">ظاهر</span>
                                @else
                                    <span class="badge-gray">مخفي</span>
                                @endif
                                <div class="flex gap-2">
                                    <button type="button" @click="edit = true" class="btn-secondary btn-sm">تعديل</button>
                                    <form method="POST" action="{{ route('admin.faqs.destroy', $faq) }}"
                                          onsubmit="return confirm('حذف السؤال نهائياً؟')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-danger btn-sm">حذف</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- تعديل سريع --}}
                    <form x-show="edit" x-cloak method="POST" action="{{ route('admin.faqs.update', $faq) }}" class="grid gap-3 sm:grid-cols-2">
                        @csrf
                        @method('PUT')
                        <div class="sm:col-span-2">
                            <label for="question-{{ $faq->id }}" class="label">السؤال</label>
                            <input id="question-{{ $faq->id }}" name="question" type="text" maxlength="255"
                                   value="{{ $faq->question }}" class="input" required>
                        </div>
                        <div class="sm:col-span-2">
                            <label for="answer-{{ $faq->id }}" class="label">الإجابة</label>
                            <textarea id="answer-{{ $faq->id }}" name="answer" rows="3" class="input" required>{{ $faq->answer }}</textarea>
                        </div>
                        <div>
                            <label for="keywords-{{ $faq->id }}" class="label">كلمات مفتاحية للبوت</label>
                            <input id="keywords-{{ $faq->id }}" name="keywords" type="text" maxlength="255"
                                   value="{{ $faq->keywords }}" class="input">
                        </div>
                        <div>
                            <label for="position-{{ $faq->id }}" class="label">ترتيب الظهور</label>
                            <input id="position-{{ $faq->id }}" name="position" type="number" min="0"
                                   value="{{ $faq->position }}" class="input" dir="ltr">
                        </div>
                        <div class="flex items-center gap-3 sm:col-span-2">
                            <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" value="1" @checked($faq->is_active)
                                       class="size-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500/40">
                                ظاهر للطلاب
                            </label>
                            <button type="submit" class="btn-primary btn-sm">حفظ التعديل</button>
                            <button type="button" @click="edit = false" class="btn-secondary btn-sm">إلغاء</button>
                        </div>
                    </form>
                </div>
            @endforeach
        </div>

        <div class="mt-4">{{ $faqs->links() }}</div>
    @endif
@endsection
