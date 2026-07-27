@extends('layouts.admin')

@section('title', 'إدارة محتوى: ' . $lecture->title . ' — الإدارة')
@section('page-title', 'إدارة محتوى المحاضرة')

@section('page-actions')
    <a href="{{ route('admin.lectures.index', $course) }}" class="btn-secondary btn-sm">رجوع لمحاضرات الكورس</a>
@endsection

@section('page')
    @if ($errors->any())
        <div class="card-pad mb-6 border-rose-200 dark:border-rose-500/30">
            <p class="mb-2 font-extrabold text-rose-600 dark:text-rose-400">راجع الأخطاء التالية:</p>
            <ul class="list-inside list-disc space-y-1 text-sm font-semibold text-rose-600 dark:text-rose-400">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <p class="mb-6 text-sm font-semibold text-slate-500 dark:text-slate-400">
        الكورس: <span class="font-extrabold text-slate-700 dark:text-slate-200">{{ $course->title }}</span> — {{ $course->yearLabel() }}
    </p>

    <div class="space-y-6">

        {{-- ============================ 1) إعدادات المحاضرة --}}
        <section x-data="{ open: true }" class="card">
            <button type="button" @click="open = !open" class="flex w-full items-center justify-between p-5 text-right sm:p-6">
                <h2 class="text-lg font-extrabold text-slate-900 dark:text-white">إعدادات المحاضرة</h2>
                <svg class="size-5 text-slate-400 transition" :class="open && 'rotate-180'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </button>

            <div x-show="open" class="border-t border-slate-100 p-5 dark:border-slate-800 sm:p-6">
                <form method="POST" action="{{ route('admin.lectures.update', $lecture) }}" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @csrf
                    @method('PUT')

                    <div class="sm:col-span-2">
                        <label for="lecture_title" class="label">اسم المحاضرة</label>
                        <input id="lecture_title" name="title" type="text" value="{{ $lecture->title }}" class="input" required>
                    </div>

                    <div>
                        <label for="lecture_position" class="label">الترتيب</label>
                        <input id="lecture_position" name="position" type="number" min="0" value="{{ $lecture->position }}" class="input" dir="ltr">
                    </div>

                    <div>
                        <label for="lecture_passing" class="label">نسبة النجاح % (اختياري)</label>
                        <input id="lecture_passing" name="passing_percent" type="number" min="1" max="100"
                               value="{{ $lecture->passing_percent }}" class="input" dir="ltr" placeholder="يورث من الكورس">
                    </div>

                    <div class="sm:col-span-2 lg:col-span-4">
                        <label for="lecture_description" class="label">وصف المحاضرة (اختياري)</label>
                        <textarea id="lecture_description" name="description" rows="3" class="input">{{ $lecture->description }}</textarea>
                    </div>

                    <div class="flex flex-wrap items-center gap-6 sm:col-span-2 lg:col-span-3">
                        <label class="flex cursor-pointer items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300">
                            <input type="hidden" name="is_published" value="0">
                            <input type="checkbox" name="is_published" value="1"
                                   class="size-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500/40 dark:border-slate-700 dark:bg-slate-950"
                                   @checked($lecture->is_published)>
                            منشورة
                        </label>
                        <label class="flex cursor-pointer items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300">
                            <input type="checkbox" name="is_free_preview" value="1"
                                   class="size-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500/40 dark:border-slate-700 dark:bg-slate-950"
                                   @checked($lecture->is_free_preview)>
                            معاينة مجانية
                        </label>
                    </div>

                    <div class="self-end">
                        <button type="submit" class="btn-primary w-full">حفظ الإعدادات</button>
                    </div>
                </form>
            </div>
        </section>

        {{-- ============================ 2) الفيديوهات --}}
        <section x-data="{ open: true }" class="card">
            <button type="button" @click="open = !open" class="flex w-full items-center justify-between p-5 text-right sm:p-6">
                <h2 class="text-lg font-extrabold text-slate-900 dark:text-white">
                    الفيديوهات
                    <span class="badge-sky mr-2">{{ $lecture->videos->count() }}</span>
                </h2>
                <svg class="size-5 text-slate-400 transition" :class="open && 'rotate-180'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </button>

            <div x-show="open" class="space-y-5 border-t border-slate-100 p-5 dark:border-slate-800 sm:p-6">

                {{-- الفيديوهات الحالية --}}
                @forelse ($lecture->videos as $video)
                    <div x-data="{ editing: false }" class="rounded-xl border border-slate-200 p-4 dark:border-slate-800">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div class="min-w-0">
                                <p class="font-bold text-slate-900 dark:text-white">{{ $video->position }}. {{ $video->title }}</p>
                                <p class="mt-1 text-xs font-semibold text-slate-500 dark:text-slate-400">
                                    المصدر: {{ ['youtube' => 'يوتيوب', 'vimeo' => 'فيميو', 'bunny' => 'Bunny', 'file' => 'ملف مرفوع'][$video->source] ?? $video->source }}
                                    — المدة: {{ $video->durationHuman() }}
                                    — <span dir="ltr">{{ \Illuminate\Support\Str::limit($video->url, 40) }}</span>
                                </p>
                            </div>
                            <div class="flex gap-2">
                                <button type="button" @click="editing = !editing" class="btn-secondary btn-sm" x-text="editing ? 'إغلاق التعديل' : 'تعديل'"></button>
                                <form method="POST" action="{{ route('admin.videos.destroy', $video) }}"
                                      onsubmit="return confirm('هل أنت متأكد من حذف هذا الفيديو؟')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-danger btn-sm">حذف</button>
                                </form>
                            </div>
                        </div>

                        {{-- نموذج تعديل الفيديو --}}
                        <form x-show="editing" style="display: none;" method="POST" action="{{ route('admin.videos.update', $video) }}"
                              class="mt-4 grid gap-4 border-t border-slate-100 pt-4 dark:border-slate-800 sm:grid-cols-2 lg:grid-cols-4">
                            @csrf
                            @method('PUT')

                            <div class="sm:col-span-2">
                                <label class="label">عنوان الفيديو</label>
                                <input name="title" type="text" value="{{ $video->title }}" class="input" required>
                            </div>

                            <div>
                                <label class="label">المصدر</label>
                                <select name="source" class="input" required>
                                    @foreach (['youtube' => 'يوتيوب', 'vimeo' => 'فيميو', 'bunny' => 'Bunny', 'file' => 'ملف مرفوع'] as $value => $label)
                                        <option value="{{ $value }}" @selected($video->source === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="label">المدة بالثواني</label>
                                <input name="duration_seconds" type="number" min="1" value="{{ $video->duration_seconds }}" class="input" dir="ltr" required>
                            </div>

                            <div class="sm:col-span-2 lg:col-span-4">
                                <label class="label">رابط/معرف الفيديو</label>
                                <input name="url" type="text" value="{{ $video->url }}" class="input" dir="ltr" required>
                            </div>

                            <div class="sm:col-span-2">
                                <label class="label">وصف الفيديو (اختياري)</label>
                                <textarea name="description" rows="2" class="input">{{ $video->description }}</textarea>
                            </div>

                            <div class="sm:col-span-2">
                                <label class="label">أهم النقاط المستفادة (اختياري)</label>
                                <textarea name="takeaways" rows="2" class="input">{{ $video->takeaways }}</textarea>
                            </div>

                            <div>
                                <button type="submit" class="btn-primary btn-sm">حفظ التعديل</button>
                            </div>
                        </form>
                    </div>
                @empty
                    <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">لا توجد فيديوهات بعد.</p>
                @endforelse

                {{-- إضافة فيديو جديد --}}
                <div class="rounded-xl border border-dashed border-slate-300 p-4 dark:border-slate-700">
                    <h3 class="mb-4 font-extrabold text-slate-900 dark:text-white">إضافة فيديو جديد</h3>
                    <form method="POST" action="{{ route('admin.videos.store', $lecture) }}" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        @csrf

                        <div class="sm:col-span-2">
                            <label for="video_title" class="label">عنوان الفيديو</label>
                            <input id="video_title" name="title" type="text" value="{{ old('title') }}" class="input" required>
                            @error('title')<p class="error">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="video_source" class="label">المصدر</label>
                            <select id="video_source" name="source" class="input" required>
                                @foreach (['youtube' => 'يوتيوب', 'vimeo' => 'فيميو', 'bunny' => 'Bunny', 'file' => 'ملف مرفوع'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('source', 'youtube') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('source')<p class="error">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="video_duration" class="label">المدة بالثواني</label>
                            <input id="video_duration" name="duration_seconds" type="number" min="1" value="{{ old('duration_seconds') }}"
                                   class="input" dir="ltr" placeholder="مثال: 1800" required>
                            @error('duration_seconds')<p class="error">{{ $message }}</p>@enderror
                        </div>

                        <div class="sm:col-span-2 lg:col-span-4">
                            <label for="video_url" class="label">رابط/معرف الفيديو</label>
                            <input id="video_url" name="url" type="text" value="{{ old('url') }}" class="input" dir="ltr"
                                   placeholder="https://youtu.be/..." required>
                            @error('url')<p class="error">{{ $message }}</p>@enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="video_description" class="label">وصف الفيديو (اختياري)</label>
                            <textarea id="video_description" name="description" rows="2" class="input">{{ old('description') }}</textarea>
                            @error('description')<p class="error">{{ $message }}</p>@enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="video_takeaways" class="label">أهم النقاط المستفادة (اختياري)</label>
                            <textarea id="video_takeaways" name="takeaways" rows="2" class="input">{{ old('takeaways') }}</textarea>
                            @error('takeaways')<p class="error">{{ $message }}</p>@enderror
                        </div>

                        <div class="self-end">
                            <button type="submit" class="btn-primary w-full">إضافة الفيديو</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        {{-- ============================ 3) الملفات --}}
        <section x-data="{ open: true }" class="card">
            <button type="button" @click="open = !open" class="flex w-full items-center justify-between p-5 text-right sm:p-6">
                <h2 class="text-lg font-extrabold text-slate-900 dark:text-white">
                    الملفات (PDF)
                    <span class="badge-gray mr-2">{{ $lecture->attachments->count() }}</span>
                </h2>
                <svg class="size-5 text-slate-400 transition" :class="open && 'rotate-180'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </button>

            <div x-show="open" class="space-y-4 border-t border-slate-100 p-5 dark:border-slate-800 sm:p-6">
                @forelse ($lecture->attachments as $attachment)
                    <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200 p-4 dark:border-slate-800">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">📄</span>
                            <div>
                                <p class="font-bold text-slate-900 dark:text-white">{{ $attachment->title }}</p>
                                <a href="{{ \Illuminate\Support\Facades\Storage::url($attachment->file_path) }}" target="_blank" rel="noopener"
                                   class="text-xs font-bold text-brand-600 hover:underline dark:text-brand-400">فتح الملف</a>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('admin.attachments.destroy', $attachment) }}"
                              onsubmit="return confirm('هل أنت متأكد من حذف هذا الملف؟')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-danger btn-sm">حذف</button>
                        </form>
                    </div>
                @empty
                    <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">لا توجد ملفات بعد.</p>
                @endforelse

                {{-- رفع ملف جديد --}}
                <div class="rounded-xl border border-dashed border-slate-300 p-4 dark:border-slate-700">
                    <h3 class="mb-4 font-extrabold text-slate-900 dark:text-white">رفع ملف جديد</h3>
                    <form method="POST" action="{{ route('admin.attachments.store', $lecture) }}" enctype="multipart/form-data"
                          class="grid gap-4 sm:grid-cols-[1fr_1fr_auto]">
                        @csrf

                        <div>
                            <label for="attachment_title" class="label">اسم الملف</label>
                            <input id="attachment_title" name="title" type="text" value="{{ old('title') }}" class="input"
                                   placeholder="مثال: ملزمة المحاضرة" required>
                            @error('title')<p class="error">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="attachment_file" class="label">ملف PDF (بحد أقصى 50 ميجا)</label>
                            <input id="attachment_file" name="file" type="file" accept="application/pdf" class="input" required>
                            @error('file')<p class="error">{{ $message }}</p>@enderror
                        </div>

                        <div class="self-end">
                            <button type="submit" class="btn-primary w-full">رفع الملف</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        {{-- ============================ 4) الواجب --}}
        <section x-data="{ open: true }" class="card">
            <button type="button" @click="open = !open" class="flex w-full items-center justify-between p-5 text-right sm:p-6">
                <h2 class="text-lg font-extrabold text-slate-900 dark:text-white">
                    الواجب
                    @if ($lecture->assignment)
                        <span class="badge-green mr-2">موجود</span>
                    @else
                        <span class="badge-gray mr-2">غير مضاف</span>
                    @endif
                </h2>
                <svg class="size-5 text-slate-400 transition" :class="open && 'rotate-180'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </button>

            <div x-show="open" class="border-t border-slate-100 p-5 dark:border-slate-800 sm:p-6">
                <form method="POST" action="{{ route('admin.assignments.save', $lecture) }}" enctype="multipart/form-data"
                      class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @csrf

                    <div class="sm:col-span-2">
                        <label for="assignment_title" class="label">عنوان الواجب</label>
                        <input id="assignment_title" name="title" type="text"
                               value="{{ old('title', $lecture->assignment?->title) }}" class="input" required>
                        @error('title')<p class="error">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="assignment_max_score" class="label">الدرجة الكلية</label>
                        <input id="assignment_max_score" name="max_score" type="number" min="1"
                               value="{{ old('max_score', $lecture->assignment?->max_score ?? 100) }}" class="input" dir="ltr" required>
                        @error('max_score')<p class="error">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="assignment_file" class="label">ملف الواجب PDF (اختياري)</label>
                        <input id="assignment_file" name="file" type="file" accept="application/pdf" class="input">
                        @if ($lecture->assignment?->file_path)
                            <a href="{{ \Illuminate\Support\Facades\Storage::url($lecture->assignment->file_path) }}" target="_blank" rel="noopener"
                               class="mt-1 inline-block text-xs font-bold text-brand-600 hover:underline dark:text-brand-400">الملف الحالي</a>
                        @endif
                        @error('file')<p class="error">{{ $message }}</p>@enderror
                    </div>

                    <div class="sm:col-span-2 lg:col-span-4">
                        <label for="assignment_description" class="label">تعليمات الواجب (اختياري)</label>
                        <textarea id="assignment_description" name="description" rows="3" class="input">{{ old('description', $lecture->assignment?->description) }}</textarea>
                        @error('description')<p class="error">{{ $message }}</p>@enderror
                    </div>

                    <div class="flex items-center gap-6 sm:col-span-2 lg:col-span-3">
                        <label class="flex cursor-pointer items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300">
                            <input type="hidden" name="is_published" value="0">
                            <input type="checkbox" name="is_published" value="1"
                                   class="size-4 rounded border-slate-300 text-brand-600 focus:ring-brand-500/40 dark:border-slate-700 dark:bg-slate-950"
                                   @checked(old('is_published', $lecture->assignment?->is_published ?? true))>
                            منشور للطلاب
                        </label>
                    </div>

                    <div class="self-end">
                        <button type="submit" class="btn-primary w-full">حفظ الواجب</button>
                    </div>
                </form>
            </div>
        </section>

        {{-- ============================ 5) امتحان المحاضرة --}}
        <section class="card-pad flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-extrabold text-slate-900 dark:text-white">امتحان المحاضرة</h2>
                @if ($lecture->exam)
                    <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">
                        {{ $lecture->exam->title }} — {{ $lecture->exam->questions->count() }} سؤال
                    </p>
                @else
                    <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">
                        لا يوجد امتحان مرتبط — أنشئ امتحاناً جديداً واربطه بهذه المحاضرة.
                    </p>
                @endif
            </div>
            <div class="flex gap-2">
                @if ($lecture->exam)
                    <a href="{{ route('admin.questions.index', $lecture->exam) }}" class="btn-secondary btn-sm">إدارة الأسئلة</a>
                    <a href="{{ route('admin.exams.edit', $lecture->exam) }}" class="btn-secondary btn-sm">إعدادات الامتحان</a>
                @else
                    <a href="{{ route('admin.exams.create') }}" class="btn-primary btn-sm">إنشاء امتحان</a>
                @endif
            </div>
        </section>
    </div>
@endsection
