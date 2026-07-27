@extends('layouts.student')

@section('title', 'اختبر أخطاءك — ' . setting('site.name', 'منصة حاتم سميكة'))
@section('page-title', 'امتحان خاص بيك')

@section('page')
    {{-- شرح الفكرة --}}
    <div class="card-pad">
        <div class="flex items-start gap-4">
            <div class="grid size-12 shrink-0 place-items-center rounded-2xl bg-brand-50 text-2xl dark:bg-brand-500/10">🎯</div>
            <div>
                <h2 class="text-lg font-extrabold text-slate-900 dark:text-white">امتحان خاص بيك — من أخطائك أنت</h2>
                <p class="mt-1.5 text-sm font-semibold leading-6 text-slate-500 dark:text-slate-400">
                    ده بنك شخصي بيجمع كل الأسئلة اللي غلطت فيها في الكويزات والامتحانات.
                    ابدأ امتحان من أخطائك — ولما تجاوب السؤال صح، بيتشال من البنك تلقائياً.
                    هنجمعلك لحد <span dir="ltr">55</span> سؤال من أحدث أخطائك في كل مرة.
                </p>
            </div>
        </div>
    </div>

    @if ($count === 0 && $subjects->isEmpty())
        {{-- مفيش أخطاء خالص — مبروك --}}
        <div class="card-pad mt-6 text-center">
            <div class="mx-auto grid size-14 place-items-center rounded-2xl bg-emerald-50 text-2xl dark:bg-emerald-500/10">🏆</div>
            <h2 class="mt-4 text-lg font-extrabold text-slate-900 dark:text-white">مبروك! مفيش أخطاء في بنكك</h2>
            <p class="mt-2 text-sm font-semibold text-slate-500 dark:text-slate-400">
                يا إما لسه محلتش امتحانات، يا إما حليت كل أخطائك صح — استمر كده يا بطل!
            </p>
            <a href="{{ route('student.courses') }}" class="btn-primary mt-5">روح لكورساتي</a>
        </div>
    @else
        {{-- إحصائيات --}}
        <div class="mt-6 grid gap-4 sm:grid-cols-2">
            <div class="stat-card">
                <span class="stat-value">{{ $count }}</span>
                <span class="stat-label">
                    سؤال مستني إعادة الاختبار
                    @if ($subject) في {{ $subject }} @endif
                </span>
            </div>
            <div class="stat-card">
                <span class="stat-value">{{ $subjects->count() }}</span>
                <span class="stat-label">مادة / فرع فيها أخطاء</span>
            </div>
        </div>

        {{-- فلترة حسب المادة --}}
        @if ($subjects->isNotEmpty())
            <div class="card-pad mt-6">
                <p class="label">اختار المادة اللي عايز تتمرن عليها</p>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('student.personal') }}"
                       class="{{ $subject === null ? 'btn-primary btn-sm' : 'btn-secondary btn-sm' }}">كل المواد</a>
                    @foreach ($subjects as $s)
                        <a href="{{ route('student.personal', ['subject' => $s]) }}"
                           class="{{ $subject === $s ? 'btn-primary btn-sm' : 'btn-secondary btn-sm' }}">{{ $s }}</a>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- بدء الامتحان --}}
        <div class="card-pad mt-6 text-center">
            @if ($count > 0)
                <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">
                    جاهز؟ الامتحان هيتكون من {{ min($count, 55) }} سؤال من أحدث أخطائك
                    @if ($subject) في {{ $subject }} @else في كل المواد @endif
                    — والوقت بيتحسب على حسب عدد الأسئلة.
                </p>
                <form method="POST" action="{{ route('student.personal.start') }}" class="mt-5">
                    @csrf
                    <input type="hidden" name="subject" value="{{ $subject }}">
                    <button type="submit" class="btn-primary px-8 py-3 text-base">🚀 ابدأ امتحانك الخاص</button>
                </form>
            @else
                <div class="mx-auto grid size-12 place-items-center rounded-2xl bg-emerald-50 text-2xl dark:bg-emerald-500/10">✅</div>
                <p class="mt-3 text-sm font-bold text-slate-700 dark:text-slate-200">
                    مفيش أخطاء متبقية في {{ $subject ?? 'الفلتر ده' }} — جرب مادة تانية من فوق.
                </p>
            @endif
            @error('subject')<p class="error mt-3">{{ $message }}</p>@enderror
        </div>

        <p class="mt-4 text-center text-sm font-semibold text-slate-500 dark:text-slate-400">
            عايز تراجع أخطاءك بهدوء من غير مؤقت؟
            <a href="{{ route('student.bank') }}" class="font-extrabold text-brand-600 hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300">افتح بنك أسئلتي</a>
        </p>
    @endif
@endsection
