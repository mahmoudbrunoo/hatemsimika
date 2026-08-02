@extends('layouts.student')

@section('title', 'بنك أسئلتي — ' . setting('site.name', 'منصة حاتم سميكة'))
@section('page-title', 'بنك الاسئلة')

@section('page')
    @if ($mistakes->isEmpty())
        <div class="card-pad text-center">
            <div class="mx-auto grid size-14 place-items-center rounded-2xl bg-emerald-50 text-2xl dark:bg-emerald-500/10">🏆</div>
            <h2 class="mt-4 text-lg font-extrabold text-slate-900 dark:text-white">بنكك فاضي — وده خبر حلو!</h2>
            <p class="mt-2 text-sm font-semibold text-slate-500 dark:text-slate-400">
                أول ما تغلط في سؤال في أي كويز أو امتحان، هيتسجل هنا عشان تراجعه وتتمرن عليه.
            </p>
            <a href="{{ route('student.courses') }}" class="btn-primary mt-5">روح لكورساتي</a>
        </div>
    @else
        <p class="mb-4 text-sm font-semibold text-slate-500 dark:text-slate-400">
            دي الأسئلة اللي غلطت فيها قبل كده — راجعها هنا بهدوء، أو
            <a href="{{ route('student.personal') }}" class="font-extrabold text-brand-600 hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300">ابدأ امتحان "اختبر أخطاءك"</a>
            عشان تتأكد إنك فهمتها.
        </p>

        <div class="space-y-4">
            @foreach ($mistakes as $mistake)
                @continue($mistake->question === null)
                @php $question = $mistake->question; @endphp

                <div class="card-pad">
                    {{-- رأس البطاقة: المادة + عدد مرات الغلط + الحالة --}}
                    <div class="flex flex-wrap items-center gap-2">
                        @if ($mistake->subject)
                            <span class="badge-sky">{{ $mistake->subject }}</span>
                        @endif
                        <span class="badge-red">غلطت فيه {{ $mistake->times_wrong }} {{ $mistake->times_wrong === 1 ? 'مرة' : 'مرات' }}</span>
                        @if ($mistake->resolved)
                            <span class="badge-green">اتحلّت ✔</span>
                        @else
                            <span class="badge-amber">لسه محتاجة مراجعة</span>
                        @endif
                        @if ($mistake->last_wrong_at)
                            <span class="mr-auto text-xs font-medium text-slate-400 dark:text-slate-500">
                                آخر غلطة: {{ $mistake->last_wrong_at->translatedFormat('d M Y') }}
                            </span>
                        @endif
                    </div>

                    {{-- نص السؤال والوسائط --}}
                    <p class="mt-4 whitespace-pre-line font-bold leading-7 text-slate-900 dark:text-white">{{ $question->body }}</p>

                    @if ($question->image_path)
                        <img src="{{ Storage::url($question->image_path) }}" alt="صورة السؤال" class="mt-3 max-h-72 rounded-xl border border-slate-300 object-contain dark:border-slate-700">
                    @endif

                    @if ($question->audio_path)
                        <audio controls class="mt-3 w-full" src="{{ Storage::url($question->audio_path) }}"></audio>
                    @endif

                    {{-- الاختيارات مع تمييز الإجابة الصحيحة --}}
                    @if ($question->isMcq() && $question->options->isNotEmpty())
                        <div class="mt-4 space-y-2">
                            @foreach ($question->options as $option)
                                <div class="flex items-start gap-3 rounded-xl border px-3.5 py-2.5 text-sm
                                            {{ $option->is_correct
                                                ? 'border-emerald-300 bg-emerald-50 dark:border-emerald-500/40 dark:bg-emerald-500/10'
                                                : 'border-slate-300 dark:border-slate-700' }}">
                                    <div class="min-w-0 flex-1">
                                        @if ($option->body)
                                            <p class="font-semibold text-slate-800 dark:text-slate-200">{{ $option->body }}</p>
                                        @endif
                                        @if ($option->image_path)
                                            <img src="{{ Storage::url($option->image_path) }}" alt="" class="mt-2 max-h-40 rounded-lg object-contain">
                                        @endif
                                        @if ($option->audio_path)
                                            <audio controls class="mt-2 w-full" src="{{ Storage::url($option->audio_path) }}"></audio>
                                        @endif
                                    </div>
                                    @if ($option->is_correct)
                                        <span class="badge-green shrink-0">الإجابة الصحيحة</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- شرح الإجابة إن وجد --}}
                    @if ($question->explanation || $question->explanation_image || $question->explanation_video_url)
                        <div x-data="{ show: false }" class="mt-4">
                            <button type="button" @click="show = !show" class="btn-secondary btn-sm">
                                <span x-text="show ? 'إخفاء الشرح' : 'اعرض شرح الإجابة 💡'"></span>
                            </button>
                            <div x-show="show" x-cloak x-transition
                                 class="mt-3 rounded-xl border border-brand-200 bg-brand-50 p-4 text-sm dark:border-brand-500/30 dark:bg-brand-500/10">
                                @if ($question->explanation)
                                    <p class="whitespace-pre-line font-semibold leading-6 text-slate-700 dark:text-slate-200">{{ $question->explanation }}</p>
                                @endif
                                @if ($question->explanation_image)
                                    <img src="{{ Storage::url($question->explanation_image) }}" alt="شرح بالصورة" class="mt-3 max-h-72 rounded-lg object-contain">
                                @endif
                                @if ($question->explanation_video_url)
                                    <a href="{{ $question->explanation_video_url }}" target="_blank" rel="noopener" class="btn-secondary btn-sm mt-3">🎬 اتفرج على فيديو الشرح</a>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="mt-4">{{ $mistakes->links() }}</div>
    @endif
@endsection
