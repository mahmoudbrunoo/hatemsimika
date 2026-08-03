{{-- ============================================================================
     الصفحة الرئيسية للطالب المسجل — تصميم fullmark (homepage/target.html)
     صفحة مستقلة: تحمل target.css (بعد توحيد ألوانه على هوية المنصة النبيتي/الكريمي
     نهاراً والخوخي الداكن ليلاً) + overrides.css + homepage.js
     كل البيانات الديناميكية والراوتات و CSRF محفوظة من النسخة السابقة
============================================================================ --}}
@php
    $user = auth()->user();
    $hoursTotal = collect($chart)->sum('hours');
    $progressPercent = $totalVideos > 0 ? (int) round(($watchedVideos / $totalVideos) * 100) : 0;

    // ------------------------------------------------ عناصر القائمة الجانبية
    // العناصر الأساسية الأربعة من التصميم + روابط الحساب من القائمة الأصلية
    $sideMain = [
        [
            'label' => 'الرئيسية',
            'href' => route('student.dashboard'),
            'active' => request()->routeIs('student.dashboard'),
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" class="iconify iconify--uiw" width="1em" height="1em" preserveAspectRatio="xMidYMid meet" viewBox="0 0 20 20"><path fill="currentColor" d="M7.07 10.91a2.02 2.02 0 0 1 2.02 2.02v5.05A2.02 2.02 0 0 1 7.07 20H2.02A2.02 2.02 0 0 1 0 17.98v-5.05a2.02 2.02 0 0 1 2.02-2.02zm10.91 0A2.02 2.02 0 0 1 20 12.93v5.05A2.02 2.02 0 0 1 17.98 20h-5.05a2.02 2.02 0 0 1-2.02-2.02v-5.05a2.02 2.02 0 0 1 2.02-2.02zM7.07 0a2.02 2.02 0 0 1 2.02 2.02v5.05a2.02 2.02 0 0 1-2.02 2.02H2.02A2.02 2.02 0 0 1 0 7.07V2.02A2.02 2.02 0 0 1 2.02 0zm10.91 0A2.02 2.02 0 0 1 20 2.02v5.05a2.02 2.02 0 0 1-2.02 2.02h-5.05a2.02 2.02 0 0 1-2.02-2.02V2.02A2.02 2.02 0 0 1 12.93 0z"></path></svg>',
        ],
        [
            'label' => 'كورساتي',
            'href' => route('student.courses'),
            'active' => request()->routeIs('student.courses') || request()->routeIs('student.learn.*'),
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" class="iconify iconify--mdi" width="1em" height="1em" preserveAspectRatio="xMidYMid meet" viewBox="0 0 24 24"><path fill="currentColor" d="m19 2l-5 4.5v11l5-4.5zM6.5 5C4.55 5 2.45 5.4 1 6.5v14.66c0 .25.25.5.5.5c.1 0 .15-.07.25-.07c1.35-.65 3.3-1.09 4.75-1.09c1.95 0 4.05.4 5.5 1.5c1.35-.85 3.8-1.5 5.5-1.5c1.65 0 3.35.31 4.75 1.06c.1.05.15.03.25.03c.25 0 .5-.25.5-.5V6.5c-.6-.45-1.25-.75-2-1V19c-1.1-.35-2.3-.5-3.5-.5c-1.7 0-4.15.65-5.5 1.5V6.5C10.55 5.4 8.45 5 6.5 5"></path></svg>',
        ],
        [
            'label' => 'المنتدى',
            'href' => route('help'),
            'active' => request()->routeIs('help'),
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" class="iconify iconify--heroicons" width="1em" height="1em" preserveAspectRatio="xMidYMid meet" viewBox="0 0 24 24"><path fill="currentColor" d="M4.5 6.375a4.125 4.125 0 1 1 8.25 0a4.125 4.125 0 0 1-8.25 0m9.75 2.25a3.375 3.375 0 1 1 6.75 0a3.375 3.375 0 0 1-6.75 0M1.5 19.125a7.125 7.125 0 0 1 14.25 0v.003l-.001.119a.75.75 0 0 1-.363.63a13.07 13.07 0 0 1-6.761 1.873c-2.472 0-4.786-.684-6.76-1.873a.75.75 0 0 1-.364-.63zm15.75.003l-.001.144a2.25 2.25 0 0 1-.233.96q.302.018.609.018c1.596 0 3.107-.37 4.451-1.029a.75.75 0 0 0 .42-.642l.004-.204a4.875 4.875 0 0 0-6.961-4.407a8.6 8.6 0 0 1 1.71 5.157z"></path></svg>',
        ],
        [
            'label' => 'حسابي',
            'href' => route('student.profile'),
            'active' => request()->routeIs('student.profile'),
            'icon' => '<svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" class="iconify iconify--zondicons" width="1em" height="1em" preserveAspectRatio="xMidYMid meet" viewBox="0 0 20 20"><path fill="currentColor" d="M5 5a5 5 0 0 1 10 0v2A5 5 0 0 1 5 7zM0 16.68A19.9 19.9 0 0 1 10 14c3.64 0 7.06.97 10 2.68V20H0z"></path></svg>',
        ],
    ];

    $strokeIcon = fn (string $paths) => '<svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveAspectRatio="xMidYMid meet" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">' . $paths . '</svg>';

    $sideExtra = [
        [
            'label' => 'شحن كود سنتر',
            'href' => route('student.charge'),
            'active' => request()->routeIs('student.charge'),
            'icon' => $strokeIcon('<rect x="2.25" y="5.25" width="19.5" height="13.5" rx="2.25"></rect><path d="M2.25 9.75h19.5"></path>'),
        ],
        [
            'label' => 'ربط ID السنتر',
            'href' => route('student.link'),
            'active' => request()->routeIs('student.link'),
            'icon' => $strokeIcon('<path d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244"></path>'),
        ],
        [
            'label' => 'رصيدي',
            'href' => route('student.wallet'),
            'active' => request()->routeIs('student.wallet'),
            'icon' => $strokeIcon('<path d="M21 12a2.25 2.25 0 0 0-2.25-2.25H15a3 3 0 1 1-6 0H5.25A2.25 2.25 0 0 0 3 12m18 0v6a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 9m18 0V6a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 6v3"></path>'),
        ],
        [
            'label' => 'الآمان وتسجيل الدخول',
            'href' => route('student.security'),
            'active' => request()->routeIs('student.security'),
            'icon' => $strokeIcon('<path d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"></path>'),
        ],
        [
            'label' => 'تفاصيل المشاهدات',
            'href' => route('student.watch'),
            'active' => request()->routeIs('student.watch'),
            'icon' => $strokeIcon('<path d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"></path><path d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"></path>'),
        ],
        [
            'label' => 'الفواتير',
            'href' => route('student.invoices'),
            'active' => request()->routeIs('student.invoices'),
            'icon' => $strokeIcon('<path d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9z"></path>'),
        ],
        [
            'label' => 'الاشتراكات',
            'href' => route('student.subscriptions'),
            'active' => request()->routeIs('student.subscriptions'),
            'icon' => $strokeIcon('<path d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"></path>'),
        ],
        [
            'label' => 'نتائج الامتحانات',
            'href' => route('student.results.exams'),
            'active' => request()->routeIs('student.results.exams'),
            'icon' => $strokeIcon('<path d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125z"></path>'),
        ],
        [
            'label' => 'نتائج اختبارات التقييم',
            'href' => route('student.results.evaluations'),
            'active' => request()->routeIs('student.results.evaluations'),
            'icon' => $strokeIcon('<path d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.563.563 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5z"></path>'),
        ],
        [
            'label' => 'نتائج الواجب',
            'href' => route('student.results.homework'),
            'active' => request()->routeIs('student.results.homework'),
            'icon' => $strokeIcon('<path d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"></path>'),
        ],
        [
            'label' => 'امتحان خاص بيك',
            'href' => route('student.personal'),
            'active' => request()->routeIs('student.personal'),
            'icon' => $strokeIcon('<path d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0 1 18 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3 1.5 1.5 3-3.75"></path>'),
        ],
        [
            'label' => 'بنك الاسئلة',
            'href' => route('student.bank'),
            'active' => request()->routeIs('student.bank'),
            'icon' => $strokeIcon('<path d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 3.75c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125m16.5 3.75c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125"></path>'),
        ],
    ];

    $activeLink = 'flex flex-row items-center pl-3 px-2 te justify-start smooth cursor-pointer rounded-md py-3 group space-x-2 space-x-reverse min-w-52 bg-primary-600 dark:bg-primary-600 hover:bg-primary-500 dark:hover:bg-primary-500 dark:text-primary-100 text-primary-100';
    $idleLink = 'flex flex-row items-center pl-3 px-2 te justify-start smooth cursor-pointer rounded-md py-3 group space-x-2 space-x-reverse min-w-52 hover:bg-slate-200 dark:hover:bg-slate-800 dark:text-slate-300 text-slate-800 dark:hover:text-slate-100 hover:text-slate-900';
    $activeIcon = 'w-10 flex-center-both font-h2';
    $idleIcon = 'w-10 flex-center-both font-h2 text-primary-500 group-hover:text-primary-600 dark:group-hover:text-primary-500 smooth';

    // أيقونات التواريخ/الميتا في كروت الكورسات (من التصميم الأصلي)
    $calendarIcon = '<svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" class="iconify iconify--ic" width="1em" height="1em" preserveAspectRatio="xMidYMid meet" viewBox="0 0 24 24"><path fill="currentColor" d="m11.17 8-.59-.59L9.17 6H4v12h16V8zM14 10h2v2h2v2h-2v2h-2v-2h-2v-2h2z" opacity=".3"></path><path fill="currentColor" d="M20 6h-8l-2-2H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V8c0-1.11-.89-2-2-2m0 12H4V6h5.17l1.41 1.41.59.59H20zm-8-4h2v2h2v-2h2v-2h-2v-2h-2v2h-2z"></path></svg>';
    $lessonsIcon = '<svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveAspectRatio="xMidYMid meet" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 10l4.553-2.276A1 1 0 0 1 21 8.618v6.764a1 1 0 0 1-1.447.894L15 14M5 18h8a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2z"></path></svg>';
@endphp
<!DOCTYPE html>
<html lang="ar" dir="rtl" style="--body-top: var(--navbar-height);">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>لوحة التعلم — {{ setting('site.name', 'منصة حاتم سميكة') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=almarai:400,700,800|cairo:400,600,700,800,900|tajawal:400,500,700,800" rel="stylesheet">

    {{-- منع وميض الثيم الخاطئ: نقرأ التفضيل قبل تحميل CSS --}}
    <script>
        (function () {
            var theme = localStorage.getItem('theme') || '{{ $user->theme ?? 'light' }}';
            if (theme === 'dark') {
                document.documentElement.classList.add('dark', 'darkmode');
            }
        })();
    </script>

    <link rel="stylesheet" href="{{ asset('assets/homepage/target.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/homepage/overrides.css') }}">
</head>
<body class="smooth" data-auth="{{ auth()->check() ? 1 : 0 }}" data-theme-url="{{ route('account.theme') }}">

    @if (session('impersonated_by'))
        <div class="impersonation-banner font-taj">
            أنت الآن داخل حساب الطالب: {{ $user->name }} (وضع الدعم الفني)
            <form method="POST" action="{{ route('impersonate.stop') }}" class="inline">
                @csrf
                <button>الرجوع لحساب الأدمن</button>
            </form>
        </div>
    @endif

    <div class="Toastify"></div>
    <div class="loading-hidden fixed inset-x-0 w-full h-1 top-0 z-50 smooth">
        <div class="loading-bar inset-0 w-full h-full smooth"></div>
    </div>

    {{-- ============================== الهيدر العلوي ============================== --}}
    <nav class="navbar z-40 fixed inset-0 bottom-auto smooth en bg-third-container font-amin">
        <div class="relative h-full rounded-[inherit]">
            <div class="max-w mx-auto px-2 sm:px-2 lg:px-4 h-full">
                <div class="progress-bar__track absolute inset-x-0 bottom-0 bg-primary-200 dark:bg-primary-900 smooth h-1 transform translate-y-0">
                    <div class="progress-bar__moving bg-primary-700 dark:bg-primary-400 h-full left-0 absolute transition-colors duration-300" style="width: 0%;"></div>
                </div>

                <div class="relative flex items-center justify-between h-full flex-row-reverse">
                    {{-- مفتاح الوضع الليلي (موبايل) — نفس مكوّن theme-pill المستخدم في باقي المنصة --}}
                    <div class="en block md:hidden absolute left-0">
                        <button data-theme-switch class="theme-pill" role="switch" type="button" tabindex="0" aria-checked="false" title="الوضع الليلي / النهاري">
                            <span class="sr-only">تبديل الوضع الليلي</span>
                            <svg class="theme-pill-sun" width="24" height="24" fill="none" viewBox="0 0 24 24"><path d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M12 4v1M18 6l-1 1M20 12h-1M18 18l-1-1M12 19v1M7 17l-1 1M5 12H4M7 7 6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <svg class="theme-pill-moon" width="24" height="24" fill="none" viewBox="0 0 24 24"><path d="M18 15.63c-.977.52-1.945.481-3.13.481A6.981 6.981 0 0 1 7.89 9.13c0-1.185-.04-2.153.481-3.13C6.166 7.174 5 9.347 5 12.018A6.981 6.981 0 0 0 11.982 19c2.67 0 4.844-1.166 6.018-3.37ZM16 5c0 2.08-.96 4-3 4 2.04 0 3 .92 3 3 0-2.08.96-3 3-3-2.04 0-3-1.92-3-4Z" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <span class="theme-pill-knob">
                                <svg class="theme-pill-knob-sun" width="24" height="24" fill="none" viewBox="0 0 24 24"><path d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M12 4v1M18 6l-1 1M20 12h-1M18 18l-1-1M12 19v1M7 17l-1 1M5 12H4M7 7 6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                <svg class="theme-pill-knob-moon" width="24" height="24" fill="none" viewBox="0 0 24 24"><path d="M18 15.63c-.977.52-1.945.481-3.13.481A6.981 6.981 0 0 1 7.89 9.13c0-1.185-.04-2.153.481-3.13C6.166 7.174 5 9.347 5 12.018A6.981 6.981 0 0 0 11.982 19c2.67 0 4.844-1.166 6.018-3.37ZM16 5c0 2.08-.96 4-3 4 2.04 0 3 .92 3 3 0-2.08.96-3 3-3-2.04 0-3-1.92-3-4Z" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            </span>
                        </button>
                    </div>

                    {{-- زر فتح القائمة الجانبية (موبايل) --}}
                    <div class="absolute top-navbar-height right-0 flex-center-both pt-2">
                        <button data-sidenav-toggle type="button" class="flex lg:hidden flex-row items-center justify-start cursor-pointer rounded-md py-3 group px-2 z-[50] bg-primary-600 dark:bg-primary-600 hover:bg-primary-500 dark:hover:bg-primary-500 dark:text-slate-900 text-primary-100 smooth">
                            <div class="w-10 flex-center-both font-h1">
                                <span class="flex-center-both trasnform smooth">
                                    <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" class="iconify iconify--solar" width="1em" height="1em" preserveAspectRatio="xMidYMid meet" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M20.536 3.464C19.07 2 16.714 2 12 2S4.929 2 3.464 3.464C2 4.93 2 7.286 2 12s0 7.071 1.464 8.535C4.93 22 7.286 22 12 22s7.071 0 8.535-1.465C22 19.072 22 16.714 22 12s0-7.071-1.465-8.536M14.03 8.47a.75.75 0 0 1 0 1.06L11.56 12l2.47 2.47a.75.75 0 1 1-1.06 1.06l-3-3a.75.75 0 0 1 0-1.06l3-3a.75.75 0 0 1 1.06 0" clip-rule="evenodd"></path></svg>
                                </span>
                            </div>
                        </button>
                    </div>

                    {{-- الشعار + مفتاح الوضع (سطح المكتب) --}}
                    <div class="flex navbar-brand-container flex-1 flex items-center justify-center md:items-center md:justify-end h-full md:space-x-8 space-x-0">
                        <div class="en hidden md:block">
                            <button data-theme-switch class="theme-pill" role="switch" type="button" tabindex="0" aria-checked="false" title="الوضع الليلي / النهاري">
                                <span class="sr-only">تبديل الوضع الليلي</span>
                                <svg class="theme-pill-sun" width="24" height="24" fill="none" viewBox="0 0 24 24"><path d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M12 4v1M18 6l-1 1M20 12h-1M18 18l-1-1M12 19v1M7 17l-1 1M5 12H4M7 7 6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                <svg class="theme-pill-moon" width="24" height="24" fill="none" viewBox="0 0 24 24"><path d="M18 15.63c-.977.52-1.945.481-3.13.481A6.981 6.981 0 0 1 7.89 9.13c0-1.185-.04-2.153.481-3.13C6.166 7.174 5 9.347 5 12.018A6.981 6.981 0 0 0 11.982 19c2.67 0 4.844-1.166 6.018-3.37ZM16 5c0 2.08-.96 4-3 4 2.04 0 3 .92 3 3 0-2.08.96-3 3-3-2.04 0-3-1.92-3-4Z" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                <span class="theme-pill-knob">
                                    <svg class="theme-pill-knob-sun" width="24" height="24" fill="none" viewBox="0 0 24 24"><path d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M12 4v1M18 6l-1 1M20 12h-1M18 18l-1-1M12 19v1M7 17l-1 1M5 12H4M7 7 6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    <svg class="theme-pill-knob-moon" width="24" height="24" fill="none" viewBox="0 0 24 24"><path d="M18 15.63c-.977.52-1.945.481-3.13.481A6.981 6.981 0 0 1 7.89 9.13c0-1.185-.04-2.153.481-3.13C6.166 7.174 5 9.347 5 12.018A6.981 6.981 0 0 0 11.982 19c2.67 0 4.844-1.166 6.018-3.37ZM16 5c0 2.08-.96 4-3 4 2.04 0 3 .92 3 3 0-2.08.96-3 3-3-2.04 0-3-1.92-3-4Z" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </span>
                            </button>
                        </div>
                        <div class="navbar-brand flex-shrink-0 flex items-center">
                            <a href="{{ route('student.dashboard') }}">
                                @if (setting_image('site.logo'))
                                    <img class="h-10 sm:h-14 w-auto" src="{{ setting_image('site.logo') }}" alt="{{ setting('site.name', config('app.name')) }}">
                                @else
                                    <span class="flex-center-both rounded-md bg-primary-600 text-primary-100 font-h1 font-w-bold" style="width: 2.75rem; height: 2.75rem;">{{ mb_substr(setting('site.name', 'س'), 0, 1) }}</span>
                                @endif
                            </a>
                        </div>
                    </div>

                    {{-- المستخدم + الإشعارات + المحفظة --}}
                    <div class="absolute inset-y-0 right-0 flex items-center pr-2 md:static md:inset-auto sm:pr-0 sm:ml-6">
                        <div class="flex flex-center-both space-x-2 relative">
                            {{-- قائمة المستخدم --}}
                            <div data-menu-root class="hover:clr-text-secondary relative ar">
                                <button data-menu-button type="button" class="p-1 smooth" aria-haspopup="menu" aria-expanded="false">
                                    <span class="sr-only">فتح قائمة المستخدم</span>
                                    @if ($user->avatar_path)
                                        <img class="h-8 w-8 rounded-full object-cover" src="{{ $user->avatar_path }}" alt="{{ $user->shortName() }}">
                                    @else
                                        <span class="h-8 w-8 rounded-full flex-center-both bg-primary-600 text-primary-100 font-w-bold">{{ mb_substr($user->name, 0, 1) }}</span>
                                    @endif
                                </button>
                                <div data-menu-panel class="hidden absolute left-0 mt-2 rounded-md bg-inner-container clr-text-primary smooth shadow-md border-2 border-third-container overflow-hidden z-50 font-taj">
                                    <div class="px-4 py-3 font-w-bold">{{ $user->shortName() }}</div>
                                    <div class="menu-sep"></div>
                                    @if ($user->isStaff())
                                        <a href="{{ route('admin.dashboard') }}" class="block px-4 py-3 smooth text-primary-500 font-w-bold hover:bg-slate-200 dark:hover:bg-slate-800">لوحة التحكم</a>
                                        <div class="menu-sep"></div>
                                    @endif
                                    <a href="{{ route('student.dashboard') }}" class="block px-4 py-3 smooth hover:bg-slate-200 dark:hover:bg-slate-800">الرئيسية</a>
                                    <a href="{{ route('student.profile') }}" class="block px-4 py-3 smooth hover:bg-slate-200 dark:hover:bg-slate-800">ملف المستخدم</a>
                                    <a href="{{ route('student.courses') }}" class="block px-4 py-3 smooth hover:bg-slate-200 dark:hover:bg-slate-800">كورساتي</a>
                                    <a href="{{ route('student.invoices') }}" class="block px-4 py-3 smooth hover:bg-slate-200 dark:hover:bg-slate-800">الفواتير</a>
                                    <div class="menu-sep"></div>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button class="block w-full px-4 py-3 smooth text-rose-500 hover:bg-slate-200 dark:hover:bg-slate-800">تسجيل الخروج</button>
                                    </form>
                                </div>
                            </div>

                            {{-- الإشعارات --}}
                            <div data-menu-root class="hover:clr-text-secondary relative ar">
                                <button data-menu-button type="button" class="p-1 smooth" aria-haspopup="menu" aria-expanded="false">
                                    <span class="sr-only">الإشعارات</span>
                                    <div class="relative clr-text-primary smooth">
                                        <span class="flex-center-both trasnform font-h1 -translate-y-px">
                                            <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" class="iconify iconify--ph" width="1em" height="1em" preserveAspectRatio="xMidYMid meet" viewBox="0 0 256 256"><g fill="currentColor"><path d="M208 192H48a8 8 0 0 1-6.88-12C47.71 168.6 56 139.81 56 104a72 72 0 0 1 144 0c0 35.82 8.3 64.6 14.9 76a8 8 0 0 1-6.9 12" opacity=".2"></path><path d="M221.8 175.94c-5.55-9.56-13.8-36.61-13.8-71.94a80 80 0 1 0-160 0c0 35.34-8.26 62.38-13.81 71.94A16 16 0 0 0 48 200h40.81a40 40 0 0 0 78.38 0H208a16 16 0 0 0 13.8-24.06M128 216a24 24 0 0 1-22.62-16h45.24A24 24 0 0 1 128 216m-80-32c7.7-13.24 16-43.92 16-80a64 64 0 1 1 128 0c0 36.05 8.28 66.73 16 80Z"></path></g></svg>
                                        </span>
                                    </div>
                                </button>
                                <div data-menu-panel class="hidden absolute left-0 mt-2 rounded-md bg-inner-container clr-text-primary smooth shadow-md border-2 border-third-container overflow-hidden z-50 font-taj">
                                    <div class="px-4 py-6 text-center clr-text-secondary smooth">لا توجد إشعارات جديدة حالياً</div>
                                </div>
                            </div>

                            {{-- كبسولة المحفظة --}}
                            <a href="{{ route('student.wallet') }}" title="محفظتي">
                                <div class="">
                                    <div class="rounded-full bg-slate-900 dark:bg-slate-100 smooth flex flex-row flex-center-both overflow-hidden">
                                        <div class="text-slate-100 font-h3 bg-slate-500 dark:text-slate-900 smooth rounded-full md:px-2 md:py-2 py-2 px-1">
                                            <span class="flex-center-both trasnform -translate-y-px">
                                                <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" class="iconify iconify--solar" width="1em" height="1em" preserveAspectRatio="xMidYMid meet" viewBox="0 0 24 24"><path fill="currentColor" d="M5.75 7a.75.75 0 0 0 0 1.5h4a.75.75 0 0 0 0-1.5z"></path><path fill="currentColor" fill-rule="evenodd" d="M21.188 8.004q-.094-.005-.2-.004h-2.773C15.944 8 14 9.736 14 12s1.944 4 4.215 4h2.773q.106.001.2-.004c.923-.056 1.739-.757 1.808-1.737c.004-.064.004-.133.004-.197V9.938c0-.064 0-.133-.004-.197c-.069-.98-.885-1.68-1.808-1.737m-3.217 5.063c.584 0 1.058-.478 1.058-1.067c0-.59-.474-1.067-1.058-1.067s-1.06.478-1.06 1.067c0 .59.475 1.067 1.06 1.067" clip-rule="evenodd"></path><path fill="currentColor" d="M21.14 8.002c0-1.181-.044-2.448-.798-3.355a4 4 0 0 0-.233-.256c-.749-.748-1.698-1.08-2.87-1.238C16.099 3 14.644 3 12.806 3h-2.112C8.856 3 7.4 3 6.26 3.153c-1.172.158-2.121.49-2.87 1.238c-.748.749-1.08 1.698-1.238 2.87C2 8.401 2 9.856 2 11.694v.112c0 1.838 0 3.294.153 4.433c.158 1.172.49 2.121 1.238 2.87c.749.748 1.698 1.08 2.87 1.238c1.14.153 2.595.153 4.433.153h2.112c1.838 0 3.294 0 4.433-.153c1.172-.158 2.121-.49 2.87-1.238q.305-.308.526-.66c.45-.72.504-1.602.504-2.45l-.15.001h-2.774C15.944 16 14 14.264 14 12s1.944-4 4.215-4h2.773q.079 0 .151.002" opacity=".5"></path></svg>
                                            </span>
                                        </div>
                                        <div class="text-slate-100 dark:text-slate-900 smooth md:pb-0.5 font-small md:px-2 px-1">{{ egp($user->balance ?? 0) }}</div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="flex flex-col justify-between h-full w-full relative min-h-screen">
        <div class="w-full">
            <div class="relative">
                <div class="w-screen flex">
                    {{-- ============================== القائمة الجانبية ============================== --}}
                    <div class="hidden lg:flex relative shrink-0 sidenav-outer">
                        @foreach (['fixed', 'spacer'] as $copy)
                            <div @if ($copy === 'spacer') inert aria-hidden="true" @endif
                                 class="{{ $copy === 'fixed'
                                    ? 'smooth fixed z-30 right-0 peer navbar-complement-height pisitive-nav-top overflow-auto font-taj bg-primary-container border-l-2 border-slate-300 dark:border-slate-800 shadow-md SideNav text-primary-500 px-5'
                                    : 'smooth relative pisitive-nav-top z-0 opacity-0 peer navbar-complement-height overflow-auto font-taj bg-primary-container border-l-2 border-slate-300 dark:border-slate-800 shadow-md SideNav text-primary-500 px-5 sidenav-spacer' }}">
                                <div class="flex flex-col space-y-3 py-4 rounded-md px-3 relative z-10">

                                    {{-- زر تصغير النافذة --}}
                                    <div class="right-0 top-0 h-full" data-collapse-toggle>
                                        <div class="flex flex-row-reverse space-x-2 space-x-reverse">
                                            <div class="flex flex-row-reverse items-center px-2 te justify-start smooth cursor-pointer rounded-md py-3 group space-x-2 hover:bg-slate-200 dark:hover:bg-slate-800 dark:text-slate-100 text-slate-600 px-2 w-full justify-between">
                                                <div class="w-10 flex-center-both font-h1">
                                                    <span data-collapse-arrow class="flex-center-both trasnform smooth text-primary-500 rotate-180">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" class="iconify iconify--solar" width="1em" height="1em" preserveAspectRatio="xMidYMid meet" viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M20.536 3.464C19.07 2 16.714 2 12 2S4.929 2 3.464 3.464C2 4.93 2 7.286 2 12s0 7.071 1.464 8.535C4.93 22 7.286 22 12 22s7.071 0 8.535-1.465C22 19.072 22 16.714 22 12s0-7.071-1.465-8.536M14.03 8.47a.75.75 0 0 1 0 1.06L11.56 12l2.47 2.47a.75.75 0 1 1-1.06 1.06l-3-3a.75.75 0 0 1 0-1.06l3-3a.75.75 0 0 1 1.06 0" clip-rule="evenodd"></path></svg>
                                                    </span>
                                                </div>
                                                <div class="font-smaller flex font-w-bold pl-3 space-x-1 space-x-reverse nav-label">
                                                    <span>تصغير</span>
                                                    <span>النافذة</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- العناصر الأساسية --}}
                                    @foreach ($sideMain as $item)
                                        <a @if ($item['active']) aria-current="page" @endif
                                           class="{{ $item['active'] ? $activeLink : $idleLink }}"
                                           href="{{ $item['href'] }}">
                                            <div class="{{ $item['active'] ? $activeIcon : $idleIcon }}">
                                                <span class="flex-center-both trasnform -translate-y-px">{!! $item['icon'] !!}</span>
                                            </div>
                                            <div class="flex font-w-bold pl-3 space-x-1 space-x-reverse nav-label">
                                                <span>{{ $item['label'] }}</span>
                                            </div>
                                        </a>
                                    @endforeach

                                    {{-- روابط الحساب والإعدادات (من القائمة الأصلية) --}}
                                    @foreach ($sideExtra as $item)
                                        <a @if ($item['active']) aria-current="page" @endif
                                           class="{{ $item['active'] ? $activeLink : $idleLink }}"
                                           href="{{ $item['href'] }}">
                                            <div class="{{ $item['active'] ? $activeIcon : $idleIcon }}">
                                                <span class="flex-center-both trasnform -translate-y-px">{!! $item['icon'] !!}</span>
                                            </div>
                                            <div class="flex font-w-bold pl-3 space-x-1 space-x-reverse nav-label">
                                                <span>{{ $item['label'] }}</span>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- ============================== محتوى الصفحة ============================== --}}
                    <div class="bg-primary-container min-h-screen smooth clr-text-primary w-full overflow-auto">
                        <div class="z-10 relative h-full w-full">
                            <div class="px-2 lg:px-4 sm:px-10 bg-outer-container clr-text-primary smooth h-full w-full space-y-10 py-8">

                                {{-- رسائل الفلاش والأخطاء --}}
                                @if (session('status'))
                                    <div data-flash class="flash-success rounded-md px-4 py-3 font-taj font-w-bold smooth flex justify-between items-center">
                                        <span>{{ session('status') }}</span>
                                        <button type="button" data-dismiss class="px-2 cursor-pointer">✕</button>
                                    </div>
                                @endif
                                @if ($errors->any())
                                    <div data-flash class="flash-error rounded-md px-4 py-3 font-taj font-w-bold smooth">
                                        <ul class="space-y-1">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <div class="flex flex-col space-y-4 w-full h-full">

                                    {{-- ترحيب باسم الطالب --}}
                                    <div class="font-taj leading-none">
                                        <div class="font-h2 font-w-bold clr-text-primary smooth">أهلاً بيك يا {{ $user->shortName() }} 👋</div>
                                        <div class="text-sm clr-text-secondary smooth pt-1">{{ $user->yearLabel() }} — تابع نشاطك ومذاكرتك من هنا</div>
                                    </div>

                                    {{-- الإحصائيات السريعة --}}
                                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 font-taj leading-none">
                                        <div class="relative overflow-hidden rounded-lg">
                                            <div class="relative z-10 bg-secondary-500 dark:bg-secondary-700 text-white rounded-lg p-6 flex justify-start items-center space-x-4 space-x-reverse smooth">
                                                <div class="p-2 bg-slate-50/10 rounded-lg font-h1">
                                                    <span class="flex-center-both trasnform -translate-y-px">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" class="iconify iconify--eva" width="1em" height="1em" preserveAspectRatio="xMidYMid meet" viewBox="0 0 24 24"><path fill="currentColor" d="M18 3H6a3 3 0 0 0-3 3v12a3 3 0 0 0 3 3h12a3 3 0 0 0 3-3V6a3 3 0 0 0-3-3m-1.7 6.61-4.57 6a1 1 0 0 1-.79.39a1 1 0 0 1-.79-.38l-2.44-3.11a1 1 0 0 1 1.58-1.23l1.63 2.08l3.78-5a1 1 0 1 1 1.6 1.22Z"></path></svg>
                                                    </span>
                                                </div>
                                                <div class="space-y-1">
                                                    <div class="font-h1 font-w-bold">{{ $watchedVideos }} <span class="font-small">/ {{ $totalVideos }}</span></div>
                                                    <div class="">فيديو خلصته من كورساتك</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="relative overflow-hidden rounded-lg">
                                            <div class="relative z-10 bg-secondary-500 dark:bg-secondary-700 text-white rounded-lg p-6 flex justify-start items-center space-x-4 space-x-reverse smooth">
                                                <div class="p-2 bg-slate-50/10 rounded-lg font-h1">
                                                    <span class="flex-center-both trasnform -translate-y-px">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" class="iconify iconify--heroicons-solid" width="1em" height="1em" preserveAspectRatio="xMidYMid meet" viewBox="0 0 20 20"><path fill="currentColor" d="M11 3a1 1 0 1 0-2 0v1a1 1 0 1 0 2 0zm4.657 2.757a1 1 0 0 0-1.414-1.414l-.707.707a1 1 0 0 0 1.414 1.414zM18 10a1 1 0 0 1-1 1h-1a1 1 0 1 1 0-2h1a1 1 0 0 1 1 1M5.05 6.464A1 1 0 1 0 6.464 5.05l-.707-.707a1 1 0 0 0-1.414 1.414zM5 10a1 1 0 0 1-1 1H3a1 1 0 1 1 0-2h1a1 1 0 0 1 1 1m3 6v-1h4v1a2 2 0 1 1-4 0m4-2c.015-.34.208-.646.477-.859a4 4 0 1 0-4.954 0c.27.213.462.519.476.859z"></path></svg>
                                                    </span>
                                                </div>
                                                <div class="space-y-1">
                                                    <div class="font-h1 font-w-bold">{{ $examsTaken }} <span class="font-small">/ {{ $examsAvailable }}</span></div>
                                                    <div class="">امتحان دخلته من المتاح</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="relative overflow-hidden rounded-lg">
                                            <div class="relative z-10 bg-secondary-500 dark:bg-secondary-700 text-white rounded-lg p-6 flex justify-start items-center space-x-4 space-x-reverse smooth">
                                                <div class="p-2 bg-slate-50/10 rounded-lg font-h1">
                                                    <span class="flex-center-both trasnform -translate-y-px">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" class="iconify iconify--ic" width="1em" height="1em" preserveAspectRatio="xMidYMid meet" viewBox="0 0 24 24"><path fill="currentColor" d="m19 18 2 1V3c0-1.1-.9-2-2-2H8.99C7.89 1 7 1.9 7 3h10c1.1 0 2 .9 2 2zM15 5H5c-1.1 0-2 .9-2 2v16l7-3l7 3V7c0-1.1-.9-2-2-2"></path></svg>
                                                    </span>
                                                </div>
                                                <div class="space-y-1">
                                                    <div class="font-h1 font-w-bold">{{ $avgPercent }}%</div>
                                                    <div class="">متوسط درجاتك في الامتحانات</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="relative overflow-hidden rounded-lg">
                                            <div class="relative z-10 bg-secondary-500 dark:bg-secondary-700 text-white rounded-lg p-6 flex justify-start items-center space-x-4 space-x-reverse smooth">
                                                <div class="p-2 bg-slate-50/10 rounded-lg font-h1">
                                                    <span class="flex-center-both trasnform -translate-y-px">
                                                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveAspectRatio="xMidYMid meet" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"></circle><path d="M12 7v5l3 3"></path></svg>
                                                    </span>
                                                </div>
                                                <div class="space-y-1">
                                                    <div class="font-h1 font-w-bold">{{ $hoursTotal }}</div>
                                                    <div class="">ساعة مذاكرة آخر 7 أيام</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- الرسم البياني الأسبوعي + مقياس التقدم --}}
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 font-taj leading-none">
                                        <div class="col-spans-1 md:col-span-2 relative overflow-hidden rounded-xl">
                                            <div class="relative z-10 bg-inner-container clr-text-primary smooth rounded-xl">
                                                <div class="w-full h-[350px] rounded-xl p-4">
                                                    <canvas data-chart="weekly" data-series='@json($chart)'></canvas>
                                                </div>
                                                <div class="font text-xs clr-text-secondary smooth -mt-4 px-4 pb-4">*نشاطك آخر 7 أيام — فيديوهاتك وكويزاتك وساعات مذاكرتك</div>
                                            </div>
                                        </div>
                                        <div class="relative overflow-hidden rounded-lg">
                                            <div class="relative z-10 bg-secondary-500 dark:bg-secondary-700 text-white rounded-lg p-2 smooth h-full">
                                                <div class="bg-inner-container clr-text-primary smooth rounded-lg rounded-tl-[80px] px-4 py-6 space-y-4 flex flex-col items-center justify-evenly w-full h-full">
                                                    <div class="text-sm">تقدمك</div>
                                                    <div class="space-y-1 w-full flex flex-col flex-center-both">
                                                        <div class="font-h2 font-w-bold text-secondary-800 dark:text-secondary-200 smooth">
                                                            <span>{{ $progressPercent }} %</span>
                                                        </div>
                                                        <div class="w-full rounded-md bg-outer-container h-4 relative overflow-hidden smooth">
                                                            <div class="absolute top-0 right-0 h-full bg-gradient-to-r from-secondary-400 to-secondary-800 rounded-md" style="width: {{ $progressPercent }}%;"></div>
                                                        </div>
                                                    </div>
                                                    <div class="text-secondary-900/50 dark:text-secondary-200/50 smooth text-center">مقياس لكمية الدروس اللي خلصتها من إجمالي دروس كورساتك!</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- الكورسات المقترحة --}}
                                    <div class="relative overflow-hidden rounded-xl">
                                        <div class="relative z-10 p-2 rounded-xl bg-inner-container clr-text-primary smooth">
                                            <div class="w-full px-2 lg:px-4 pt-4 flex justify-between items-center font-taj">
                                                <div>
                                                    <div class="font-w-bold">الكورسات المقترحة</div>
                                                    <div class="text-xs clr-text-secondary smooth pt-1">
                                                        {{ $showAll ? 'بتعرض كورسات كل الصفوف' : 'مفلترة على ' . $user->yearLabel() }}
                                                    </div>
                                                </div>
                                                @if ($showAll)
                                                    <a href="{{ route('student.dashboard') }}">
                                                        <button class="border-2 smooth rounded-xl bg-primary-500 border-primary-500 dark:bg-primary-500 dark:border-primary-500 hover:bg-opacity-100 dark:hover:bg-opacity-100 dark:bg-opacity-0 bg-opacity-0 text-primary-500 dark:hover:text-primary-500 hover:text-slate-50 dark:hover:text-slate-50 px-4 py-2">عرض صفي فقط</button>
                                                    </a>
                                                @else
                                                    <a href="{{ route('student.dashboard', ['all' => 1]) }}">
                                                        <button class="border-2 smooth rounded-xl bg-primary-500 border-primary-500 dark:bg-primary-500 dark:border-primary-500 hover:bg-opacity-100 dark:hover:bg-opacity-100 dark:bg-opacity-0 bg-opacity-0 text-primary-500 dark:hover:text-primary-500 hover:text-slate-50 dark:hover:text-slate-50 px-4 py-2">عرض كل الصفوف</button>
                                                    </a>
                                                @endif
                                            </div>

                                            <div class="px-2 lg:px-4 sm:px-10 space-y-10 py-8">
                                                @if ($featured->isEmpty())
                                                    <div class="bg-third-container clr-text-secondary smooth rounded-xl p-6 text-center font-taj">
                                                        مفيش كورسات متاحة حالياً — تابعنا قريباً!
                                                    </div>
                                                @else
                                                    <div class="smooth clr-text-primary bg-opacity-50 dark:bg-opacity-50 grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-10">
                                                        @foreach ($featured as $course)
                                                            <div class="group relative font-amin h-full">
                                                                <div class="bg-third-container clr-text-primary border-2 border-third-container smooth rounded-xl p-4 flex flex-col space-y-4 h-full">
                                                                    <div class="rounded-xl relative overflow-hidden">
                                                                        @if ($course->thumbnail_path)
                                                                            <img src="{{ $course->thumbnail_path }}" alt="{{ $course->title }}" class="w-full h-full max-h-[600px] object-cover">
                                                                        @else
                                                                            <div class="w-full flex-center-both bg-secondary-500 dark:bg-secondary-700 text-white font-h1 font-w-bold" style="height: 180px;">{{ mb_substr($course->title, 0, 1) }}</div>
                                                                        @endif
                                                                    </div>
                                                                    <div class="flex flex-col space-y-0 leading-0">
                                                                        <div class="flex md:flex-row md:justify-between flex-col flex-col-reverse w-full text-lg">
                                                                            <div class="">{{ $course->title }}</div>
                                                                        </div>
                                                                        <div class="flex md:flex-row md:justify-between flex-col md:items-center w-full">
                                                                            <div class="md:space-y-1 flex md:flex-col flex-row-reverse justify-between">
                                                                                <div class="text-center md:text-right -mt-6 md:mt-0">
                                                                                    @if ((float) $course->price <= 0)
                                                                                        <div class="text-primary-500">
                                                                                            <div class="border-primary-500 w-fit dark:text-primary-200 smooth bg-primary-100 dark:bg-primary-900 border rounded-md py-1 px-3">كورس مجاني</div>
                                                                                        </div>
                                                                                    @else
                                                                                        <div class="text-primary-500">
                                                                                            {{ egp($course->effectivePrice()) }}
                                                                                            @if ($course->discount_price !== null)
                                                                                                <span class="line-through clr-text-secondary text-sm smooth">{{ egp($course->price) }}</span>
                                                                                            @endif
                                                                                        </div>
                                                                                    @endif
                                                                                </div>
                                                                                <div class="text-sm clr-text-secondary font-taj mt-1 smooth">
                                                                                    @if ($course->description)
                                                                                        <span>{{ \Illuminate\Support\Str::limit($course->description, 90) }}<br></span>
                                                                                    @endif
                                                                                    <span>{{ $course->yearLabel() }} • {{ $course->categoryLabel() }}<br></span>
                                                                                </div>
                                                                            </div>
                                                                            <div class="flex flex-col md:items-end items-start space-y-1 clr-text-secondary smooth font-taj text-xs md:block hidden">
                                                                                <div class="flex md:space-x-1 md:space-x-reverse md:flex-row space-x-1 flex-row-reverse items-center">
                                                                                    <span class="flex-center-both">{{ $course->created_at->locale('ar')->translatedFormat('l، j F Y') }}</span>
                                                                                    <span class="font-sm flex-center-both transform -translate-y-0.5">{!! $calendarIcon !!}</span>
                                                                                </div>
                                                                                <div class="flex md:space-x-1 md:space-x-reverse md:flex-row space-x-1 flex-row-reverse items-center">
                                                                                    <span class="flex-center-both">{{ $course->totalVideos() }} درس • {{ floor($course->totalDurationSeconds() / 3600) }} ساعة</span>
                                                                                    <span class="font-sm flex-center-both transform -translate-y-0.5">{!! $lessonsIcon !!}</span>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="flex justify-between items-center w-full !mt-auto pt-4">
                                                                        <div class="flex flex-col md:items-end items-start space-y-1 clr-text-secondary smooth font-taj text-xs block md:hidden w-1/2">
                                                                            <div class="flex md:space-x-1 md:space-x-reverse md:flex-row space-x-1 flex-row-reverse items-center">
                                                                                <span class="flex-center-both">{{ $course->created_at->locale('ar')->translatedFormat('l، j F Y') }}</span>
                                                                                <span class="font-sm flex-center-both transform -translate-y-0.5">{!! $calendarIcon !!}</span>
                                                                            </div>
                                                                            <div class="flex md:space-x-1 md:space-x-reverse md:flex-row space-x-1 flex-row-reverse items-center">
                                                                                <span class="flex-center-both">{{ $course->totalVideos() }} درس • {{ floor($course->totalDurationSeconds() / 3600) }} ساعة</span>
                                                                                <span class="font-sm flex-center-both transform -translate-y-0.5">{!! $lessonsIcon !!}</span>
                                                                            </div>
                                                                        </div>
                                                                        <div class="md:w-full w-1/2 flex flex-col space-y-1">
                                                                            <a class="w-full" href="{{ route('courses.show', $course) }}">
                                                                                <button class="border-2 smooth w-full rounded-xl bg-primary-500 border-primary-500 dark:bg-primary-500 dark:border-primary-500 hover:bg-opacity-100 dark:hover:bg-opacity-100 dark:bg-opacity-0 bg-opacity-0 text-primary-500 dark:hover:text-primary-500 hover:text-slate-50 dark:hover:text-slate-50 px-4 py-2">الدخول للكورس</button>
                                                                            </a>
                                                                            @if ((float) $course->price > 0)
                                                                                <a class="w-full" href="{{ route('student.checkout.course', $course) }}">
                                                                                    <button class="border-2 smooth w-full rounded-xl bg-primary-500 border-primary-500 dark:bg-primary-600 dark:border-primary-600 hover:bg-opacity-0 dark:hover:bg-opacity-0 hover:text-primary-500 dark:hover:text-primary-600 clr-white px-4 py-2">الإشتراك في الكورس !</button>
                                                                                </a>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================== الفوتر ============================== --}}
        <div class="footer bg-primary-container clr-text-primary smooth w-full relative flex md:flex-row flex-col md:items-center items-start md:space-y-0 space-y-10 py-10 px-14 font-taj z-50">
            <div class="flex flex-col items-start justify-between h-full space-y-8 md:w-1/2 w-full">
                <div>
                    @if (setting_image('site.logo'))
                        <img class="h-24" src="{{ setting_image('site.logo') }}" alt="{{ setting('site.name', config('app.name')) }}">
                    @else
                        <span class="flex-center-both rounded-md bg-primary-600 text-primary-100 font-h1 font-w-bold" style="width: 4rem; height: 4rem;">{{ mb_substr(setting('site.name', 'س'), 0, 1) }}</span>
                    @endif
                </div>
                <div class="">
                    <div class="font-w-bold">{{ setting('footer.copyright', 'جميع الحقوق محفوظة') }} © {{ date('Y') }}</div>
                    @if (setting('site.developer'))
                        <div class="en" dir="ltr">
                            <span class="">&lt;</span>
                            <span class="font-w-bold text-secondary-500">{{ setting('site.developer') }}</span>
                            <span class=""> /&gt;</span>
                        </div>
                    @endif
                </div>
            </div>
            <div class="grid md:grid-cols-3 grid-cols-2 gap-8 md:w-1/2 w-full">
                <div class="flex flex-col space-y-4">
                    <div class="font-w-bold text-secondary-500">الصفحات</div>
                    <div class="flex flex-col space-y-1">
                        <a class="hover:text-secondary-500 clr-text-primary smooth" href="{{ route('home') }}"><span>الرئيسية</span></a>
                        <a class="hover:text-secondary-500 clr-text-primary smooth" href="{{ route('courses.index') }}"><span>الكورسات</span></a>
                        <a class="hover:text-secondary-500 clr-text-primary smooth" href="{{ route('books.index') }}"><span>الكتب</span></a>
                        <a class="hover:text-secondary-500 clr-text-primary smooth" href="{{ route('help') }}"><span>المساعدة</span></a>
                    </div>
                </div>
                <div class="flex flex-col space-y-4">
                    <div class="font-w-bold text-secondary-500">السوشيال ميديا</div>
                    <div class="flex flex-col space-y-1">
                        <a href="{{ setting('social.facebook', 'https://www.facebook.com/share/14cpLcJ6Yx5/') }}" target="_blank" rel="noopener" class="hover:text-secondary-500 clr-text-primary smooth flex flex-row space-x-1 space-x-reverse">
                            <span class="flex-cent"><svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg></span>
                            <span>فيسبوك</span>
                        </a>
                        <a href="{{ setting('social.instagram', 'https://www.instagram.com/hatemsimika') }}" target="_blank" rel="noopener" class="hover:text-secondary-500 clr-text-primary smooth flex flex-row space-x-1 space-x-reverse">
                            <span class="flex-cent"><svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zm0 10.162a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg></span>
                            <span>انستجرام</span>
                        </a>
                        <a href="{{ setting('social.tiktok', 'https://www.tiktok.com/@hatem.simika') }}" target="_blank" rel="noopener" class="hover:text-secondary-500 clr-text-primary smooth flex flex-row space-x-1 space-x-reverse">
                            <span class="flex-cent"><svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg></span>
                            <span>تيك توك</span>
                        </a>
                        <a href="{{ setting('social.youtube', 'https://youtube.com/@hatem_simika') }}" target="_blank" rel="noopener" class="hover:text-secondary-500 clr-text-primary smooth flex flex-row space-x-1 space-x-reverse">
                            <span class="flex-cent"><svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg></span>
                            <span>يوتيوب</span>
                        </a>
                    </div>
                </div>
                <div class="flex flex-col space-y-4 col-span-2 md:col-span-1">
                    <div class="font-w-bold text-secondary-500">تواصل معنا</div>
                    <div class="flex flex-col space-y-1">
                        <a class="border-2 smooth text-center bg-slate-500 border-slate-500 dark:bg-slate-400 dark:border-slate-400 hover:bg-opacity-100 dark:hover:bg-opacity-100 dark:bg-opacity-0 bg-opacity-0 text-slate-500 dark:text-slate-400 hover:text-slate-50 dark:hover:text-slate-50 rounded-md px-4 py-2" href="https://wa.me/2{{ setting('support.whatsapp', '01003878666') }}" target="_blank" rel="noopener">
                            <span class="block pt-1">المساعدة</span>
                        </a>
                        <div class="text-sm clr-text-secondary smooth">للدعم الفني: <span dir="ltr">{{ setting('support.whatsapp', '01003878666') }}</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/homepage/chart.umd.js') }}"></script>
    <script src="{{ asset('assets/homepage/homepage.js') }}"></script>
</body>
</html>
