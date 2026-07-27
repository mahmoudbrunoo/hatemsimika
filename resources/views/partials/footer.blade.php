<footer class="mt-16 border-t border-slate-100 bg-white dark:border-night-800 dark:bg-night-900">
    <div class="mx-auto grid max-w-[90rem] gap-12 px-5 py-14 sm:px-8 md:grid-cols-3">

        {{-- الشعار والحقوق --}}
        <div class="text-center md:text-right">
            <div class="flex items-center justify-center gap-2 md:justify-start">
                @if (setting_image('site.logo'))
                    <img src="{{ setting_image('site.logo') }}" alt="{{ setting('site.name') }}" class="h-16 w-auto">
                @else
                    <span class="grid size-12 place-items-center rounded-xl bg-flame-600 text-xl font-black text-white">{{ mb_substr(setting('site.name', 'س'), 0, 1) }}</span>
                    <span class="text-xl font-black text-slate-900 dark:text-white">{{ setting('site.name', 'منصة حاتم سميكة') }}</span>
                @endif
            </div>

            <p class="mt-4 text-sm font-semibold leading-7 text-slate-500 dark:text-slate-400">
                {{ setting('footer.bio', 'منصة تعليمية متخصصة لطلاب الثانوية العامة — شرح مبسط، امتحانات دورية، ومتابعة مستمرة لكل طالب.') }}
            </p>

            <p class="mt-5 text-sm font-black text-slate-800 dark:text-slate-200">
                {{ setting('footer.copyright', 'جميع الحقوق محفوظة') }} &copy; {{ date('Y') }}
            </p>

            @if (setting('site.developer'))
                <p class="mt-2 text-xs font-bold text-slate-400 dark:text-slate-500" dir="ltr">
                    &lt;<span class="text-flame-600 dark:text-flame-400">{{ setting('site.developer') }}</span> /&gt;
                </p>
            @endif
        </div>

        {{-- الصفحات --}}
        <div class="text-center">
            <h3 class="text-lg font-black text-slate-900 dark:text-white">الصفحات</h3>
            <ul class="mt-5 space-y-3 text-sm font-bold text-slate-500 dark:text-slate-400">
                <li><a href="{{ route('home') }}" class="transition hover:text-flame-600 dark:hover:text-flame-400">الرئيسية</a></li>
                <li><a href="{{ route('courses.index') }}" class="transition hover:text-flame-600 dark:hover:text-flame-400">الكورسات</a></li>
                <li><a href="{{ route('books.index') }}" class="transition hover:text-flame-600 dark:hover:text-flame-400">الكتب</a></li>
                <li><a href="{{ route('help') }}" class="transition hover:text-flame-600 dark:hover:text-flame-400">المساعدة</a></li>
                @guest
                    <li><a href="{{ route('register') }}" class="transition hover:text-flame-600 dark:hover:text-flame-400">انشاء حساب جديد</a></li>
                    <li><a href="{{ route('login') }}" class="transition hover:text-flame-600 dark:hover:text-flame-400">تسجيل الدخول</a></li>
                @endguest
            </ul>
        </div>

        {{-- السوشيال ميديا --}}
        <div class="text-center">
            <h3 class="text-lg font-black text-flame-600 dark:text-flame-400">السوشيال ميديا</h3>
            <ul class="mt-5 space-y-3 text-sm font-bold text-slate-600 dark:text-slate-300">
                <li>
                    <a href="{{ setting('social.facebook', 'https://www.facebook.com/share/14cpLcJ6Yx5/') }}" target="_blank" rel="noopener"
                       class="inline-flex items-center gap-2 transition hover:text-[#1877f2]">
                        <span>فيسبوك</span>
                        <svg class="size-5 text-[#1877f2]" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    </a>
                </li>
                <li>
                    <a href="{{ setting('social.instagram', 'https://www.instagram.com/hatemsimika?igsh=eHA1b3FlOHliZmhj') }}" target="_blank" rel="noopener"
                       class="inline-flex items-center gap-2 transition hover:text-[#e4405f]">
                        <span>انستجرام</span>
                        <svg class="size-5 text-[#e4405f]" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zm0 10.162a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                    </a>
                </li>
                <li>
                    <a href="{{ setting('social.tiktok', 'https://www.tiktok.com/@hatem.simika?_r=1&_t=ZS-96ITC7fEcVs') }}" target="_blank" rel="noopener"
                       class="inline-flex items-center gap-2 transition hover:text-slate-900 dark:hover:text-white">
                        <span>تيك توك</span>
                        <svg class="size-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg>
                    </a>
                </li>
                <li>
                    <a href="{{ setting('social.youtube', 'https://youtube.com/@hatem_simika?si=mP6yMz6MdCNx63jL') }}" target="_blank" rel="noopener"
                       class="inline-flex items-center gap-2 transition hover:text-[#ff0000]">
                        <span>يوتيوب</span>
                        <svg class="size-5 text-[#ff0000]" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                    </a>
                </li>
            </ul>

            <p class="mt-5 text-sm font-semibold text-slate-500 dark:text-slate-400">
                للدعم الفني: <span dir="ltr">{{ setting('support.whatsapp', '01003878666') }}</span>
            </p>
        </div>
    </div>
</footer>
