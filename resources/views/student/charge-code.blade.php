@extends('layouts.student')

@section('title', 'شحن كود سنتر — ' . setting('site.name', 'منصة حاتم سميكة'))
@section('page-title', 'شحن كود سنتر')

@section('page')
    <div class="mx-auto max-w-2xl pt-6 text-center">
        <h2 class="text-3xl font-black text-slate-900 dark:text-white sm:text-4xl">شحن كود سنتر</h2>
        <p class="mx-auto mt-5 max-w-lg text-base font-bold leading-8 text-slate-600 dark:text-slate-300">
            اكتب الكود المكون من ١٥ رقم على كارت السنتر بتاعك هنا عشان تشحنه على الأكونت بتاعك
        </p>

        <form method="POST" action="{{ route('student.charge.submit') }}" class="mt-14">
            @csrf

            <div class="relative mx-auto max-w-md">
                <label for="code" class="sr-only">كود السنتر</label>
                <input id="code" name="code" type="text" value="{{ old('code') }}"
                       class="input-underline" maxlength="20"
                       placeholder="كود السنتر الخاص بك" autocomplete="off" autofocus required>
                <svg class="pointer-events-none absolute left-1 top-1/2 size-5 -translate-y-1/2 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z"/><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75zM6.75 16.5h.75v.75h-.75v-.75zM16.5 6.75h.75v.75h-.75v-.75zM13.5 13.5h.75v.75h-.75v-.75zM13.5 19.5h.75v.75h-.75v-.75zM19.5 13.5h.75v.75h-.75v-.75zM19.5 19.5h.75v.75h-.75v-.75zM16.5 16.5h.75v.75h-.75v-.75z"/></svg>
                @error('code')<p class="error text-center">{{ $message }}</p>@enderror
            </div>

            <button type="submit"
                    class="mt-12 inline-flex items-center justify-center rounded-lg bg-brand-600 px-10 py-4 text-base font-black text-white shadow-md transition hover:-translate-y-0.5 hover:bg-brand-700 active:scale-[.98]">
                شحن الكود
            </button>
        </form>

        <p class="mt-10 text-xs font-semibold text-slate-400 dark:text-slate-500">
            الكود بيتستخدم مرة واحدة بس — لو ظهرلك إن الكود مستخدم أو غلط، كلم الدعم.
        </p>
    </div>
@endsection
