@extends('layouts.student')

@section('title', 'الملف الشخصي — ' . setting('site.name', 'منصة حاتم سميكة'))
@section('page-title', 'ملف المستخدم')

@section('page')
    {{-- البيانات المسجلة --}}
    <div class="card-pad">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-4">
                @if ($user->avatar_path)
                    <img src="{{ Storage::url($user->avatar_path) }}" alt="{{ $user->name }}" class="size-16 rounded-2xl object-cover">
                @else
                    <div class="grid size-16 place-items-center rounded-2xl bg-brand-50 text-2xl font-black text-brand-600 dark:bg-brand-500/10 dark:text-brand-300">
                        {{ mb_substr($user->name, 0, 1) }}
                    </div>
                @endif
                <div>
                    <h2 class="text-lg font-extrabold text-slate-900 dark:text-white">{{ $user->name }}</h2>
                    <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">{{ $user->yearLabel() }}</p>
                </div>
            </div>

            @php
                $statusBadge = match ($user->status) {
                    \App\Models\User::STATUS_APPROVED => 'badge-green',
                    \App\Models\User::STATUS_PENDING => 'badge-amber',
                    default => 'badge-red',
                };
            @endphp
            <span class="{{ $statusBadge }}">{{ $user->statusLabel() }}</span>
        </div>

        {{-- بيانات أساسية للعرض فقط — باقي البيانات والمستندات محفوظة لدى الإدارة --}}
        <dl class="mt-6 grid gap-x-6 gap-y-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ([
                ['الاسم', $user->name, null],
                ['البريد الإلكتروني', $user->email, 'ltr'],
                ['رقم الموبايل', $user->phone, 'ltr'],
            ] as [$dt, $dd, $dir])
                <div>
                    <dt class="text-xs font-bold text-slate-400 dark:text-slate-500">{{ $dt }}</dt>
                    <dd class="mt-0.5 text-right text-sm font-semibold text-slate-800 dark:text-slate-200" @if ($dir) dir="{{ $dir }}" @endif>
                        {{ $dd ?: '—' }}
                    </dd>
                </div>
            @endforeach
        </dl>

        <div class="mt-6 flex items-start gap-3 rounded-2xl border border-brand-200 bg-brand-50 px-4 py-3 text-sm font-semibold text-brand-800 dark:border-brand-500/30 dark:bg-brand-500/10 dark:text-brand-300">
            <svg class="mt-0.5 size-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p>بياناتك محفوظة ومؤمنة — لو محتاج تعدل أي بيانات أو تغير كلمة المرور، كلم الدعم الفني وهنظبطها لك فوراً.</p>
        </div>
    </div>

    {{-- تنبيه الاستعلام عن الرصيد — كما في المرجع --}}
    <div class="mx-auto mt-8 w-fit rounded-2xl bg-brand-500 p-3.5 shadow-md">
        <a href="{{ route('student.wallet') }}"
           class="block rounded-xl bg-mint-500 px-6 py-3 text-sm font-black text-white transition hover:brightness-110">
            لو شحنت كود , يمكنك استعلام عن الرصيد هنا
        </a>
    </div>

    {{-- احصائيات كورساتك --}}
    <div class="mt-12">
        <div class="mx-auto h-0.5 w-2/3 rounded-full bg-mint-600/70"></div>

        <h2 class="mt-10 flex items-center justify-center gap-4 text-center text-3xl font-black">
            <svg class="size-9 text-brand-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 21.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"/></svg>
            <span><span class="text-brand-500">احصائيات</span> <span class="text-slate-900 dark:text-white">كورساتك</span></span>
            <svg class="size-9 text-brand-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 21.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"/></svg>
        </h2>

        <div class="mt-10 grid gap-10 sm:grid-cols-3">
            {{-- عدد الفيديوهات --}}
            <div class="flex flex-col items-center gap-5">
                <div class="donut" style="--donut-value: {{ $stats['videos_percent'] }}; --donut-color: #800033;">
                    <div class="donut-inner">
                        <div>
                            <p class="text-2xl font-black text-slate-900 dark:text-white">{{ $stats['videos_percent'] }} %</p>
                            <p class="mt-1 text-sm font-extrabold text-slate-500 dark:text-slate-300">
                                {{ $stats['videos_percent'] >= 85 ? 'اشطر واحد ❤' : ($stats['videos_percent'] >= 40 ? 'فاضل حبة!' : 'يلا ابدأ!') }}
                            </p>
                        </div>
                    </div>
                </div>
                <p class="text-lg font-black text-slate-900 dark:text-white">عدد الفيديوهات شوفتها</p>
                <p class="flex items-center overflow-hidden rounded-full bg-surface text-sm font-black shadow dark:bg-white">
                    <span class="rounded-full bg-brand-500 px-4 py-2 text-white">{{ $stats['videos_watched'] }} فيديو</span>
                    <span class="px-4 py-2 text-night-900">من {{ $stats['videos_total'] }}</span>
                </p>
            </div>

            {{-- عدد الاختبارات --}}
            <div class="flex flex-col items-center gap-5">
                <div class="donut" style="--donut-value: {{ $stats['exams_percent'] }}; --donut-color: #9c6a82;">
                    <div class="donut-inner">
                        <div>
                            <p class="text-2xl font-black text-slate-900 dark:text-white">{{ $stats['exams_percent'] }} %</p>
                            <p class="mt-1 text-sm font-extrabold text-slate-500 dark:text-slate-300">
                                {{ $stats['exams_percent'] >= 85 ? 'اشطر واحد ❤' : ($stats['exams_percent'] >= 40 ? 'فاضل حبة!' : 'يلا ابدأ!') }}
                            </p>
                        </div>
                    </div>
                </div>
                <p class="text-lg font-black text-slate-900 dark:text-white">عدد الاختبارات اللي خلصتها</p>
                <p class="flex items-center overflow-hidden rounded-full bg-surface text-sm font-black shadow dark:bg-white">
                    <span class="rounded-full bg-brand-500 px-4 py-2 text-white">{{ $stats['exams_done'] }} امتحان</span>
                    <span class="px-4 py-2 text-night-900">من {{ $stats['exams_total'] }}</span>
                </p>
            </div>

            {{-- متوسط النتائج --}}
            <div class="flex flex-col items-center gap-5">
                <div class="donut" style="--donut-value: {{ $stats['avg_percent'] }}; --donut-color: #c08a45;">
                    <div class="donut-inner">
                        <div>
                            <p class="text-2xl font-black text-slate-900 dark:text-white">{{ $stats['avg_percent'] }} %</p>
                            <p class="mt-1 text-sm font-extrabold text-slate-500 dark:text-slate-300">
                                {{ $stats['avg_percent'] >= 85 ? 'اشطر واحد ❤' : ($stats['avg_percent'] >= 40 ? 'فاضل حبة!' : 'يلا ابدأ!') }}
                            </p>
                        </div>
                    </div>
                </div>
                <p class="text-lg font-black text-slate-900 dark:text-white">متوسط النتائج اللي جبتها</p>
            </div>
        </div>
    </div>

@endsection
