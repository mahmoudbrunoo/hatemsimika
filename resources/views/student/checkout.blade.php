@extends('layouts.student')

@section('title', 'إتمام الشراء — ' . $item->title)
@section('page-title', 'إتمام الشراء')

@section('page')
    @php
        $image = $type === 'course' ? $item->thumbnail_path : $item->cover_path;
        $balance = (float) auth()->user()->balance;
        $price = $item->effectivePrice();
    @endphp

    <div class="grid gap-6 lg:grid-cols-[1fr_20rem]" x-data="{ method: '{{ old('payment_method', 'manual_vodafone') }}' }">

        {{-- طرق الدفع --}}
        <form method="POST" action="{{ route('student.checkout.store', [$type, $item->id]) }}"
              enctype="multipart/form-data" class="space-y-4">
            @csrf

            @error('payment_method')
                <div class="card-pad border-rose-300 text-sm font-semibold text-rose-600 dark:border-rose-500/40 dark:text-rose-400">{{ $message }}</div>
            @enderror

            {{-- المحفظة --}}
            <label class="card-pad block cursor-pointer transition"
                   :class="method === 'wallet' ? 'ring-2 ring-brand-500' : ''">
                <div class="flex items-center justify-between gap-3">
                    <span class="flex items-center gap-3">
                        <input type="radio" name="payment_method" value="wallet" x-model="method" class="accent-brand-600">
                        <span class="font-extrabold text-slate-900 dark:text-white">💳 الدفع من المحفظة (فوري)</span>
                    </span>
                    <span class="{{ $balance >= $price ? 'badge-green' : 'badge-red' }}">رصيدك: {{ egp($balance) }}</span>
                </div>
                <p class="mt-2 pr-7 text-sm text-slate-500 dark:text-slate-400">
                    @if ($balance >= $price)
                        هيتم خصم {{ egp($price) }} من رصيدك ويتفعل الاشتراك فوراً.
                    @else
                        رصيدك مش كفاية — اشحن كود سنتر من صفحة <a href="{{ route('student.charge') }}" class="font-bold text-brand-600 hover:underline">شحن كود</a> أو ادفع بطريقة تانية.
                    @endif
                </p>
            </label>

            {{-- فودافون كاش --}}
            <label class="card-pad block cursor-pointer transition"
                   :class="method === 'manual_vodafone' ? 'ring-2 ring-brand-500' : ''">
                <span class="flex items-center gap-3">
                    <input type="radio" name="payment_method" value="manual_vodafone" x-model="method" class="accent-brand-600">
                    <span class="font-extrabold text-slate-900 dark:text-white">📱 فودافون كاش / محافظ الموبايل</span>
                </span>
                <p class="mt-2 pr-7 text-sm leading-7 text-slate-600 dark:text-slate-300">
                    حوّل مبلغ <b>{{ egp($price) }}</b> على الرقم
                    <b dir="ltr" class="rounded-lg bg-slate-100 px-2 py-0.5 font-mono text-brand-700 dark:bg-slate-800 dark:text-brand-300">{{ setting('pay.vodafone', '01003878666') }}</b>
                    وبعدين ارفع صورة (سكرين شوت) إيصال التحويل تحت.
                </p>
            </label>

            {{-- انستا باي --}}
            <label class="card-pad block cursor-pointer transition"
                   :class="method === 'manual_instapay' ? 'ring-2 ring-brand-500' : ''">
                <span class="flex items-center gap-3">
                    <input type="radio" name="payment_method" value="manual_instapay" x-model="method" class="accent-brand-600">
                    <span class="font-extrabold text-slate-900 dark:text-white">🏦 انستا باي (InstaPay)</span>
                </span>
                <p class="mt-2 pr-7 text-sm leading-7 text-slate-600 dark:text-slate-300">
                    حوّل مبلغ <b>{{ egp($price) }}</b> على الرقم
                    <b dir="ltr" class="rounded-lg bg-slate-100 px-2 py-0.5 font-mono text-brand-700 dark:bg-slate-800 dark:text-brand-300">{{ setting('pay.instapay', '01003878666') }}</b>
                    وبعدين ارفع صورة إيصال التحويل تحت.
                </p>
            </label>

            {{-- بيانات التحويل اليدوي --}}
            <div x-show="method !== 'wallet'" x-cloak class="card-pad space-y-4">
                <div>
                    <label class="label" for="receipt">صورة إيصال التحويل (سكرين شوت) *</label>
                    <input type="file" name="receipt" id="receipt" accept="image/jpeg,image/png,image/webp" class="input">
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">صيغ مقبولة: JPG / PNG / WEBP — بحد أقصى 5 ميجا.</p>
                    @error('receipt') <p class="error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="label" for="sender_phone">الرقم اللي حولت منه (اختياري)</label>
                    <input type="text" name="sender_phone" id="sender_phone" value="{{ old('sender_phone') }}"
                           placeholder="01xxxxxxxxx" dir="ltr" class="input text-left">
                    @error('sender_phone') <p class="error">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- كود الخصم --}}
            <div class="card-pad">
                <label class="label" for="coupon_code">كود خصم (لو معاك)</label>
                <input type="text" name="coupon_code" id="coupon_code" value="{{ old('coupon_code') }}"
                       placeholder="مثال: SAVE20" class="input font-mono uppercase">
                @error('coupon_code') <p class="error">{{ $message }}</p> @enderror
            </div>

            <button type="submit" class="btn-primary w-full py-3 text-base">
                <span x-show="method === 'wallet'">ادفع {{ egp($price) }} من المحفظة</span>
                <span x-show="method !== 'wallet'" x-cloak>تأكيد الطلب وإرسال الإيصال</span>
            </button>

            <p class="text-center text-xs text-slate-500 dark:text-slate-400">
                طلبات التحويل اليدوي بتتراجع من الإدارة وبيتم التفعيل خلال ساعات من رفع الإيصال.
            </p>
        </form>

        {{-- ملخص الطلب --}}
        <aside class="lg:order-first lg:col-start-2">
            <div class="card overflow-hidden lg:sticky lg:top-24">
                @if ($image)
                    <img src="{{ $image }}" alt="{{ $item->title }}" class="aspect-video w-full object-cover">
                @else
                    <div class="grid aspect-video w-full place-items-center bg-brand-50 text-4xl dark:bg-slate-800">{{ $type === 'course' ? '🎓' : '📕' }}</div>
                @endif

                <div class="space-y-3 p-5">
                    <span class="badge-sky">{{ $type === 'course' ? 'كورس' : 'كتاب' }}</span>
                    <h2 class="text-lg font-extrabold text-slate-900 dark:text-white">{{ $item->title }}</h2>

                    <div class="flex flex-wrap items-baseline gap-2 border-t border-slate-300/60 pt-3 dark:border-slate-800">
                        <span class="text-2xl font-black text-brand-600 dark:text-brand-300">{{ egp($price) }}</span>
                        @if ($item->discountPercent())
                            <s class="text-sm font-semibold text-slate-400">{{ egp($item->price) }}</s>
                            <span class="badge-green">وفر {{ $item->discountPercent() }}%</span>
                        @endif
                    </div>
                </div>
            </div>
        </aside>
    </div>
@endsection
