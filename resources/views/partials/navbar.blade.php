@php
    // منطقة الطالب/الأدمن = هيدر بسطتهالك الداكن | الصفحات العامة = هيدر خالد صقر العائم الأبيض
    $dash = auth()->check() && (request()->is('me') || request()->is('me/*') || request()->is('admin') || request()->is('admin/*'));
@endphp

@if ($dash)
    {{-- ============================ هيدر منطقة الطالب — نمط بسطتهالك ============================ --}}
    <header class="sticky top-0 z-40 border-b border-slate-300 bg-surface/95 backdrop-blur dark:border-night-800 dark:bg-night-950/95"
            x-data="{ open: false, menu: false, bell: false }">
        <nav class="relative mx-auto flex h-[4.25rem] max-w-7xl items-center gap-3 px-4 sm:px-6">

            {{-- الشعار --}}
            <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-2">
                @if (setting_image('site.logo'))
                    <img src="{{ setting_image('site.logo') }}" alt="{{ setting('site.name', config('app.name')) }}" class="h-11 w-auto">
                @else
                    <span class="grid size-10 place-items-center rounded-xl bg-brand-500 text-lg font-black text-white">{{ mb_substr(setting('site.name', 'س'), 0, 1) }}</span>
                @endif
            </a>

            {{-- مفتاح الوضع الليلي --}}
            <button type="button" @click="$store.theme.toggle()" class="theme-pill" role="switch" :aria-checked="$store.theme.dark" title="الوضع الليلي / النهاري">
                <svg class="theme-pill-sun" width="24" height="24" fill="none" viewBox="0 0 24 24"><path d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M12 4v1M18 6l-1 1M20 12h-1M18 18l-1-1M12 19v1M7 17l-1 1M5 12H4M7 7 6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <svg class="theme-pill-moon" width="24" height="24" fill="none" viewBox="0 0 24 24"><path d="M18 15.63c-.977.52-1.945.481-3.13.481A6.981 6.981 0 0 1 7.89 9.13c0-1.185-.04-2.153.481-3.13C6.166 7.174 5 9.347 5 12.018A6.981 6.981 0 0 0 11.982 19c2.67 0 4.844-1.166 6.018-3.37ZM16 5c0 2.08-.96 4-3 4 2.04 0 3 .92 3 3 0-2.08.96-3 3-3-2.04 0-3-1.92-3-4Z" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                <span class="theme-pill-knob">
                    <svg class="theme-pill-knob-sun" width="24" height="24" fill="none" viewBox="0 0 24 24"><path d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M12 4v1M18 6l-1 1M20 12h-1M18 18l-1-1M12 19v1M7 17l-1 1M5 12H4M7 7 6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <svg class="theme-pill-knob-moon" width="24" height="24" fill="none" viewBox="0 0 24 24"><path d="M18 15.63c-.977.52-1.945.481-3.13.481A6.981 6.981 0 0 1 7.89 9.13c0-1.185-.04-2.153.481-3.13C6.166 7.174 5 9.347 5 12.018A6.981 6.981 0 0 0 11.982 19c2.67 0 4.844-1.166 6.018-3.37ZM16 5c0 2.08-.96 4-3 4 2.04 0 3 .92 3 3 0-2.08.96-3 3-3-2.04 0-3-1.92-3-4Z" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </span>
            </button>

            <div class="flex-1"></div>

            {{-- بحث في الكورسات --}}
            <a href="{{ route('courses.index') }}" title="تصفح الكورسات"
               class="grid size-10 place-items-center rounded-full text-slate-500 transition hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-night-800 dark:hover:text-white">
                <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            </a>

            {{-- كبسولة المحفظة --}}
            <a href="{{ route('student.wallet') }}" class="wallet-pill" title="محفظتي">
                <span class="grid size-8 place-items-center rounded-full bg-night-900 text-white dark:bg-night-900">
                    <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 003 12m18 0v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 9m18 0V6a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v3"/></svg>
                </span>
                <span>{{ egp(auth()->user()->balance ?? 0) }}</span>
            </a>

            {{-- الإشعارات --}}
            <div class="relative" @click.outside="bell = false">
                <button type="button" @click="bell = !bell" title="الإشعارات"
                        class="grid size-10 place-items-center rounded-full text-slate-500 transition hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-night-800 dark:hover:text-white">
                    <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/></svg>
                </button>
                <div x-show="bell" x-cloak x-transition
                     class="absolute left-0 mt-2 w-64 rounded-2xl border border-slate-300 bg-surface p-4 text-center text-sm font-semibold text-slate-500 shadow-xl dark:border-night-700 dark:bg-night-850 dark:text-slate-300">
                    لا توجد إشعارات جديدة حالياً
                </div>
            </div>

            {{-- قائمة المستخدم --}}
            <div class="relative" @click.outside="menu = false">
                <button type="button" @click="menu = !menu" title="حسابي" class="flex items-center">
                    @if (auth()->user()->avatar_path)
                        <img src="{{ auth()->user()->avatar_path }}" class="size-10 rounded-full object-cover ring-2 ring-slate-300 dark:ring-night-700" alt="">
                    @else
                        <span class="grid size-10 place-items-center rounded-full bg-rose-300/80 text-sm font-extrabold text-white ring-2 ring-slate-300 dark:ring-night-700">
                            {{ mb_substr(auth()->user()->name, 0, 1) }}
                        </span>
                    @endif
                </button>

                <div x-show="menu" x-cloak x-transition
                     class="absolute left-0 mt-2 w-56 overflow-hidden rounded-2xl border border-slate-300 bg-surface py-2 shadow-xl dark:border-night-700 dark:bg-night-850">
                    <p class="px-4 py-2 text-sm font-extrabold text-slate-900 dark:text-white">{{ auth()->user()->shortName() }}</p>
                    <div class="my-1 border-t border-slate-300/60 dark:border-night-800"></div>
                    @if (auth()->user()->isStaff())
                        <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2.5 text-sm font-bold text-brand-600 hover:bg-slate-50 dark:text-brand-300 dark:hover:bg-night-800">لوحة التحكم</a>
                        <div class="my-1 border-t border-slate-300/60 dark:border-night-800"></div>
                    @endif
                    <a href="{{ route('student.dashboard') }}" class="block px-4 py-2.5 text-sm font-semibold hover:bg-slate-50 dark:hover:bg-night-800">الرئيسية</a>
                    <a href="{{ route('student.profile') }}" class="block px-4 py-2.5 text-sm font-semibold hover:bg-slate-50 dark:hover:bg-night-800">ملف المستخدم</a>
                    <a href="{{ route('student.courses') }}" class="block px-4 py-2.5 text-sm font-semibold hover:bg-slate-50 dark:hover:bg-night-800">كورساتي</a>
                    <a href="{{ route('student.invoices') }}" class="block px-4 py-2.5 text-sm font-semibold hover:bg-slate-50 dark:hover:bg-night-800">الفواتير</a>
                    <div class="my-1 border-t border-slate-300/60 dark:border-night-800"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="block w-full px-4 py-2.5 text-right text-sm font-semibold text-rose-600 hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-500/10">
                            تسجيل الخروج
                        </button>
                    </form>
                </div>
            </div>

            {{-- شريط تقدم التمرير --}}
            <span class="scroll-progress scroll-progress-sky"></span>
        </nav>
    </header>
@else
    {{-- ============================ الهيدر العام — نمط خالد صقر العائم ============================ --}}
    <header class="sticky top-0 z-40 px-3 pt-3 sm:px-5" x-data="{ open: false, menu: false }">
        <nav class="relative mx-auto flex h-[4.5rem] max-w-[90rem] flex-row-reverse items-center justify-between gap-3 rounded-full md:rounded-xl border border-slate-300/60 bg-surface px-4 shadow-xl dark:border-night-800 dark:bg-night-900 sm:px-6">

            {{-- الشعار + مفتاح الوضع (يسار فعلي كما في المرجع) --}}
            <div class="flex items-center gap-4">
                <a href="{{ route('home') }}" class="flex items-center gap-2">
                    @if (setting_image('site.logo'))
                        <img src="{{ setting_image('site.logo') }}" alt="{{ setting('site.name', config('app.name')) }}" class="h-12 w-auto">
                    @else
                        <span class="grid size-11 place-items-center rounded-xl bg-flame-600 text-xl font-black text-white">{{ mb_substr(setting('site.name', 'س'), 0, 1) }}</span>
                        <span class="hidden text-lg font-black text-slate-900 dark:text-white sm:block">{{ setting('site.name', 'منصة حاتم سميكة') }}</span>
                    @endif
                </a>

                <button type="button" @click="$store.theme.toggle()" class="theme-pill" role="switch" :aria-checked="$store.theme.dark" title="الوضع الليلي / النهاري">
                    <svg class="theme-pill-sun" width="24" height="24" fill="none" viewBox="0 0 24 24"><path d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M12 4v1M18 6l-1 1M20 12h-1M18 18l-1-1M12 19v1M7 17l-1 1M5 12H4M7 7 6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <svg class="theme-pill-moon" width="24" height="24" fill="none" viewBox="0 0 24 24"><path d="M18 15.63c-.977.52-1.945.481-3.13.481A6.981 6.981 0 0 1 7.89 9.13c0-1.185-.04-2.153.481-3.13C6.166 7.174 5 9.347 5 12.018A6.981 6.981 0 0 0 11.982 19c2.67 0 4.844-1.166 6.018-3.37ZM16 5c0 2.08-.96 4-3 4 2.04 0 3 .92 3 3 0-2.08.96-3 3-3-2.04 0-3-1.92-3-4Z" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span class="theme-pill-knob">
                        <svg class="theme-pill-knob-sun" width="24" height="24" fill="none" viewBox="0 0 24 24"><path d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M12 4v1M18 6l-1 1M20 12h-1M18 18l-1-1M12 19v1M7 17l-1 1M5 12H4M7 7 6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <svg class="theme-pill-knob-moon" width="24" height="24" fill="none" viewBox="0 0 24 24"><path d="M18 15.63c-.977.52-1.945.481-3.13.481A6.981 6.981 0 0 1 7.89 9.13c0-1.185-.04-2.153.481-3.13C6.166 7.174 5 9.347 5 12.018A6.981 6.981 0 0 0 11.982 19c2.67 0 4.844-1.166 6.018-3.37ZM16 5c0 2.08-.96 4-3 4 2.04 0 3 .92 3 3 0-2.08.96-3 3-3-2.04 0-3-1.92-3-4Z" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                </button>
            </div>

            {{-- روابط سطح المكتب (وسط) --}}
            <div class="hidden items-center gap-1 lg:flex">
                <a href="{{ route('home') }}" class="rounded-xl px-3.5 py-2 text-sm font-bold transition {{ request()->routeIs('home') ? 'text-flame-600' : 'text-slate-600 hover:text-flame-600 dark:text-slate-300 dark:hover:text-flame-400' }}">الرئيسية</a>
                <a href="{{ route('courses.index') }}" class="rounded-xl px-3.5 py-2 text-sm font-bold transition {{ request()->routeIs('courses.*') ? 'text-flame-600' : 'text-slate-600 hover:text-flame-600 dark:text-slate-300 dark:hover:text-flame-400' }}">الكورسات</a>
                <a href="{{ route('books.index') }}" class="rounded-xl px-3.5 py-2 text-sm font-bold transition {{ request()->routeIs('books.*') ? 'text-flame-600' : 'text-slate-600 hover:text-flame-600 dark:text-slate-300 dark:hover:text-flame-400' }}">الكتب</a>
                <a href="{{ route('help') }}" class="rounded-xl px-3.5 py-2 text-sm font-bold transition {{ request()->routeIs('help') ? 'text-flame-600' : 'text-slate-600 hover:text-flame-600 dark:text-slate-300 dark:hover:text-flame-400' }}">المساعدة</a>
            </div>

            {{-- أزرار الدخول (يمين فعلي كما في المرجع) --}}
            <div class="flex flex-row-reverse items-center gap-2.5">
                @guest
                    <a href="{{ route('register') }}" class="btn-flame rounded-full px-5 sm:px-7">حساب جديد</a>
                    <a href="{{ route('login') }}" class="btn-flame-outline hidden rounded-full px-5 sm:inline-flex sm:px-7">تسجيل الدخول</a>
                @endguest

                @auth
                    <div class="relative" @click.outside="menu = false">
                        <button type="button" @click="menu = !menu" class="flex items-center gap-2 rounded-full p-1 hover:bg-slate-100 dark:hover:bg-night-800">
                            @if (auth()->user()->avatar_path)
                                <img src="{{ auth()->user()->avatar_path }}" class="size-10 rounded-full object-cover" alt="">
                            @else
                                <span class="grid size-10 place-items-center rounded-full bg-flame-100 text-sm font-extrabold text-flame-700 dark:bg-flame-500/20 dark:text-flame-300">
                                    {{ mb_substr(auth()->user()->name, 0, 1) }}
                                </span>
                            @endif
                            <svg class="size-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </button>

                        <div x-show="menu" x-cloak x-transition
                             class="absolute left-0 mt-2 w-56 overflow-hidden rounded-2xl border border-slate-300 bg-surface py-2 shadow-xl dark:border-night-700 dark:bg-night-850">
                            @if (auth()->user()->isStaff())
                                <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2.5 text-sm font-bold text-flame-600 hover:bg-slate-50 dark:text-flame-400 dark:hover:bg-night-800">لوحة التحكم</a>
                                <div class="my-1 border-t border-slate-300/60 dark:border-night-800"></div>
                            @endif
                            @if (auth()->user()->isApproved() || auth()->user()->isStaff())
                                <a href="{{ route('student.dashboard') }}" class="block px-4 py-2.5 text-sm font-semibold hover:bg-slate-50 dark:hover:bg-night-800">لوحة التعلم</a>
                                <a href="{{ route('student.profile') }}" class="block px-4 py-2.5 text-sm font-semibold hover:bg-slate-50 dark:hover:bg-night-800">ملف المستخدم</a>
                                <a href="{{ route('student.courses') }}" class="block px-4 py-2.5 text-sm font-semibold hover:bg-slate-50 dark:hover:bg-night-800">كورساتي</a>
                            @else
                                <a href="{{ route('account.pending') }}" class="block px-4 py-2.5 text-sm font-semibold hover:bg-slate-50 dark:hover:bg-night-800">حالة الحساب</a>
                            @endif
                            <div class="my-1 border-t border-slate-300/60 dark:border-night-800"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="block w-full px-4 py-2.5 text-right text-sm font-semibold text-rose-600 hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-500/10">
                                    تسجيل الخروج
                                </button>
                            </form>
                        </div>
                    </div>
                @endauth

                {{-- زر القائمة للموبايل --}}
                <button type="button" @click="open = !open" class="grid size-10 place-items-center rounded-xl text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-night-800 lg:hidden">
                    <svg x-show="!open" class="size-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    <svg x-show="open" x-cloak class="size-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- شريط تقدم التمرير --}}
            <span class="scroll-progress"></span>
        </nav>

        {{-- قائمة الموبايل --}}
        <div x-show="open" x-cloak x-transition
             class="mx-auto mt-2 max-w-[90rem] rounded-2xl border border-slate-300/60 bg-surface px-4 py-3 shadow-md dark:border-night-800 dark:bg-night-900 lg:hidden">
            <a href="{{ route('home') }}" class="side-link">الرئيسية</a>
            <a href="{{ route('courses.index') }}" class="side-link">الكورسات</a>
            <a href="{{ route('books.index') }}" class="side-link">الكتب</a>
            <a href="{{ route('help') }}" class="side-link">المساعدة</a>
            @guest
                <a href="{{ route('login') }}" class="side-link sm:hidden">تسجيل الدخول</a>
            @endguest
        </div>
    </header>
@endif
