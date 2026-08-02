@extends('layouts.app')

@section('title', 'المساعدة والأسئلة الشائعة')

@section('content')
    <section class="mx-auto max-w-3xl px-4 py-10 sm:px-6">
        <h1 class="text-3xl font-black text-slate-900 dark:text-white">مركز المساعدة</h1>
        <p class="mt-2 font-semibold text-slate-500 dark:text-slate-400">
            إجابات أشهر الأسئلة — ولو محتاج مساعدة أكتر كلمنا واتساب أو استخدم المساعد الذكي أسفل الصفحة.
        </p>

        <div class="mt-8 space-y-3">
            @forelse ($faqs as $faq)
                <div x-data="{ open: false }" class="card overflow-hidden">
                    <button type="button" @click="open = !open" class="flex w-full items-center justify-between gap-4 p-5 text-right">
                        <span class="font-extrabold text-slate-900 dark:text-white">{{ $faq->question }}</span>
                        <svg class="size-5 shrink-0 text-slate-400 transition" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="open" x-transition x-cloak class="border-t border-slate-300/60 px-5 py-4 leading-8 text-slate-600 dark:border-slate-800 dark:text-slate-300">
                        {{ $faq->answer }}
                    </div>
                </div>
            @empty
                <div class="card-pad text-center text-slate-500 dark:text-slate-400">لا توجد أسئلة شائعة بعد.</div>
            @endforelse
        </div>
    </section>
@endsection
