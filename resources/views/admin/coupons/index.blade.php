@extends('layouts.admin')

@section('title', 'الكوبونات — لوحة التحكم')
@section('page-title', 'كوبونات الخصم')

@section('page')
    {{-- إنشاء كوبون جديد --}}
    <div class="card-pad mb-6">
        <h2 class="mb-4 text-lg font-extrabold text-slate-900 dark:text-white">إنشاء كوبون جديد</h2>
        <form method="POST" action="{{ route('admin.coupons.store') }}" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @csrf
            <div>
                <label for="code" class="label">الكود</label>
                <input id="code" name="code" type="text" maxlength="40" value="{{ old('code') }}"
                       class="input font-mono uppercase" dir="ltr" placeholder="RAMADAN25" required>
                @error('code')<p class="error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="type" class="label">نوع الخصم</label>
                <select id="type" name="type" class="input" required>
                    <option value="percent" @selected(old('type', 'percent') === 'percent')>نسبة مئوية %</option>
                    <option value="fixed" @selected(old('type') === 'fixed')>مبلغ ثابت (جنيه)</option>
                </select>
                @error('type')<p class="error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="value" class="label">قيمة الخصم</label>
                <input id="value" name="value" type="number" min="1" step="0.5" value="{{ old('value') }}"
                       class="input" dir="ltr" placeholder="مثال: 20" required>
                @error('value')<p class="error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="max_uses" class="label">أقصى عدد استخدام (اختياري)</label>
                <input id="max_uses" name="max_uses" type="number" min="1" value="{{ old('max_uses') }}"
                       class="input" dir="ltr" placeholder="اتركه فاضي = بلا حد">
                @error('max_uses')<p class="error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="expires_at" class="label">تاريخ الانتهاء (اختياري)</label>
                <input id="expires_at" name="expires_at" type="datetime-local" value="{{ old('expires_at') }}"
                       class="input" dir="ltr">
                @error('expires_at')<p class="error">{{ $message }}</p>@enderror
            </div>
            <div class="flex items-end gap-3">
                <label class="flex items-center gap-2 py-2.5 text-sm font-semibold text-slate-700 dark:text-slate-300">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', '1') === '1')
                           class="size-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500/40">
                    مفعّل
                </label>
                <button type="submit" class="btn-primary">إنشاء الكوبون</button>
            </div>
        </form>
    </div>

    {{-- قائمة الكوبونات --}}
    @if ($coupons->isEmpty())
        <div class="card-pad text-center text-slate-500 dark:text-slate-400">لا توجد كوبونات بعد — أنشئ أول كوبون من الفورم اللي فوق.</div>
    @else
        <div class="table-box">
            <table class="table">
                <thead>
                    <tr>
                        <th>الكود</th>
                        <th>الخصم</th>
                        <th>الاستخدام</th>
                        <th>الانتهاء</th>
                        <th>الحالة</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($coupons as $coupon)
                        <tr x-data="{ edit: false }">
                            <td class="font-mono font-bold" dir="ltr">{{ $coupon->code }}</td>
                            <td class="font-semibold">
                                @if ($coupon->type === 'percent')
                                    خصم {{ rtrim(rtrim(number_format((float) $coupon->value, 2), '0'), '.') }}%
                                @else
                                    خصم {{ egp($coupon->value) }}
                                @endif
                            </td>
                            <td class="font-semibold" dir="ltr">{{ $coupon->used_count }} / {{ $coupon->max_uses ?? '∞' }}</td>
                            <td class="text-xs font-semibold text-slate-500">
                                {{ $coupon->expires_at?->format('Y/m/d H:i') ?? 'بدون انتهاء' }}
                            </td>
                            <td>
                                @if ($coupon->isUsable())
                                    <span class="badge-green">فعّال</span>
                                @elseif (! $coupon->is_active)
                                    <span class="badge-gray">موقوف</span>
                                @elseif ($coupon->expires_at?->isPast())
                                    <span class="badge-red">منتهي</span>
                                @else
                                    <span class="badge-red">استُهلك بالكامل</span>
                                @endif
                            </td>
                            <td>
                                <div class="flex flex-wrap items-start gap-2">
                                    <button type="button" @click="edit = !edit" class="btn-secondary btn-sm" x-text="edit ? 'إغلاق' : 'تعديل'">تعديل</button>
                                    <form method="POST" action="{{ route('admin.coupons.destroy', $coupon) }}"
                                          onsubmit="return confirm('حذف الكوبون {{ $coupon->code }} نهائياً؟')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-danger btn-sm">حذف</button>
                                    </form>
                                </div>

                                {{-- تعديل سريع --}}
                                <form x-show="edit" x-cloak method="POST" action="{{ route('admin.coupons.update', $coupon) }}"
                                      class="mt-3 grid w-72 gap-2 rounded-xl bg-slate-50 p-3 dark:bg-slate-950/50">
                                    @csrf
                                    @method('PUT')
                                    <input type="text" name="code" maxlength="40" value="{{ $coupon->code }}" class="input font-mono uppercase" dir="ltr" required>
                                    <div class="flex gap-2">
                                        <select name="type" class="input">
                                            <option value="percent" @selected($coupon->type === 'percent')>نسبة %</option>
                                            <option value="fixed" @selected($coupon->type === 'fixed')>مبلغ ثابت</option>
                                        </select>
                                        <input type="number" name="value" min="1" step="0.5" value="{{ (float) $coupon->value }}" class="input" dir="ltr" required>
                                    </div>
                                    <input type="number" name="max_uses" min="1" value="{{ $coupon->max_uses }}" class="input" dir="ltr" placeholder="أقصى استخدام (فاضي = بلا حد)">
                                    <input type="datetime-local" name="expires_at" value="{{ $coupon->expires_at?->format('Y-m-d\TH:i') }}" class="input" dir="ltr">
                                    <label class="flex items-center gap-2 text-xs font-semibold text-slate-700 dark:text-slate-300">
                                        <input type="hidden" name="is_active" value="0">
                                        <input type="checkbox" name="is_active" value="1" @checked($coupon->is_active)
                                               class="size-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500/40">
                                        مفعّل
                                    </label>
                                    <button type="submit" class="btn-primary btn-sm">حفظ التعديل</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $coupons->links() }}</div>
    @endif
@endsection
