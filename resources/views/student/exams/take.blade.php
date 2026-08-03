@extends('layouts.app')

@section('title', $exam->title . ' — ' . setting('site.name', 'منصة حاتم سميكة'))

@section('content')
    <div class="mx-auto max-w-4xl px-4 py-8" x-data="examTimer({{ $attempt->remainingSeconds() }}, 'exam-form')">

        {{-- الشريط العلوي الثابت: اسم الامتحان + المؤقت --}}
        <div class="card sticky top-20 z-30 mb-6 flex items-center justify-between gap-4 px-5 py-4">
            <div class="min-w-0">
                <h1 class="truncate text-lg font-black text-slate-900 dark:text-white">{{ $exam->title }}</h1>
                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                    {{ $questions->count() }} سؤال — بيتسلم تلقائياً لما الوقت يخلص
                </p>
            </div>
            <div class="shrink-0 text-left">
                <span class="block text-2xl font-black tabular-nums text-slate-900 dark:text-white"
                      dir="ltr" x-text="display" :class="danger && 'text-rose-600'"></span>
                <span class="block text-xs font-semibold text-slate-400 dark:text-slate-500">الوقت المتبقي</span>
            </div>
        </div>

        <form id="exam-form" method="POST" action="{{ route('student.exams.submit', $attempt) }}"
              enctype="multipart/form-data" class="space-y-6">
            @csrf

            @foreach ($questions as $question)
                <div class="card-pad">
                    {{-- رأس السؤال --}}
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="grid size-8 shrink-0 place-items-center rounded-xl bg-brand-600 text-sm font-black text-white">{{ $loop->iteration }}</span>
                        <span class="badge-gray">{{ $question->isMcq() ? 'اختيار من متعدد' : 'سؤال مقالي' }}</span>
                        <span class="badge-sky">{{ $question->points + 0 }} {{ ($question->points + 0) == 1 ? 'درجة' : 'درجات' }}</span>
                    </div>

                    {{-- نص السؤال والوسائط --}}
                    <p class="mt-4 whitespace-pre-line text-base font-bold leading-8 text-slate-900 dark:text-white">{{ $question->body }}</p>

                    @if ($question->image_path)
                        <img src="{{ $question->image_path }}" alt="صورة السؤال"
                             class="mt-3 max-h-80 rounded-xl border border-slate-300 object-contain dark:border-slate-700">
                    @endif

                    @if ($question->audio_path)
                        <audio controls class="mt-3 w-full" src="{{ $question->audio_path }}"></audio>
                    @endif

                    {{-- التلميح (لو الامتحان مفعّل التلميحات) --}}
                    @if ($exam->hints_enabled && $question->hint)
                        <div x-data="{ show: false }" class="mt-4">
                            <button type="button" @click="show = !show" class="btn-secondary btn-sm">
                                <span x-text="show ? 'إخفاء التلميح' : '💡 محتاج تلميح؟'"></span>
                            </button>
                            <div x-show="show" x-cloak x-transition
                                 class="mt-3 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm font-semibold leading-6 text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300">
                                {{ $question->hint }}
                            </div>
                        </div>
                    @endif

                    {{-- منطقة الإجابة --}}
                    @if ($question->isMcq())
                        <div class="mt-5 space-y-3" role="radiogroup" aria-label="اختيارات السؤال {{ $loop->iteration }}">
                            @foreach ($question->options as $option)
                                <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-300 p-3.5 transition
                                              hover:border-brand-400 hover:bg-brand-50/40
                                              has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50 has-[:checked]:ring-2 has-[:checked]:ring-brand-500/30
                                              dark:border-slate-700 dark:hover:bg-brand-500/5
                                              dark:has-[:checked]:border-brand-500 dark:has-[:checked]:bg-brand-500/10">
                                    <input type="radio"
                                           name="answers[{{ $question->id }}]"
                                           value="{{ $option->id }}"
                                           class="mt-1 size-5 shrink-0 border-slate-300 text-brand-600 focus:ring-brand-500/40 dark:border-slate-600 dark:bg-slate-950">
                                    <span class="min-w-0 flex-1">
                                        @if ($option->body)
                                            <span class="block text-sm font-semibold leading-7 text-slate-800 dark:text-slate-200">{{ $option->body }}</span>
                                        @endif
                                        @if ($option->image_path)
                                            <img src="{{ $option->image_path }}" alt="صورة الاختيار"
                                                 class="mt-2 max-h-48 rounded-lg border border-slate-300 object-contain dark:border-slate-700">
                                        @endif
                                        @if ($option->audio_path)
                                            <audio controls class="mt-2 w-full" src="{{ $option->audio_path }}"></audio>
                                        @endif
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    @else
                        <div class="mt-5 space-y-4">
                            <div>
                                <label for="essay-{{ $question->id }}" class="label">إجابتك</label>
                                <textarea id="essay-{{ $question->id }}"
                                          name="essays[{{ $question->id }}]"
                                          rows="6" class="input leading-7"
                                          placeholder="اكتب إجابتك هنا بالتفصيل..."></textarea>
                            </div>
                            <div>
                                <label for="essay-image-{{ $question->id }}" class="label">أو صوّر حلك بخط إيدك (اختياري)</label>
                                <input id="essay-image-{{ $question->id }}"
                                       name="essay_images[{{ $question->id }}]"
                                       type="file" accept="image/*"
                                       class="input cursor-pointer p-2 file:ml-3 file:cursor-pointer file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-1.5 file:text-xs file:font-bold file:text-brand-700 dark:file:bg-brand-500/10 dark:file:text-brand-300">
                                <p class="mt-1 text-xs font-medium text-slate-400 dark:text-slate-500">صورة واضحة للحل (JPG أو PNG)</p>
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach

            {{-- التسليم --}}
            <div class="card-pad text-center">
                <p class="mb-4 text-sm font-semibold text-slate-500 dark:text-slate-400">
                    راجع إجاباتك كويس — مفيش رجوع بعد التسليم.
                </p>
                <button type="submit" class="btn-primary w-full py-3.5 text-base"
                        onclick="return confirm('متأكد إنك عايز تسلم الامتحان؟ مش هتقدر تعدل إجاباتك بعد كده.')">
                    ✅ تسليم الامتحان
                </button>
            </div>
        </form>
    </div>
@endsection
