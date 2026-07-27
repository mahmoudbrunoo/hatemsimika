@extends('layouts.admin')

@section('title', 'أسئلة: ' . $exam->title . ' — الإدارة')
@section('page-title', 'أسئلة الامتحان')

@section('page-actions')
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('admin.exams.edit', $exam) }}" class="btn-secondary btn-sm">إعدادات الامتحان</a>
        <a href="{{ route('admin.exams.index') }}" class="btn-secondary btn-sm">رجوع للامتحانات</a>
    </div>
@endsection

@section('page')
    {{-- بطاقة بيانات الامتحان --}}
    <div class="card-pad mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h2 class="text-lg font-extrabold text-slate-900 dark:text-white">{{ $exam->title }}</h2>
            <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">
                {{ $exam->typeLabel() }} — المدة: {{ $exam->duration_minutes }} دقيقة — نسبة النجاح: {{ $exam->passingPercent() }}%
            </p>
        </div>
        <div class="flex gap-6 text-center">
            <div>
                <p class="stat-value">{{ $questions->count() }}</p>
                <p class="stat-label">سؤال</p>
            </div>
            <div>
                <p class="stat-value">{{ rtrim(rtrim(number_format($questions->sum('points'), 2), '0'), '.') }}</p>
                <p class="stat-label">إجمالي الدرجات</p>
            </div>
        </div>
    </div>

    {{-- إضافة سؤال جديد --}}
    <div class="card-pad mb-6" x-data="{ qtype: '{{ old('type', 'mcq') }}' }">
        <h2 class="mb-4 font-extrabold text-slate-900 dark:text-white">إضافة سؤال جديد</h2>

        <form method="POST" action="{{ route('admin.questions.store', $exam) }}" enctype="multipart/form-data" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @csrf

            <div>
                <label for="type" class="label">نوع السؤال</label>
                <select id="type" name="type" class="input" x-model="qtype" required>
                    <option value="mcq">اختيار من متعدد</option>
                    <option value="essay">مقالي</option>
                </select>
                @error('type')<p class="error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="subject" class="label">المادة/الفرع (اختياري)</label>
                <input id="subject" name="subject" type="text" value="{{ old('subject') }}" class="input" maxlength="60"
                       placeholder="مثال: نحو — أدب — بلاغة">
                @error('subject')<p class="error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="points" class="label">الدرجة</label>
                <input id="points" name="points" type="number" step="0.25" min="0.25" max="100"
                       value="{{ old('points', 1) }}" class="input" dir="ltr" required>
                @error('points')<p class="error">{{ $message }}</p>@enderror
            </div>

            <div class="sm:col-span-2 lg:col-span-4">
                <label for="body" class="label">نص السؤال</label>
                <textarea id="body" name="body" rows="3" class="input" required>{{ old('body') }}</textarea>
                @error('body')<p class="error">{{ $message }}</p>@enderror
            </div>

            <div class="sm:col-span-2">
                <label for="image" class="label">صورة السؤال (اختياري)</label>
                <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp" class="input">
                @error('image')<p class="error">{{ $message }}</p>@enderror
            </div>

            <div class="sm:col-span-2">
                <label for="audio" class="label">تسجيل صوتي للسؤال (اختياري)</label>
                <input id="audio" name="audio" type="file" accept="audio/mpeg,audio/wav,audio/ogg,audio/mp4,.mp3,.wav,.ogg,.m4a" class="input">
                @error('audio')<p class="error">{{ $message }}</p>@enderror
            </div>

            {{-- الاختيارات (للأسئلة الاختيارية فقط) --}}
            <div class="sm:col-span-2 lg:col-span-4" x-show="qtype === 'mcq'">
                <p class="label">الاختيارات — حدد الإجابة الصحيحة، واملأ الاختيارات بالترتيب (اختياران على الأقل)</p>
                @error('options')<p class="error">{{ $message }}</p>@enderror
                @error('correct')<p class="error">{{ $message }}</p>@enderror

                <div class="mt-2 space-y-3">
                    @for ($i = 0; $i < 4; $i++)
                        <div class="grid gap-3 rounded-xl border border-slate-200 p-3 dark:border-slate-800 sm:grid-cols-[auto_1fr_1fr_1fr]">
                            <label class="flex cursor-pointer items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300" title="الإجابة الصحيحة">
                                <input type="radio" name="correct" value="{{ $i }}"
                                       class="size-4 border-slate-300 text-emerald-600 focus:ring-emerald-500/40 dark:border-slate-700 dark:bg-slate-950"
                                       @checked((string) old('correct') === (string) $i)>
                                صحيحة
                            </label>
                            <div>
                                <input name="options[{{ $i }}][body]" type="text" value="{{ old("options.$i.body") }}"
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
                    @endfor
                </div>
            </div>

            <div class="sm:col-span-2">
                <label for="hint" class="label">تلميح للطالب (اختياري)</label>
                <textarea id="hint" name="hint" rows="2" class="input">{{ old('hint') }}</textarea>
                @error('hint')<p class="error">{{ $message }}</p>@enderror
            </div>

            <div class="sm:col-span-2">
                <label for="explanation" class="label">شرح الإجابة (اختياري)</label>
                <textarea id="explanation" name="explanation" rows="2" class="input">{{ old('explanation') }}</textarea>
                @error('explanation')<p class="error">{{ $message }}</p>@enderror
            </div>

            <div class="sm:col-span-2">
                <label for="explanation_image" class="label">صورة شرح الإجابة (اختياري)</label>
                <input id="explanation_image" name="explanation_image" type="file" accept="image/jpeg,image/png,image/webp" class="input">
                @error('explanation_image')<p class="error">{{ $message }}</p>@enderror
            </div>

            <div class="sm:col-span-2">
                <label for="explanation_video_url" class="label">رابط فيديو شرح الإجابة (اختياري)</label>
                <input id="explanation_video_url" name="explanation_video_url" type="url"
                       value="{{ old('explanation_video_url') }}" class="input" dir="ltr" placeholder="https://...">
                @error('explanation_video_url')<p class="error">{{ $message }}</p>@enderror
            </div>

            <div class="sm:col-span-2 lg:col-span-4">
                <button type="submit" class="btn-primary">إضافة السؤال</button>
            </div>
        </form>
    </div>

    {{-- جدول الأسئلة --}}
    <div class="table-box">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>النوع</th>
                    <th>نص السؤال</th>
                    <th>الاختيارات</th>
                    <th>الدرجة</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($questions as $question)
                    <tr>
                        <td class="font-bold">{{ $loop->iteration }}</td>
                        <td>
                            @if ($question->isMcq())
                                <span class="badge-sky">اختياري</span>
                            @else
                                <span class="badge-amber">مقالي</span>
                            @endif
                        </td>
                        <td class="max-w-md">
                            {{ \Illuminate\Support\Str::limit($question->body, 70) }}
                            <span class="mt-1 flex flex-wrap gap-1">
                                @if ($question->image_path)<span class="badge-gray">صورة</span>@endif
                                @if ($question->audio_path)<span class="badge-gray">صوت</span>@endif
                                @if ($question->hint)<span class="badge-gray">تلميح</span>@endif
                                @if ($question->subject)<span class="badge-gray">{{ $question->subject }}</span>@endif
                            </span>
                        </td>
                        <td>{{ $question->isMcq() ? $question->options->count() : '—' }}</td>
                        <td>{{ rtrim(rtrim(number_format((float) $question->points, 2), '0'), '.') }}</td>
                        <td>
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('admin.questions.edit', $question) }}" class="btn-secondary btn-sm">تعديل</a>
                                <form method="POST" action="{{ route('admin.questions.destroy', $question) }}"
                                      onsubmit="return confirm('هل أنت متأكد من حذف هذا السؤال؟')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-danger btn-sm">حذف</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-slate-500 dark:text-slate-400">لا توجد أسئلة بعد — أضف أول سؤال من النموذج بالأعلى.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
