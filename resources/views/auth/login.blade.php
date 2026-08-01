@extends('layouts.app')

@section('title', 'تسجيل الدخول — ' . setting('site.name', 'منصة حاتم سميكة'))

@section('content')
    <div class="auth-login relative z-10 -mt-[5.4rem] flex flex-col space-y-10 lg:min-h-screen lg:flex-row lg:space-y-0">

        {{-- الجانب الأيمن — صورة --}}
        <div class="min-w-0 lg:basis-2/5">
            <div class="relative h-64 w-full overflow-hidden lg:h-full"
                 @if (setting_image('login.image'))
                     style="background-image: url('{{ setting_image('login.image') }}'); background-size: cover; background-position: center top;"
                 @endif>
                @unless (setting_image('login.image'))
                    <div class="absolute inset-0 h-full w-full bg-gradient-to-br from-flame-500 to-flame-700"></div>
                @endunless
                <div class="form-img-cover smooth absolute inset-0 h-full w-full opacity-0 dark:opacity-70"></div>
                <div class="smooth absolute inset-0 block h-full w-full bg-slate-900 opacity-50 lg:hidden"></div>
            </div>
        </div>

        {{-- الجانب الأيسر — الفورم --}}
        <div class="flex items-center justify-center lg:basis-3/5">
            <div class="font-taj relative flex h-4/5 w-4/5 flex-col justify-center space-y-12 py-10">

                {{-- العنوان --}}
                <div class="relative text-center text-xl font-bold md:text-2xl">
                    <div class="flex flex-row flex-wrap gap-x-2">
                        <span>تسجيل</span>
                        <span class="text-yellow-700 dark:text-yellow-400">الدخول :</span>
                    </div>
                    <span class="absolute inset-y-0 -right-2 flex w-8 translate-x-full transform items-center justify-center text-yellow-700 dark:text-yellow-400">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1.5em" height="1.5em" viewBox="0 0 24 24" aria-hidden="true">
                            <path fill="currentColor" d="M20.5 6c-2.61.7-5.67 1-8.5 1s-5.89-.3-8.5-1L3 8c1.86.5 4 .83 6 1v13h2v-6h2v6h2V9c2-.17 4.14-.5 6-1zM12 6c1.1 0 2-.9 2-2s-.9-2-2-2s-2 .9-2 2s.9 2 2 2"/>
                        </svg>
                    </span>
                </div>

                {{-- الوصف --}}
                <div class="text-[0.938rem] font-normal text-gray-500 md:text-base dark:text-gray-400">
                    ادخل على حسابك بإدخال البريد الإلكتروني أو رقم الهاتف وكلمة المرور
                </div>

                {{-- رابط إنشاء حساب --}}
                <div class="flex flex-row flex-wrap gap-x-2 text-[0.938rem] font-normal text-gray-500 md:text-base dark:text-gray-400">
                    <span>لا يوجد لديك حساب؟</span>
                    <a href="{{ route('register') }}">
                        <span class="smooth text-sky-500 dark:text-sky-400">انشئ حسابك الآن !</span>
                    </a>
                </div>

                {{-- الفورم --}}
                <div>
                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="space-y-6">
                            <div class="space-y-12">

                                <div class="form-reg__group @error('login') has-error @enderror">
                                    <input id="login" name="login" type="text" value="{{ old('login') }}"
                                           class="smooth z-10" autocomplete="username" autofocus required>
                                    <span class="bg"></span>
                                    <span class="highlight"></span>
                                    <span class="bar"></span>
                                    <label for="login">
                                        <div class="flex flex-row flex-wrap items-center gap-x-2">
                                            <span class="flex -translate-y-px transform items-center justify-center text-cyan-500">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 1024 1024" aria-hidden="true">
                                                    <path fill="currentColor" d="M885.6 230.2L779.1 123.8a80.83 80.83 0 0 0-57.3-23.8c-21.7 0-42.1 8.5-57.4 23.8L549.8 238.4a80.83 80.83 0 0 0-23.8 57.3c0 21.7 8.5 42.1 23.8 57.4l83.8 83.8A393.8 393.8 0 0 1 553.1 553A395.3 395.3 0 0 1 437 633.8L353.2 550a80.83 80.83 0 0 0-57.3-23.8c-21.7 0-42.1 8.5-57.4 23.8L123.8 664.5a80.9 80.9 0 0 0-23.8 57.4c0 21.7 8.5 42.1 23.8 57.4l106.3 106.3c24.4 24.5 58.1 38.4 92.7 38.4c7.3 0 14.3-.6 21.2-1.8c134.8-22.2 268.5-93.9 376.4-201.7C828.2 612.8 899.8 479.2 922.3 344c6.8-41.3-6.9-83.8-36.7-113.8"/>
                                                </svg>
                                            </span>
                                            <span>البريد الإلكتروني أو رقم الموبايل</span>
                                        </div>
                                    </label>
                                    @error('login')<p class="error">{{ $message }}</p>@enderror
                                </div>

                                <div class="form-reg__group @error('password') has-error @enderror">
                                    <input id="password" name="password" type="password"
                                           class="smooth z-10" autocomplete="current-password" required>
                                    <span class="bg"></span>
                                    <span class="highlight"></span>
                                    <span class="bar"></span>
                                    <label for="password">
                                        <div class="flex flex-row flex-wrap items-center gap-x-2">
                                            <span class="flex -translate-y-px transform items-center justify-center text-cyan-500">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path fill="currentColor" d="M18 8h2a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V9a1 1 0 0 1 1-1h2V7a6 6 0 1 1 12 0zm-2 0V7a4 4 0 0 0-8 0v1zm-5 6v2h2v-2zm-4 0v2h2v-2zm8 0v2h2v-2z"/>
                                                </svg>
                                            </span>
                                            <span>كلمة السر</span>
                                        </div>
                                    </label>
                                    @error('password')<p class="error">{{ $message }}</p>@enderror
                                </div>

                                <label for="remember"
                                       class="flex w-fit cursor-pointer items-center gap-2 text-[0.938rem] text-gray-500 md:text-base dark:text-gray-400">
                                    <input id="remember" name="remember" type="checkbox" value="1" @checked(old('remember'))
                                           class="size-4 rounded border-gray-300 text-cyan-500 accent-cyan-500 focus:ring-cyan-500/40 dark:border-night-700 dark:bg-night-950">
                                    افتكرني على الجهاز ده
                                </label>

                            </div>

                            <div class="flex flex-wrap items-center gap-4">
                                <button type="submit"
                                        class="smooth rounded-md border-2 border-yellow-400 bg-yellow-400 px-4 py-3 text-white hover:bg-transparent hover:text-yellow-400 dark:border-yellow-500 dark:bg-yellow-500 dark:hover:bg-transparent dark:hover:text-yellow-500">
                                    تسجيل الدخول
                                </button>

                                {{-- زر فيديو شرح التسجيل — يظهر فقط لو الأدمن ضبط الرابط --}}
                                @if (setting('auth.video_url'))
                                    <a href="{{ setting('auth.video_url') }}" target="_blank" rel="noopener"
                                       class="smooth inline-flex items-center gap-2 rounded-md border-2 border-red-500 px-4 py-3 font-bold text-red-500 hover:bg-red-500 hover:text-white dark:text-red-400 dark:hover:bg-red-500 dark:hover:text-white">
                                        <svg class="size-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 14.5v-9l6 4.5-6 4.5z"/></svg>
                                        {{ setting('auth.video_label', 'شاهد طريقة التسجيل') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>

                {{-- رابط إنشاء حساب — أسفل الصفحة --}}
                <div class="flex flex-row flex-wrap gap-x-2 text-[0.938rem] font-normal text-gray-500 md:text-base dark:text-gray-400">
                    <span>لا يوجد لديك حساب؟</span>
                    <a href="{{ route('register') }}">
                        <span class="smooth text-sky-500 dark:text-sky-400">انشئ حسابك الآن !</span>
                    </a>
                </div>

            </div>
        </div>
    </div>
@endsection
