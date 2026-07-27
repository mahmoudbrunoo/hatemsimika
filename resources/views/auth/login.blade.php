@extends('layouts.app')

@section('title', 'تسجيل الدخول — ' . setting('site.name', 'منصة حاتم سميكة'))

@section('content')
    <section class="mx-auto max-w-md px-4 py-12 sm:px-6 lg:py-16">
        <div class="text-center">
            <div class="mx-auto grid size-14 place-items-center rounded-2xl bg-brand-50 text-2xl dark:bg-brand-500/10">👋</div>
            <h1 class="mt-4 text-2xl font-black text-slate-900 dark:text-white">أهلاً بيك من تاني!</h1>
            <p class="mt-2 text-sm font-semibold text-slate-500 dark:text-slate-400">
                سجل دخولك وكمل مذاكرتك من المكان اللي وقفت عنده.
            </p>
        </div>

        <div class="card-pad mt-8">
            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="login" class="label">البريد الإلكتروني أو رقم الموبايل</label>
                    <input id="login" name="login" type="text" value="{{ old('login') }}"
                           class="input" placeholder="example@mail.com أو 01XXXXXXXXX"
                           autocomplete="username" autofocus required>
                    @error('login')<p class="error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <div class="mb-1.5 flex items-center justify-between">
                        <label for="password" class="label mb-0">كلمة المرور</label>
                    </div>
                    <input id="password" name="password" type="password"
                           class="input" placeholder="••••••••"
                           autocomplete="current-password" required>
                    @error('password')<p class="error">{{ $message }}</p>@enderror
                </div>

                <label for="remember" class="flex cursor-pointer items-center gap-2 text-sm font-semibold text-slate-600 dark:text-slate-300">
                    <input id="remember" name="remember" type="checkbox" value="1" @checked(old('remember'))
                           class="size-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500/40 dark:border-slate-700 dark:bg-slate-950">
                    افتكرني على الجهاز ده
                </label>

                <button type="submit" class="btn-primary w-full py-3 text-base">تسجيل الدخول</button>
            </form>
        </div>

        <p class="mt-6 text-center text-sm font-semibold text-slate-500 dark:text-slate-400">
            لسه معندكش حساب؟
            <a href="{{ route('register') }}" class="font-extrabold text-brand-600 hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300">
                ابدأ رحلتك الآن
            </a>
        </p>
    </section>
@endsection
