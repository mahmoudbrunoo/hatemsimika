@extends('layouts.app')

@section('title', $exam->title . ' — ' . setting('site.name', 'منصة حاتم سميكة'))

@section('content')
    {{-- محاكي الامتحان بواجهة المرجع: سؤال واحد في كل شاشة + لوحة تحكم وتنقل ملونة --}}
    <div class="mx-auto max-w-5xl px-4 py-8">
        <div class="exam-simulator flex flex-col space-y-5"
             x-data="examSimulator({
                 seconds: {{ $attempt->remainingSeconds() }},
                 formId: 'exam-form',
                 storageKey: 'exam-draft-{{ $attempt->id }}',
                 exitUrl: '{{ route('student.exams.show', $exam) }}',
                 draftUrl: '{{ route('student.exams.draft', $attempt) }}',
                 questionIds: @js($questions->pluck('id')),
             })">

            {{-- الرجوع لصفحة الامتحان --}}
            <a class="border-2 smooth flex items-center bg-transparent border-none gap-1 text-primary-800 hover:text-primary-500 dark:text-primary-200 dark:hover:text-primary-400 transition rounded-md px-4 py-2"
               href="{{ route('student.exams.show', $exam) }}">
                <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="22" height="22" preserveAspectRatio="xMidYMid meet" viewBox="0 0 24 24">
                    <path fill="currentColor" d="M4 11v2h12l-5.5 5.5l1.42 1.42L19.84 12l-7.92-7.92L10.5 5.5L16 11z"/>
                </svg>
                <span class="font-semibold">الرجوع للامتحان</span>
            </a>

            {{-- عنوان الامتحان + مشاركة --}}
            <div class="flex items-center justify-between">
                <h1 class="text-4xl font-bold text-primary-800 dark:text-primary-100 smooth">{{ $exam->title }}</h1>
                <div class="flex items-center gap-2">
                    <button type="button" @click="share()"
                            class="text-primary-800 hover:text-primary-500 dark:text-primary-200 dark:hover:text-primary-400 transition duration-300"
                            aria-label="مشاركة الامتحان">
                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="28" height="28" preserveAspectRatio="xMidYMid meet" viewBox="0 0 24 24">
                            <path fill="currentColor" d="M18 16.08c-.76 0-1.44.3-1.96.77L8.91 12.7c.05-.23.09-.46.09-.7s-.04-.47-.09-.7l7.05-4.11c.54.5 1.25.81 2.04.81a3 3 0 0 0 3-3a3 3 0 0 0-3-3a3 3 0 0 0-3 3c0 .24.04.47.09.7L8.04 9.81C7.5 9.31 6.79 9 6 9a3 3 0 0 0-3 3a3 3 0 0 0 3 3c.79 0 1.5-.31 2.04-.81l7.12 4.15c-.05.21-.08.43-.08.66c0 1.61 1.31 2.91 2.92 2.91s2.92-1.3 2.92-2.91A2.92 2.92 0 0 0 18 16.08"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="space-y-4">
                {{-- وضع الحل العادي: سؤال بسؤال --}}
                <div x-show="!review" class="rounded-md border border-secondary-container smooth clr-text-primary bg-inner-container pt-10 h-full">
                    <form id="exam-form" method="POST" action="{{ route('student.exams.submit', $attempt) }}"
                          enctype="multipart/form-data">
                        @csrf

                        <div class="mx-4 rounded-2xl overflow-hidden smooth clr-text-primary bg-primary-container relative z-30">

                            {{-- لوحة التحكم: المؤقت + الأزرار + الإحصائيات + لوحة الأسئلة --}}
                            <div class="flex-center-both">
                                <div class="w-full max-w-2xl bg-third-container rounded-md px-5 py-2 shadow-md space-y-3 smooth clr-text-primary">

                                    {{-- المؤقت التنازلي --}}
                                    <div class="flex-center-both">
                                        <div class="bg-rose-500 rounded-md font-w-bold font-com font-h3 en space-y-1 px-3 text-white pt-1 pb-2">
                                            <div class="bg-third-container font-smaller font-alm flex-center-both rounded-md px-2 pb-1 clr-text-secondary smooth">: باقي من الزمن</div>
                                            <div class="flex gap-2 flex-center-both">
                                                <div x-text="minutesDisplay">--</div>
                                                <div class="font-com -mt-0.5">:</div>
                                                <div x-text="secondsDisplay">--</div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- إنهاء / استكمال لاحقًا --}}
                                    <div class="w-full max-w-xs mx-auto py-3 space-y-5">
                                        <button type="button" @click="finish()"
                                                class="border-2 smooth w-full bg-blue-500 border-blue-500 dark:bg-blue-600 dark:border-blue-600 hover:bg-transparent dark:hover:bg-transparent hover:text-blue-500 dark:hover:text-blue-600 text-white rounded-md px-4 py-2">إنهاء الاختبار</button>
                                        <button type="button" @click="later()"
                                                class="border-2 smooth w-full bg-yellow-400 border-yellow-400 dark:bg-yellow-500 dark:border-yellow-500 hover:bg-transparent dark:hover:bg-transparent hover:text-yellow-400 dark:hover:text-yellow-500 text-white rounded-md px-4 py-2">استكمال الاختبار لاحقًا</button>
                                    </div>

                                    {{-- عرض الإجابات — بيقلب الحاوية لوضع مراجعة الإجابات المختارة --}}
                                    <div class="flex-center-both mb-4">
                                        <button type="button" @click="toggleReview()"
                                                class="border-2 smooth w-full max-w-xs bg-primary-500 border-primary-500 dark:bg-primary-600 dark:border-primary-600 hover:bg-transparent dark:hover:bg-transparent hover:text-primary-500 dark:hover:text-primary-600 text-white rounded-md px-4 py-2"
                                                x-text="review ? 'الرجوع لتعديل الأسئلة' : 'عرض الإجابات'">عرض الإجابات</button>
                                    </div>

                                    {{-- إحصائيات المحاولة --}}
                                    <div class="flex-center-both flex-col max-w-xs mx-auto space-y-2">
                                        <div class="flex justify-between w-full">
                                            <div>اجمالي درجات الامتحان :</div>
                                            <div class="flex-center-both">
                                                <div class="rounded-md bg-primary-container smooth clr-text-primary px-2 py-1">{{ $exam->totalPoints() + 0 }}</div>
                                            </div>
                                        </div>
                                        <div class="flex justify-between w-full">
                                            <div>عدد الاسئلة :</div>
                                            <div class="flex-center-both">
                                                <div class="rounded-md bg-primary-container smooth clr-text-primary px-2 py-1">{{ $questions->count() }}</div>
                                            </div>
                                        </div>
                                        <div class="flex justify-between w-full">
                                            <div>عدد الاسئلة التي تم فتحها :</div>
                                            <div class="flex-center-both">
                                                <div class="rounded-md bg-yellow-500 text-white px-2 py-1" x-text="openedCount">1</div>
                                            </div>
                                        </div>
                                        <div class="flex justify-between w-full">
                                            <div>عدد الاسئلة غير المحلولة :</div>
                                            <div class="flex-center-both">
                                                <div class="rounded-md bg-rose-500 text-white px-2 py-1" x-text="unsolvedCount">1</div>
                                            </div>
                                        </div>
                                        <div class="flex justify-between w-full">
                                            <div>عدد الاسئلة المحلولة :</div>
                                            <div class="flex-center-both">
                                                <div class="rounded-md bg-blue-500 text-white px-2 py-1" x-text="answeredCount">0</div>
                                            </div>
                                        </div>
                                        <div class="h-px max-w-sm w-full bg-text-secondary smooth mx-auto opacity-50"></div>
                                        <div class="flex justify-between w-full">
                                            <div>السؤال الحالي :</div>
                                            <div class="flex-center-both">
                                                <div class="rounded-md bg-primary-container smooth clr-text-primary px-2 py-1 border-2 border-rose-500" x-text="current + 1">1</div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- لوحة التنقل بين الأسئلة --}}
                                    <div class="grid grid-cols-12 gap-1 mt-4">
                                        <template x-for="i in count" :key="i">
                                            <button type="button" @click="go(i - 1)"
                                                    class="smooth rounded-lg border-2"
                                                    :class="current === i - 1 ? 'border-blue-500' : 'border-transparent'"
                                                    :aria-current="current === i - 1 ? 'page' : null">
                                                <div class="rounded-md text-white hover-shadow flex-center-both smooth"
                                                     :class="paletteClass(i - 1)" x-text="i"></div>
                                            </button>
                                        </template>
                                    </div>

                                    <div class="h-px max-w-sm w-full bg-text-secondary smooth mx-auto opacity-50"></div>

                                    {{-- السابق / التالي --}}
                                    <div class="">
                                        <div class="flex-center-both gap-4">
                                            <button type="button" @click="prev()" :disabled="current === 0"
                                                    class="border-2 smooth bg-teal-400 border-teal-400 dark:bg-teal-500 dark:border-teal-500 hover:bg-transparent dark:hover:bg-transparent hover:text-teal-400 dark:hover:text-teal-500 text-white rounded-md px-4 py-2 disabled:opacity-60 disabled:pointer-events-none">السابق</button>
                                            <button type="button" @click="next()" :disabled="current === count - 1"
                                                    class="border-2 smooth px-6 py-2 bg-teal-400 border-teal-400 dark:bg-teal-500 dark:border-teal-500 hover:bg-transparent dark:hover:bg-transparent hover:text-teal-400 dark:hover:text-teal-500 text-white rounded-md disabled:opacity-60 disabled:pointer-events-none">التالي</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- كروت الأسئلة: كلها داخل نفس النموذج، والظاهر منها سؤال واحد --}}
                            @foreach ($questions as $question)
                                @php
                                    $points = $question->points + 0;
                                    $pointsLabel = $points == 1
                                        ? 'درجة واحدة'
                                        : ($points == 2 ? 'درجتان' : ($points >= 3 && $points <= 10 ? $points . ' درجات' : $points . ' درجة'));
                                @endphp

                                <div x-show="current === {{ $loop->index }}" @if (!$loop->first) x-cloak @endif>
                                    <div class="rounded-md shadow-lg py-6 md:py-10 px-4 md:px-10 space-y-6">

                                        {{-- درجة السؤال --}}
                                        <div class="flex-center-both">
                                            <div class="rounded-full bg-yellow-500 text-white px-10 py-1">{{ $pointsLabel }}</div>
                                        </div>

                                        {{-- نص السؤال --}}
                                        <div class="font-h2 font-w-bold flex gap-2">
                                            <span class="flex-center-both text-blue-400 font-h1 -translate-y-px">
                                                <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" role="img" width="1em" height="1em" preserveAspectRatio="xMidYMid meet" viewBox="0 0 24 24">
                                                    <g stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
                                                        <path fill="currentColor" fill-opacity="0" stroke-dasharray="60" d="M12 3c4.97 0 9 4.03 9 9c0 4.97 -4.03 9 -9 9c-4.97 0 -9 -4.03 -9 -9c0 -4.97 4.03 -9 9 -9Z">
                                                            <animate fill="freeze" attributeName="stroke-dashoffset" dur="0.6s" values="60;0"/>
                                                            <animate fill="freeze" attributeName="fill-opacity" begin="1s" dur="0.15s" to=".3"/>
                                                        </path>
                                                        <g fill="none">
                                                            <path stroke-dasharray="18" stroke-dashoffset="18" d="M9 10c0 -1.66 1.34 -3 3 -3c1.66 0 3 1.34 3 3c0 0.98 -0.47 1.85 -1.2 2.4c-0.73 0.55 -1.3 0.6 -1.8 1.6">
                                                                <animate fill="freeze" attributeName="stroke-dashoffset" begin="0.7s" dur="0.3s" to="0"/>
                                                            </path>
                                                            <path stroke-dasharray="4" stroke-dashoffset="4" d="M12 17v0.01">
                                                                <animate fill="freeze" attributeName="stroke-dashoffset" begin="0.7s" dur="0.2s" to="0"/>
                                                            </path>
                                                        </g>
                                                    </g>
                                                </svg>
                                            </span>
                                            <div class="whitespace-pre-line">{{ $question->body }}</div>
                                        </div>

                                        {{-- وسائط السؤال --}}
                                        @if ($question->image_path)
                                            <div class="flex-center-both">
                                                <img src="{{ $question->image_path }}" alt="صورة السؤال"
                                                     class="max-h-80 rounded-xl border border-secondary-container object-contain smooth">
                                            </div>
                                        @endif

                                        @if ($question->audio_path)
                                            <audio controls class="w-full" src="{{ $question->audio_path }}"></audio>
                                        @endif

                                        {{-- التلميح (لو الامتحان مفعّل التلميحات) --}}
                                        @if ($exam->hints_enabled && $question->hint)
                                            <div x-data="{ show: false }">
                                                <button type="button" @click="show = !show"
                                                        class="border-2 smooth bg-yellow-400 border-yellow-400 dark:bg-yellow-500 dark:border-yellow-500 hover:bg-transparent dark:hover:bg-transparent hover:text-yellow-400 dark:hover:text-yellow-500 text-white rounded-md px-4 py-2 text-sm">
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
                                            <div class="px-2 space-y-5 sm:space-y-0 flex sm:flex-row flex-col gap-2">
                                                <div class="w-full space-y-1">
                                                    <div class="flex font-h1 pb-1 -mt-4 text-blue-500 -mr-2 opacity-0" aria-hidden="true">
                                                        <span class="flex-center-both">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" preserveAspectRatio="xMidYMid meet" viewBox="0 0 24 24">
                                                                <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3c4.97 0 9 4.03 9 9">
                                                                    <animateTransform attributeName="transform" dur="1.5s" repeatCount="indefinite" type="rotate" values="0 12 12;360 12 12"/>
                                                                </path>
                                                            </svg>
                                                        </span>
                                                    </div>
                                                    <fieldset class="space-y-3" role="radiogroup" aria-label="اختيارات السؤال {{ $loop->iteration }}">
                                                        @foreach ($question->options as $option)
                                                            <div class="relative">
                                                                <input id="question_{{ $question->id }}_{{ $loop->index }}" type="radio"
                                                                       name="answers[{{ $question->id }}]" value="{{ $option->id }}"
                                                                       data-label="{{ $option->body ? \Illuminate\Support\Str::limit($option->body, 80) : 'الاختيار رقم ' . $loop->iteration }}"
                                                                       @checked(($savedAnswers[$question->id]['option_id'] ?? null) == $option->id)
                                                                       class="peer sr-only">
                                                                <label for="question_{{ $question->id }}_{{ $loop->index }}"
                                                                       class="flex items-center smooth gap-4 rounded-2xl border border-gray-200 px-5 py-4 text-base md:text-lg font-medium shadow-sm cursor-pointer hover:shadow-md hover:-translate-y-[1px] focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary-500 peer-checked:border-primary-600 peer-checked:bg-primary-50 peer-checked:shadow-md peer-checked:[&_.radio-circle]:border-primary-600 peer-checked:[&_.radio-circle]:bg-primary-600 peer-checked:[&_.radio-dot]:opacity-100 peer-disabled:opacity-60 peer-disabled:cursor-not-allowed dark:border-night-700 dark:peer-checked:border-primary-600 dark:peer-checked:bg-primary-950/80">
                                                                    <span aria-hidden="true" class="radio-circle ml-4 grid h-7 w-7 place-items-center rounded-full border-2 border-gray-300 transition-colors">
                                                                        <span class="radio-dot h-3 w-3 rounded-full bg-white opacity-0 transition-opacity"></span>
                                                                    </span>
                                                                    <span dir="auto" class="flex-1 min-w-0 w-full text-sm md:text-base whitespace-normal break-normal hyphens-none">
                                                                        @if ($option->body)
                                                                            {{ $option->body }}
                                                                        @endif
                                                                        @if ($option->image_path)
                                                                            <img src="{{ $option->image_path }}" alt="صورة الاختيار"
                                                                                 class="mt-2 max-h-48 rounded-lg border border-secondary-container object-contain">
                                                                        @endif
                                                                        @if ($option->audio_path)
                                                                            <audio controls class="mt-2 w-full" src="{{ $option->audio_path }}"></audio>
                                                                        @endif
                                                                    </span>
                                                                </label>
                                                            </div>
                                                        @endforeach
                                                    </fieldset>
                                                </div>
                                            </div>
                                        @else
                                            <div class="px-2 space-y-4">
                                                <div>
                                                    <label for="essay-{{ $question->id }}" class="label">إجابتك</label>
                                                    <textarea id="essay-{{ $question->id }}"
                                                              name="essays[{{ $question->id }}]"
                                                              rows="6" class="input leading-7"
                                                              placeholder="اكتب إجابتك هنا بالتفصيل...">{{ $savedAnswers[$question->id]['essay_text'] ?? '' }}</textarea>
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
                                </div>
                            @endforeach
                        </div>
                    </form>
                </div>

                {{-- وضع مراجعة الإجابات المختارة (عرض الإجابات) — بنفس بنية التصميم المرجعي --}}
                <div x-show="review" x-cloak class="rounded-md border border-secondary-container smooth clr-text-primary bg-inner-container py-10 h-full">
                    <div class="flex-center-both px-4">
                        <div class="w-full max-w-2xl bg-third-container rounded-md px-5 py-2 shadow-md space-y-3 smooth clr-text-primary">

                            {{-- المؤقت — شغال ومتزامن مع السيرفر حتى أثناء المراجعة --}}
                            <div class="flex-center-both">
                                <div class="bg-rose-500 rounded-md font-w-bold font-com font-h3 en space-y-1 px-3 text-white pt-1 pb-2">
                                    <div class="bg-third-container font-smaller font-alm flex-center-both rounded-md px-2 pb-1 clr-text-secondary smooth">: باقي من الزمن</div>
                                    <div class="flex space-x-2 flex-center-both">
                                        <div x-text="minutesDisplay">--</div>
                                        <div class="font-com -mt-0.5">:</div>
                                        <div x-text="secondsDisplay">--</div>
                                    </div>
                                </div>
                            </div>

                            {{-- شريط الأدوات: إنهاء / استكمال لاحقاً --}}
                            <div class="w-full max-w-xs mx-auto py-3 space-y-5">
                                <button type="button" @click="finish()"
                                        class="border-2 smooth w-full bg-blue-500 border-blue-500 dark:bg-blue-600 dark:border-blue-600 hover:bg-opacity-0 dark:hover:bg-opacity-0 dark:bg-opacity-100 bg-opacity-100 hover:text-blue-500 dark:hover:text-blue-600 text-white rounded-md px-4 py-2">إنهاء الاختبار</button>
                                <button type="button" @click="later()"
                                        class="border-2 smooth w-full bg-yellow-400 border-yellow-400 dark:bg-yellow-500 dark:border-yellow-500 hover:bg-opacity-0 dark:hover:bg-opacity-0 hover:text-yellow-400 dark:hover:text-yellow-500 text-white rounded-md px-4 py-2">استكمال الاختبار لاحقًا</button>
                            </div>

                            {{-- الرجوع لواجهة حل الأسئلة --}}
                            <div class="flex-center-both mb-4">
                                <button type="button" @click="toggleReview()"
                                        class="border-2 smooth w-full max-w-xs bg-teal-400 border-teal-400 dark:bg-teal-500 dark:border-teal-500 hover:bg-opacity-0 dark:hover:bg-opacity-0 hover:text-teal-400 dark:hover:text-teal-500 text-white rounded-md px-4 py-2">الرجوع لتعديل الأسئلة</button>
                            </div>

                            {{-- قائمة مراجعة الإجابات --}}
                            <div class="w-full smooth">
                                <div class="mb-4 smooth">
                                    <h3 class="text-lg font-semibold clr-text-primary text-center mb-2 smooth">مراجعة الإجابات</h3>
                                    <p class="text-sm clr-text-secondary text-center smooth">تأكد من إجاباتك قبل تسليم الاختبار</p>
                                </div>

                                <div class="max-h-96 overflow-y-auto border border-secondary-container rounded-lg smooth"
                                     style="scrollbar-width: thin; scrollbar-color: rgb(29, 172, 98) rgb(243, 244, 246);">
                                    <div class="space-y-4 p-4 smooth">
                                        @foreach ($questions as $question)
                                            <div class="bg-third-container rounded-lg border border-secondary-container p-4 smooth">
                                                <div class="flex items-start space-x-4 space-x-reverse smooth">
                                                    <div class="flex-shrink-0 smooth">
                                                        <span class="inline-flex items-center justify-center w-8 h-8 bg-primary-500 text-white rounded-full text-sm font-medium smooth">{{ $loop->iteration }}</span>
                                                    </div>
                                                    <div class="flex-1 min-w-0 smooth">
                                                        <h3 class="text-lg font-semibold clr-text-primary mb-2 smooth whitespace-pre-line">{{ $question->body }}</h3>

                                                        @if ($question->image_path)
                                                            <div class="mb-4 smooth">
                                                                <div class="text-sm clr-text-secondary mb-2 smooth">صورة السؤال:</div>
                                                                <div class="max-w-xs smooth">
                                                                    <div class="w-full flex flex-col justify-center space-y-2 pt-4">
                                                                        <img alt="صورة السؤال" class="h-60 m-auto cursor-pointer rounded-md object-contain" src="{{ $question->image_path }}">
                                                                        <div class="flex-col flex-center-both w-full space-y-4">
                                                                            <div class="flex-center-both w-full">
                                                                                <a href="{{ $question->image_path }}" target="_blank" rel="noopener"
                                                                                   class="border-2 smooth bg-blue-500 border-blue-500 dark:bg-blue-600 dark:border-blue-600 hover:bg-opacity-0 dark:hover:bg-opacity-0 dark:bg-opacity-100 bg-opacity-100 hover:text-blue-500 dark:hover:text-blue-600 text-white rounded-md px-4 py-2">عرض الصورة</a>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endif

                                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 mb-3 smooth"></div>

                                                        {{-- الإجابة المختارة حالياً (بتتحدث لحظياً مع أي تعديل) --}}
                                                        <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium smooth"
                                                             :class="hasSelection({{ $question->id }}) ? 'text-primary-600 bg-primary-100' : 'text-secondary-600 bg-secondary-100'">
                                                            <span class="ml-2 smooth">الإجابة المختارة:</span>
                                                            <span class="font-bold smooth" x-text="selectionLabel({{ $question->id }})">لم يتم الإجابة</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- التسليم النهائي من شاشة المراجعة --}}
                                <div class="flex-center-both pt-5 pb-3">
                                    <button type="button" @click="finish()"
                                            class="border-2 smooth w-full max-w-xs bg-rose-500 border-rose-500 dark:bg-rose-600 dark:border-rose-600 hover:bg-opacity-0 dark:hover:bg-opacity-0 hover:text-rose-500 dark:hover:text-rose-600 text-white rounded-md px-4 py-2 font-w-bold">تسليم الاختبار نهائياً</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- نافذة تأكيد التسليم النهائي --}}
            <div x-show="modal === 'finish'" x-cloak @keydown.escape.window="modal = null"
                 class="fixed inset-0 z-50 flex-center-both p-4">
                <div class="absolute inset-0 bg-black/60" @click="modal = null"></div>
                <div class="relative w-full max-w-md rounded-2xl border border-secondary-container bg-inner-container p-6 text-center shadow-xl space-y-4 smooth clr-text-primary"
                     role="alertdialog" aria-modal="true" aria-label="تأكيد تسليم الاختبار">
                    <div class="text-4xl">📤</div>
                    <h3 class="text-lg font-w-bold">تسليم الاختبار نهائياً</h3>
                    <p class="text-sm clr-text-secondary leading-7">هل أنت متأكد من إنهاء وتسليم الاختبار نهائياً؟ لن تتمكن من تعديل إجاباتك بعد التسليم.</p>
                    <div class="flex-center-both gap-3 pt-2">
                        <button type="button" @click="confirmFinish()"
                                class="border-2 smooth bg-rose-500 border-rose-500 hover:bg-opacity-0 hover:text-rose-500 text-white rounded-md px-6 py-2 font-w-bold">تأكيد التسليم</button>
                        <button type="button" @click="modal = null"
                                class="border-2 smooth bg-slate-400 border-slate-400 hover:bg-opacity-0 hover:text-slate-500 text-white rounded-md px-6 py-2">إلغاء</button>
                    </div>
                </div>
            </div>

            {{-- نافذة تحذير الخروج واستكمال الاختبار لاحقاً --}}
            <div x-show="modal === 'later'" x-cloak @keydown.escape.window="modal = null"
                 class="fixed inset-0 z-50 flex-center-both p-4">
                <div class="absolute inset-0 bg-black/60" @click="modal = null"></div>
                <div class="relative w-full max-w-md rounded-2xl border border-secondary-container bg-inner-container p-6 text-center shadow-xl space-y-4 smooth clr-text-primary"
                     role="alertdialog" aria-modal="true" aria-label="تحذير الخروج من الاختبار">
                    <div class="text-4xl">⚠️</div>
                    <h3 class="text-lg font-w-bold">استكمال الاختبار لاحقاً</h3>
                    <p class="text-sm leading-7 rounded-xl border border-amber-200 bg-amber-50 p-3 font-semibold text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300">
                        تنبيه: في حال الخروج واستكمال الاختبار لاحقاً، يستمر عداد الوقت بالعمل ولن يتوقف. هل أنت أصلًا متأكد من الخروج؟
                    </p>
                    <p class="text-xs clr-text-secondary">إجاباتك المختارة محفوظة تلقائياً وهترجعلها زي ما سيبتها بالظبط.</p>
                    <div class="flex-center-both gap-3 pt-2">
                        <button type="button" @click="confirmLater()" :disabled="leaving"
                                class="border-2 smooth bg-yellow-400 border-yellow-400 dark:bg-yellow-500 dark:border-yellow-500 hover:bg-opacity-0 hover:text-yellow-500 text-white rounded-md px-6 py-2 font-w-bold disabled:opacity-60 disabled:pointer-events-none"
                                x-text="leaving ? 'جاري الحفظ...' : 'تأكيد الخروج'">تأكيد الخروج</button>
                        <button type="button" @click="modal = null"
                                class="border-2 smooth bg-slate-400 border-slate-400 hover:bg-opacity-0 hover:text-slate-500 text-white rounded-md px-6 py-2">إلغاء</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
