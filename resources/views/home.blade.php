@extends('layouts.app')

@section('title', setting('site.name', 'منصة حاتم سميكة') . ' — الرئيسية')

@section('content')
    {{-- ============================ الهيرو الأحمر — نمط خالد صقر ============================ --}}
    <section class="-mt-[5.4rem] overflow-hidden bg-flame-600 pt-28 lg:pt-32">
        <div class="mx-auto grid max-w-[90rem] items-center gap-10 px-5 sm:px-8 lg:grid-cols-2">

            {{-- النص --}}
            <div class="order-1 pb-10 text-center lg:order-2 lg:pb-16 lg:text-right">
                <h1 class="text-4xl font-black leading-[1.35] text-white sm:text-5xl sm:leading-[1.35] lg:text-[3.4rem]">
                    {{ setting('hero.title', 'منصتك الأولى لتعلم وفهم الكيمياء بأسلوب بسيط وممتع') }}
                </h1>

                <p class="mt-8 text-lg font-black text-white">{{ setting('hero.welcome', 'اهلاً بيك في بيتك التاني!') }}</p>
                <p class="mx-auto mt-1 max-w-xl text-sm font-semibold leading-7 text-flame-100 lg:mr-0 lg:ml-auto">
                    {{ setting('hero.subtitle', 'سواء كنت في أولى، تانية، أو تالتة ثانوي، هنا هتلاقي كل اللي تحتاجه علشان تتفوق، وتفهمها صح، وتطبقها بسهولة.') }}
                </p>

                <div class="mt-10">
                    @auth
                        <a href="{{ route('student.dashboard') }}"
                           class="inline-flex items-center justify-center rounded-xl bg-flame-50 px-14 py-4 text-base font-black text-flame-600 shadow-lg transition hover:scale-[1.03] hover:bg-white">
                            لوحة التعلم
                        </a>
                    @else
                        <a href="{{ route('register') }}"
                           class="inline-flex items-center justify-center rounded-xl bg-flame-50 px-14 py-4 text-base font-black text-flame-600 shadow-lg transition hover:scale-[1.03] hover:bg-white">
                            {{ setting('hero.cta', 'اشترك دلوقتي !') }}
                        </a>
                    @endauth
                </div>
            </div>

            {{-- صورة المدرس داخل الدائرة --}}
            <div class="order-2 lg:order-1">
                <div class="relative mx-auto flex aspect-square w-full max-w-[34rem] items-end justify-center">
                    <div class="absolute inset-0 rounded-full bg-gradient-to-br from-flame-400/80 via-flame-500/70 to-flame-700/60"></div>
                    @if (setting_image('hero.image'))
                        <img src="{{ setting_image('hero.image') }}" alt="{{ setting('site.name') }}"
                             class="relative z-10 max-h-full w-auto object-contain drop-shadow-2xl">
                    @else
                        <svg class="relative z-10 mb-[12%] size-2/3 text-flame-200/70" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 12a5 5 0 100-10 5 5 0 000 10zm0 2c-4.418 0-8 2.239-8 5v1a1 1 0 001 1h14a1 1 0 001-1v-1c0-2.761-3.582-5-8-5z"/>
                        </svg>
                    @endif
                </div>
            </div>
        </div>

        {{-- إحصائيات المتابعين --}}
        <div class="mx-auto flex max-w-5xl flex-wrap items-center justify-around gap-8 px-5 pb-14 pt-4">
            <div class="text-center">
                <p class="text-5xl font-black text-white sm:text-6xl">{{ setting('stats.facebook_count', '1.0M+') }}</p>
                <p class="mt-3 text-base font-extrabold text-white">{{ setting('stats.facebook_label', 'متابعين على الفيسبوك') }}</p>
            </div>
            <div class="text-center">
                <p class="text-5xl font-black text-white sm:text-6xl">{{ setting('stats.youtube_count', '2.0M+') }}</p>
                <p class="mt-3 text-base font-extrabold text-white">{{ setting('stats.youtube_label', 'متابعين على اليوتيوب') }}</p>
            </div>
        </div>
    </section>

    {{-- ============================ ليه تشترك معانا؟ ============================ --}}
    <section class="mx-auto max-w-[90rem] px-5 py-16 sm:px-8 lg:py-20">
        <h2 class="text-center text-4xl font-black text-slate-900 dark:text-white sm:text-5xl">
            {{ setting('why.title', 'ليه تشترك معانا؟') }}
        </h2>

        <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach (range(1, 8) as $i)
                @php
                    $whyDefaults = [
                        1 => 'شرح بسيط ومفهوم',
                        2 => 'فيديوهات برسومات توضيحية',
                        3 => 'تمارين تفاعلية على الدروس',
                        4 => 'مرونة كاملة في المذاكرة',
                        5 => 'اختبارات مستمرة',
                        6 => 'محتوى متكامل ومنظم',
                        7 => 'تحديث مستمر حسب المنهج',
                        8 => 'مجتمع طلابي ضخم',
                    ];
                @endphp
                <div class="why-card">
                    <p class="text-left text-5xl font-black leading-none" dir="ltr">{{ $i }}</p>
                    <p class="mt-10 min-h-16 text-xl font-black leading-9">
                        {{ setting('why.card' . $i, $whyDefaults[$i]) }}
                    </p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ============================ الكورسات المتاحة ============================ --}}
    <section class="mx-auto max-w-[90rem] px-5 pb-16 sm:px-8">
        <div class="flex flex-wrap items-center justify-between gap-6">
            <h2 class="text-3xl font-black leading-snug text-slate-900 dark:text-white sm:text-4xl">
                {{ setting('courses.title', 'كورساتنا المتاحة للعام 2025/2026') }}
            </h2>
            <a href="{{ route('courses.index') }}"
               class="rounded-md border-2 border-slate-900 bg-slate-900 px-10 py-4 text-base font-black text-white shadow-md transition hover:bg-transparent hover:text-slate-900 dark:border-white dark:bg-white dark:text-night-950 dark:hover:bg-transparent dark:hover:text-white">
                {{ setting('courses.all_button', 'الكل') }}
            </a>
        </div>

        @if ($featured->isEmpty())
            <div class="mt-10 rounded-xl bg-slate-100 py-14 text-center dark:bg-night-900">
                <p class="flex items-center justify-center gap-3 text-2xl font-black text-slate-700 dark:text-slate-200">
                    <svg class="size-9 text-mint-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ setting('courses.empty', 'سيتم اضافه المحاضرات قريبًا...') }}
                </p>
            </div>
        @else
            <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($featured as $course)
                    @include('partials.course-card', ['course' => $course])
                @endforeach
            </div>
        @endif
    </section>

    {{-- ============================ المميزات الثلاث ============================ --}}
    <section class="mx-auto max-w-[90rem] px-5 pb-20 sm:px-8">
        <div class="grid gap-6 lg:grid-cols-3">
            @php
                $features = [
                    ['key' => 'f1', 'title' => 'تنظيم الدروس والوحدات', 'text' => 'كورسات مُقسّمة لوحدات صغيرة علشان تذاكر بترتيب وتفضّل مُتابع بسهولة.',
                     'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>', 'tint' => 'text-mint-600 bg-mint-100 dark:bg-mint-500/10 dark:text-mint-400'],
                    ['key' => 'f2', 'title' => 'دروس بالفيديو والصور التوضيحية', 'text' => 'شروحات مصوّرة مُفصلة مع رسومات توضيحية وأسئلة شائعة مُجاب عنها.',
                     'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>', 'tint' => 'text-flame-600 bg-flame-50 dark:bg-flame-500/10 dark:text-flame-400'],
                    ['key' => 'f3', 'title' => 'تطبيقات وتمارين تفاعلية', 'text' => 'تمارين تفاعلية بعد كل درس علشان تثبت المعلومة وتختبر نفسك.',
                     'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>', 'tint' => 'text-flame-600 bg-flame-50 dark:bg-flame-500/10 dark:text-flame-400'],
                ];
            @endphp

            @foreach ($features as $f)
                <div class="rounded-2xl border border-slate-300/60 bg-surface p-7 shadow-md transition duration-300 hover:-translate-y-1 hover:shadow-xl dark:border-night-700/50 dark:bg-night-900">
                    <span class="grid size-12 place-items-center rounded-xl {{ $f['tint'] }}">
                        <svg class="size-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">{!! $f['icon'] !!}</svg>
                    </span>
                    <h3 class="mt-5 text-xl font-black text-slate-900 dark:text-white">
                        {{ setting('features.' . $f['key'] . '_title', $f['title']) }}
                    </h3>
                    <p class="mt-3 text-sm font-semibold leading-7 text-slate-500 dark:text-slate-400">
                        {{ setting('features.' . $f['key'] . '_text', $f['text']) }}
                    </p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ============================ عن المدرس ============================ --}}
    <section class="mx-auto max-w-[90rem] px-5 pb-20 sm:px-8">
        <h2 class="text-4xl font-black text-slate-900 dark:text-white sm:text-5xl">
            {{ setting('about.title', 'عن م/حاتم سميكة') }}
        </h2>

        <div class="mt-10 grid items-end gap-10 lg:grid-cols-2">
            {{-- المميزات + صورة المدرس (يمين) --}}
            <div class="order-1 lg:order-2">
                <div class="grid grid-cols-3 gap-4">
                    @php
                        $aboutFeatures = [
                            ['key' => 'f1', 'label' => 'شروحات فيديو تفصيلية.', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/>'],
                            ['key' => 'f2', 'label' => 'تمارين تفاعلية.', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0112 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5"/>'],
                            ['key' => 'f3', 'label' => 'اختبارات وواجبات دورية.', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>'],
                        ];
                    @endphp

                    @foreach ($aboutFeatures as $af)
                        <div class="text-center">
                            <span class="mx-auto grid size-16 place-items-center rounded-2xl bg-slate-100 text-slate-800 shadow-sm dark:bg-night-850 dark:text-slate-100">
                                <svg class="size-8" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">{!! $af['icon'] !!}</svg>
                            </span>
                            <p class="mt-4 text-sm font-extrabold text-slate-700 dark:text-slate-300">
                                {{ setting('about.' . $af['key'], $af['label']) }}
                            </p>
                        </div>
                    @endforeach
                </div>

                @if (setting_image('about.image'))
                    <img src="{{ setting_image('about.image') }}" alt="{{ setting('about.title') }}"
                         class="mx-auto mt-6 max-h-[26rem] w-auto object-contain drop-shadow-xl">
                @endif
            </div>

            {{-- اللوحة الحمراء (يسار) --}}
            <div class="order-2 flex min-h-72 items-start rounded-2xl bg-flame-600 p-8 shadow-lg sm:p-12 lg:order-1 lg:min-h-[30rem]">
                <p class="text-2xl font-black leading-[1.9] text-white sm:text-3xl sm:leading-[1.9]">
                    {{ setting('about.text', 'صحصح شوية وشد حيلك معانا... هنمشيها سوا خطوة بخطوة لحد ما تلم المنهج وتبقى لعبه في ايدك .') }}
                </p>
            </div>
        </div>
    </section>
@endsection
