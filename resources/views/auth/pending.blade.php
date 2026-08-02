@extends('layouts.app')

@section('title', 'حالة الحساب — ' . setting('site.name', 'منصة حاتم سميكة'))

@section('content')
    @php
        $user = auth()->user();
        $isRejected = $user->status === \App\Models\User::STATUS_REJECTED;
        $whatsapp = setting('support.whatsapp', '01003878666');
        $whatsappLink = 'https://wa.me/2' . preg_replace('/\D/', '', $whatsapp);
    @endphp

    <section class="mx-auto max-w-xl px-4 py-12 sm:px-6 lg:py-16">
        <div class="card-pad text-center">
            <div class="mx-auto grid size-16 place-items-center rounded-2xl {{ $isRejected ? 'bg-rose-50 dark:bg-rose-500/10' : 'bg-amber-50 dark:bg-amber-500/10' }} text-3xl">
                {{ $isRejected ? '📋' : '⏳' }}
            </div>

            <div class="mt-4">
                <span class="{{ $isRejected ? 'badge-red' : 'badge-amber' }}">{{ $user->statusLabel() }}</span>
            </div>

            @if ($isRejected)
                <h1 class="mt-3 text-2xl font-black text-slate-900 dark:text-white">للأسف، طلب تسجيلك اترفض</h1>
                <p class="mt-2 leading-8 text-slate-600 dark:text-slate-300">
                    أهلاً {{ $user->shortName() }} — راجعنا بياناتك ولقينا مشكلة منعتنا نفعّل الحساب.
                    متقلقش، الموضوع بيتحل بسهولة لما تتواصل معانا.
                </p>

                @if ($user->rejection_reason)
                    <div class="mt-5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-right text-sm font-bold text-rose-700 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-300">
                        <span class="block text-xs font-semibold text-rose-500 dark:text-rose-400">سبب الرفض:</span>
                        <p class="mt-1 leading-7">{{ $user->rejection_reason }}</p>
                    </div>
                @endif

                <p class="mt-5 text-sm font-semibold leading-7 text-slate-500 dark:text-slate-400">
                    ابعتلنا على الواتساب وهنساعدك تظبط بياناتك ونفعّل حسابك في أسرع وقت.
                </p>
            @else
                <h1 class="mt-3 text-2xl font-black text-slate-900 dark:text-white">حسابك قيد المراجعة</h1>
                <p class="mt-2 leading-8 text-slate-600 dark:text-slate-300">
                    أهلاً {{ $user->shortName() }} 👋 — وصلنا طلب تسجيلك بنجاح،
                    وفريقنا بيراجع بياناتك وصورة البطاقة دلوقتي.
                </p>

                <div class="mt-6 space-y-3 text-right">
                    @foreach ([
                        ['1', 'بنراجع بياناتك', 'بنتأكد إن الاسم والرقم القومي وصورة البطاقة سليمين.'],
                        ['2', 'بنفعّل حسابك', 'المراجعة عادة بتخلص خلال 24 ساعة على الأكثر.'],
                        ['3', 'تبدأ رحلتك', 'أول ما الحساب يتفعّل هتقدر تدخل لوحة التعلم وتشترك في كورساتك.'],
                    ] as [$step, $stepTitle, $stepText])
                        <div class="flex items-start gap-3 rounded-2xl bg-slate-50 p-4 dark:bg-slate-950/50">
                            <span class="grid size-8 shrink-0 place-items-center rounded-xl bg-brand-50 text-sm font-black text-brand-600 dark:bg-brand-500/10 dark:text-brand-300">{{ $step }}</span>
                            <div>
                                <h3 class="text-sm font-extrabold text-slate-900 dark:text-white">{{ $stepTitle }}</h3>
                                <p class="mt-0.5 text-sm leading-6 text-slate-500 dark:text-slate-400">{{ $stepText }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="mt-7 border-t border-slate-300/60 pt-6 dark:border-slate-800">
                <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">
                    محتاج مساعدة أو استعجلت التفعيل؟ كلمنا واتساب على
                    <span dir="ltr" class="font-extrabold text-slate-700 dark:text-slate-200">{{ $whatsapp }}</span>
                </p>
                <div class="mt-4 flex flex-wrap justify-center gap-3">
                    <a href="{{ $whatsappLink }}" target="_blank" rel="noopener" class="btn-success px-5">
                        تواصل معانا واتساب
                    </a>
                    <a href="{{ route('courses.index') }}" class="btn-secondary px-5">
                        تصفح الكورسات لحد ما نخلص
                    </a>
                </div>
            </div>
        </div>
    </section>
@endsection
