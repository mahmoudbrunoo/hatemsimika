@extends('layouts.app')

@section('title', $course->title)

@section('content')
    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6">
        <div class="grid gap-8 lg:grid-cols-3">

            {{-- تفاصيل الكورس --}}
            <div class="lg:col-span-2">
                <span class="badge-sky">{{ $course->yearLabel() }}</span>
                <span class="badge-gray mr-1">{{ $course->categoryLabel() }}</span>

                <h1 class="mt-3 text-3xl font-black text-slate-900 dark:text-white">{{ $course->title }}</h1>

                @if ($course->description)
                    <p class="mt-4 leading-8 text-slate-600 dark:text-slate-300">{{ $course->description }}</p>
                @endif

                <div class="mt-6 flex flex-wrap gap-6 text-sm font-bold text-slate-600 dark:text-slate-300">
                    <span>📚 {{ $course->lectures->count() }} محاضرة</span>
                    <span>🎬 {{ $course->totalVideos() }} فيديو</span>
                    <span>⏱️ {{ floor($course->totalDurationSeconds() / 3600) }} ساعة {{ intdiv($course->totalDurationSeconds() % 3600, 60) }} دقيقة إجمالي</span>
                    <span>✅ نسبة النجاح المطلوبة {{ $course->passing_percent }}%</span>
                </div>

                {{-- تقسيمة المنهج --}}
                @if ($course->syllabus)
                    <div class="card-pad mt-8">
                        <h2 class="text-lg font-extrabold text-slate-900 dark:text-white">تقسيمة المنهج</h2>
                        <p class="mt-3 whitespace-pre-line leading-8 text-slate-600 dark:text-slate-300">{{ $course->syllabus }}</p>
                    </div>
                @endif

                {{-- المحاضرات --}}
                <div class="mt-8">
                    <h2 class="text-lg font-extrabold text-slate-900 dark:text-white">محتوى الكورس</h2>
                    <div class="mt-4 space-y-3">
                        @forelse ($course->lectures as $i => $lecture)
                            <div class="card flex items-center gap-4 p-4">
                                <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-brand-50 font-black text-brand-600 dark:bg-brand-500/10 dark:text-brand-300">{{ $i + 1 }}</span>
                                <div class="min-w-0 flex-1">
                                    <h3 class="truncate font-bold text-slate-900 dark:text-white">{{ $lecture->title }}</h3>
                                    @if ($lecture->description)
                                        <p class="truncate text-sm text-slate-500 dark:text-slate-400">{{ $lecture->description }}</p>
                                    @endif
                                </div>
                                @if ($lecture->is_free_preview)
                                    <span class="badge-green shrink-0">معاينة مجانية</span>
                                @else
                                    <svg class="size-5 shrink-0 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                @endif
                            </div>
                        @empty
                            <div class="card-pad text-center text-slate-500 dark:text-slate-400">سيتم إضافة المحاضرات قريباً.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- بطاقة الاشتراك --}}
            <div>
                <div class="card sticky top-24 overflow-hidden">
                    <div class="aspect-video bg-slate-100 dark:bg-slate-800">
                        @if ($course->thumbnail_path)
                            <img src="{{ $course->thumbnail_path }}" alt="" class="size-full object-cover">
                        @else
                            <div class="grid size-full place-items-center bg-gradient-to-br from-brand-500 to-brand-800 text-5xl font-black text-white/90">{{ mb_substr($course->title, 0, 1) }}</div>
                        @endif
                    </div>
                    <div class="p-5">
                        {{-- السعر بخصم السلاش --}}
                        @php $isFree = $course->effectivePrice() <= 0; @endphp
                        <div class="flex items-baseline gap-2">
                            @if ($isFree)
                                <span class="text-3xl font-black text-emerald-600">مجاني</span>
                                @if ((float) $course->price > 0)
                                    <span class="text-lg font-bold text-slate-400 line-through">{{ egp($course->price) }}</span>
                                @endif
                            @elseif ($course->discount_price !== null)
                                <span class="text-3xl font-black text-brand-600">{{ egp($course->discount_price) }}</span>
                                <span class="text-lg font-bold text-slate-400 line-through">{{ egp($course->price) }}</span>
                                <span class="badge-red">وفر {{ $course->discountPercent() }}%</span>
                            @else
                                <span class="text-3xl font-black text-brand-600">{{ egp($course->price) }}</span>
                            @endif
                        </div>

                        @if ($course->access_days)
                            <p class="mt-2 text-sm font-semibold text-slate-500 dark:text-slate-400">صلاحية الاشتراك: {{ $course->access_days }} يوم</p>
                        @endif

                        <div class="mt-5">
                            @if ($enrolled)
                                <a href="{{ route('student.learn.course', $course) }}" class="btn-success w-full py-3 text-base">أكمل التعلم</a>
                            @elseif (auth()->check())
                                @if ($isFree)
                                    <form method="POST" action="{{ route('student.checkout.free', $course) }}">
                                        @csrf
                                        <button type="submit" class="btn-success w-full py-3 text-base">ابدأ الكورس الآن — اشترك مجاناً</button>
                                    </form>
                                @else
                                    <a href="{{ route('student.checkout.course', $course) }}" class="btn-primary w-full py-3 text-base">اشترك الآن</a>
                                @endif
                            @else
                                <a href="{{ route('register') }}" class="btn-primary w-full py-3 text-base">{{ $isFree ? 'سجل وابدأ الكورس مجاناً' : 'سجل واشترك الآن' }}</a>
                            @endif
                        </div>

                        <ul class="mt-5 space-y-2.5 text-sm font-semibold text-slate-600 dark:text-slate-300">
                            <li class="flex items-center gap-2">✅ فيديوهات بجودة عالية</li>
                            <li class="flex items-center gap-2">✅ ملفات PDF لكل محاضرة</li>
                            <li class="flex items-center gap-2">✅ واجبات وامتحانات بتصحيح فوري</li>
                            <li class="flex items-center gap-2">✅ بنك أخطاء شخصي (اختبر أخطاءك)</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
