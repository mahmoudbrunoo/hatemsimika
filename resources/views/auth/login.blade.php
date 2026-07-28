@extends('layouts.app')

@section('title', 'تسجيل الدخول — ' . setting('site.name', 'منصة حاتم سميكة'))

@section('content')
    <div class="-mt-[5.4rem] flex min-h-screen flex-col lg:flex-row">

        {{-- الجانب الأيسر — صورة --}}
        <div class="h-56 shrink-0 overflow-hidden lg:h-auto lg:w-2/5">
            @if (setting_image('login.image'))
                <img src="{{ setting_image('login.image') }}" alt="تسجيل الدخول"
                     class="h-full w-full object-cover object-top">
            @else
                <div class="h-full w-full bg-gradient-to-br from-flame-500 to-flame-700"></div>
            @endif
        </div>

        {{-- الجانب الأيمن — الفورم --}}
        <div class="flex flex-1 items-center justify-center px-6 py-16 lg:py-24">
            <div class="w-full max-w-md space-y-8">

                {{-- العنوان --}}
                <div>
                    <h1 class="flex flex-wrap items-baseline gap-2 text-2xl font-black text-slate-900 dark:text-white">
                        <span>تسجيل</span>
                        <span class="text-amber-600 dark:text-amber-400">الدخول :</span>
                    </h1>
                    <p class="mt-2 text-sm font-semibold text-slate-500 dark:text-slate-400">
                        ادخل على حسابك بإدخال البريد الإلكتروني أو رقم الهاتف وكلمة المرور
                    </p>
                    <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">
                        لا يوجد لديك حساب؟
                        <a href="{{ route('register') }}" class="font-extrabold text-flame-600 hover:text-flame-700 dark:text-flame-400">
                            انشئ حسابك الآن !
                        </a>
                    </p>
                </div>

                {{-- الفورم --}}
                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf

                    <div>
                        <label for="login" class="label">البريد الإلكتروني أو رقم الموبايل</label>
                        <input id="login" name="login" type="text" value="{{ old('login') }}"
                               class="input" placeholder="example@mail.com أو 01XXXXXXXXX"
                               autocomplete="username" autofocus required>
                        @error('login')<p class="error">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="password" class="label">كلمة السر</label>
                        <input id="password" name="password" type="password"
                               class="input" placeholder="••••••••"
                               autocomplete="current-password" required>
                        @error('password')<p class="error">{{ $message }}</p>@enderror
                    </div>

                    <label for="remember" class="flex cursor-pointer items-center gap-2 text-sm font-semibold text-slate-600 dark:text-slate-300">
                        <input id="remember" name="remember" type="checkbox" value="1" @checked(old('remember'))
                               class="size-4 rounded border-slate-300 text-flame-600 focus:ring-flame-500/40 dark:border-slate-700 dark:bg-slate-950">
                        افتكرني على الجهاز ده
                    </label>

                    <button type="submit"
                            class="w-full rounded-md border-2 border-amber-400 bg-amber-400 py-3 text-base font-black text-white transition hover:bg-transparent hover:text-amber-500 dark:border-amber-500 dark:bg-amber-500 dark:hover:bg-transparent dark:hover:text-amber-400">
                        تسجيل الدخول
                    </button>
                </form>

                <p class="text-center text-sm font-semibold text-slate-500 dark:text-slate-400">
                    لا يوجد لديك حساب؟
                    <a href="{{ route('register') }}" class="font-extrabold text-flame-600 hover:text-flame-700 dark:text-flame-400">
                        انشئ حسابك الآن !
                    </a>
                </p>
            </div>
        </div>
    </div>
@endsection
