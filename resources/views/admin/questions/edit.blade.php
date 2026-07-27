@extends('layouts.admin')

@section('title', 'تعديل سؤال — ' . $exam->title)
@section('page-title', 'تعديل سؤال')

@section('page-actions')
    <a href="{{ route('admin.questions.index', $exam) }}" class="btn-secondary btn-sm">رجوع لأسئلة الامتحان</a>
@endsection

@section('page')
    @php
        $correctIndex = old('correct');
        if ($correctIndex === null) {
            $found = $question->options->values()->search(fn ($option) => $option->is_correct);
            $correctIndex = $found === false ? null : $found;
        }
        $rowsCount = max(4, $question->options->count());
    @endphp

    <p class="mb-6 text-sm font-semibold text-slate-500 dark:text-slate-400">
        الامتحان: <span class="font-extrabold text-slate-700 dark:text-slate-200">{{ $exam->title }}</span> — {{ $exam->typeLabel() }}
    </p>

    <div class="card-pad" x-data="{ qtype: '{{ old('type', $question->type) }}' }">
        <form method="POST" action="{{ route('admin.questions.update', $question) }}" enctype="multipart/form-data" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @csrf
            @method('PUT')

            <div>
                <label for="type" class="label">نوع السؤال</label>
                <select id="type" name="type" class="input" x-model="qtype" required>
                    <option value="mcq">اختيار من متعدد</option>
                    <option value="essay">مقالي</option>
                </select>
                <p class="mt-1 text-xs font-medium text-slate-400 dark:text-slate-500">التحويل إلى مقالي يحذف الاختيارات الحالية.</p>
                @error('type')<p class="error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="subject" class="label">المادة/الفرع (اختياري)</label>
                <input id="subject" name="subject" type="text" value="{{ old('subject', $question->subject) }}" class="input" maxlength="60">
                @error('subject')<p class="error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="points" class="label">الدرجة</label>
                <input id="points" name="points" type="number" step="0.25" min="0.25" max="100"
                       value="{{ old('points', $question->points) }}" class="input" dir="ltr" required>
                @error('points')<p class="error">{{ $message }}</p>@enderror
            </div>

            <div class="sm:col-span-2 lg:col-span-4">
                <label for="body" class="label">نص السؤال</label>
                <textarea id="body" name="body" rows="3" class="input" required>{{ old('body', $question->body) }}</textarea>
                @error('body')<p class="error">{{ $message }}</p>@enderror
            </div>

            <div class="sm:col-span-2">
                <label for="image" class="label">صورة السؤال {{ $question->image_path ? '(ارفع صورة جديدة للاستبدال)' : '(اختياري)' }}</label>
                <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp" class="input">
                @if ($question->image_path)
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($question->image_path) }}" alt="صورة السؤال"
                         class="mt-2 h-24 rounded-xl border border-slate-200 object-contain dark:border-slate-800">
                @endif
                @error('image')<p class="error">{{ $message }}</p>@enderror
            </div>

            <div class="sm:col-span-2">
                <label for="audio" class="label">تسجيل صوتي للسؤال {{ $question->audio_path ? '(ارفع ملفاً جديداً للاستبدال)' : '(اختياري)' }}</label>
                <input id="audio" name="audio" type="file" accept="audio/mpeg,audio/wav,audio/ogg,audio/mp4,.mp3,.wav,.ogg,.m4a" class="input">
                @if ($question->audio_path)
                    <audio controls class="mt-2 w-full" src="{{ \Illuminate\Support\Facades\Storage::url($question->audio_path) }}"></audio>
                @endif
                @error('audio')<p class="error">{{ $message }}</p>@enderror
            </div>

            {{-- الاختيارات (للأسئلة الاختيارية فقط) --}}
            <div class="sm:col-span-2 lg:col-span-4" x-show="qtype === 'mcq'">
                <p class="label">الاختيارات — حدد الإجابة الصحيحة، واملأ الاختيارات بالترتيب (اختياران على الأقل)</p>
                <p class="mb-2 rounded-xl bg-amber-50 px-4 py-2.5 text-xs font-semibold text-amber-700 dark:bg-amber-500/10 dark:text-amber-300">
                    ملحوظة: عند الحفظ يعاد إنشاء الاختيارات — صور/صوتيات الاختيارات الحالية لن تُحفظ إلا لو أعدت رفعها.
                </p>
                @error('options')<p class="error">{{ $message }}</p>@enderror
                @error('correct')<p class="error">{{ $message }}</p>@enderror

                <div class="mt-2 space-y-3">
                    @for ($i = 0; $i < $rowsCount; $i++)
                        @php $option = $question->options->values()->get($i); @endphp
                        <div class="rounded-xl border {{ $option?->is_correct ? 'border-emerald-300 dark:border-emerald-500/40' : 'border-slate-200 dark:border-slate-800' }} p-3">
                            <div class="grid gap-3 sm:grid-cols-[auto_1fr_1fr_1fr]">
                                <label class="flex cursor-pointer items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300" title="الإجابة الصحيحة">
                                    <input type="radio" name="correct" value="{{ $i }}"
                                           class="size-4 border-slate-300 text-emerald-600 focus:ring-emerald-500/40 dark:border-slate-700 dark:bg-slate-950"
                                           @checked((string) $correctIndex === (string) $i)>
                                    صحيحة
                                </label>
                                <div>
                                    <input name="options[{{ $i }}][body]" type="text"
                                           value="{{ old("options.$i.body", $option?->body) }}"
                                           class="input" placeholder="نص الاختيار {{ $i + 1 }}">
                                    @error("options.$i.body")<p class="error">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <input name="options[{{ $i }}][image]" type="file" accept="image/jpeg,image/png,image/webp"
                                           class="input" title="صورة الاختيار (اختياري)">
                                    @error("options.$i.image")<p class="error">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <input name="options[{{ $i }}][audio]" type="file" accept="audio/mpeg,audio/wav,audio/ogg,audio/mp4,.mp3,.wav,.ogg,.m4a"
                                           class="input" title="تسجيل صوتي للاختيار (اختياري)">
                                    @error("options.$i.audio")<p class="error">{{ $message }}</p>@enderror
                                </div>
                            </div>

                            @if ($option && ($option->image_path || $option->audio_path))
                                <div class="mt-3 flex flex-wrap items-center gap-4 border-t border-slate-100 pt-3 dark:border-slate-800">
                                    <span class="text-xs font-bold text-slate-400">الوسائط الحالية:</span>
                                    @if ($option->image_path)
                                        <img src="{{ \Illuminate\Support\Facades\Storage::url($option->image_path) }}" alt="صورة الاختيار"
                                             class="h-14 rounded-lg border border-slate-200 object-contain dark:border-slate-800">
                                    @endif
                                    @if ($option->audio_path)
                                        <audio controls class="h-9" src="{{ \Illuminate\Support\Facades\Storage::url($option->audio_path) }}"></audio>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endfor
                </div>
            </div>

            <div class="sm:col-span-2">
                <label for="hint" class="label">تلميح للطالب (اختياري)</label>
                <textarea id="hint" name="hint" rows="2" class="input">{{ old('hint', $question->hint) }}</textarea>
                @error('hint')<p class="error">{{ $message }}</p>@enderror
            </div>

            <div class="sm:col-span-2">
                <label for="explanation" class="label">شرح الإجابة (اختياري)</label>
                <textarea id="explanation" name="explanation" rows="2" class="input">{{ old('explanation', $question->explanation) }}</textarea>
                @error('explanation')<p class="error">{{ $message }}</p>@enderror
            </div>

            <div class="sm:col-span-2">
                <label for="explanation_image" class="label">صورة شرح الإجابة {{ $question->explanation_image ? '(ارفع صورة جديدة للاستبدال)' : '(اختياري)' }}</label>
                <input id="explanation_image" name="explanation_image" type="file" accept="image/jpeg,image/png,image/webp" class="input">
                @if ($question->explanation_image)
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($question->explanation_image) }}" alt="صورة الشرح"
                         class="mt-2 h-24 rounded-xl border border-slate-200 object-contain dark:border-slate-800">
                @endif
                @error('explanation_image')<p class="error">{{ $message }}</p>@enderror
            </div>

            <div class="sm:col-span-2">
                <label for="explanation_video_url" class="label">رابط فيديو شرح الإجابة (اختياري)</label>
                <input id="explanation_video_url" name="explanation_video_url" type="url"
                       value="{{ old('explanation_video_url', $question->explanation_video_url) }}" class="input" dir="ltr" placeholder="https://...">
                @error('explanation_video_url')<p class="error">{{ $message }}</p>@enderror
            </div>

            <div class="flex gap-3 sm:col-span-2 lg:col-span-4">
                <button type="submit" class="btn-primary">حفظ السؤال</button>
                <a href="{{ route('admin.questions.index', $exam) }}" class="btn-secondary">إلغاء</a>
            </div>
        </form>
    </div>
@endsection
