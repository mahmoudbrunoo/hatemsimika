@extends('layouts.admin')

@section('title', 'أسئلة الطلاب — لوحة التحكم')
@section('page-title', 'مراجعة أسئلة الطلاب')

@section('page')
    {{-- تبويبات الحالة --}}
    <div class="mb-6 flex flex-wrap gap-2">
        <a href="{{ route('admin.qa.index', ['status' => \App\Models\QaThread::STATUS_PENDING]) }}"
           class="{{ $status === \App\Models\QaThread::STATUS_PENDING ? 'btn-primary' : 'btn-secondary' }}">
            قيد المراجعة <span class="badge-amber">{{ $counts['pending'] }}</span>
        </a>
        <a href="{{ route('admin.qa.index', ['status' => \App\Models\QaThread::STATUS_APPROVED]) }}"
           class="{{ $status === \App\Models\QaThread::STATUS_APPROVED ? 'btn-primary' : 'btn-secondary' }}">
            منشور <span class="badge-sky">{{ $counts['approved'] }}</span>
        </a>
        <a href="{{ route('admin.qa.index', ['status' => \App\Models\QaThread::STATUS_REJECTED]) }}"
           class="{{ $status === \App\Models\QaThread::STATUS_REJECTED ? 'btn-primary' : 'btn-secondary' }}">
            مرفوض <span class="badge-red">{{ $counts['rejected'] }}</span>
        </a>
    </div>

    @if ($threads->isEmpty())
        <div class="card-pad text-center text-slate-500 dark:text-slate-400">
            @if ($status === \App\Models\QaThread::STATUS_PENDING)
                مفيش أسئلة مستنية المراجعة 🎉
            @else
                لا توجد أسئلة في هذه الحالة.
            @endif
        </div>
    @else
        <div class="space-y-4">
            @foreach ($threads as $thread)
                <div class="card-pad">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="font-extrabold text-slate-900 dark:text-white">{{ $thread->user->name }}</p>
                            <p class="mt-0.5 text-sm font-semibold text-slate-500 dark:text-slate-400">
                                {{ $thread->lecture->course->title ?? '' }} — {{ $thread->lecture->title ?? '' }}
                            </p>
                        </div>
                        <div class="text-left">
                            @if ($thread->status === \App\Models\QaThread::STATUS_PENDING)
                                <span class="badge-amber">قيد المراجعة</span>
                            @elseif ($thread->status === \App\Models\QaThread::STATUS_REJECTED)
                                <span class="badge-red">مرفوض</span>
                            @else
                                @if ($thread->answered_at)
                                    <span class="badge-green">تم الرد</span>
                                @endif
                                @if ($thread->is_locked)
                                    <span class="badge-gray">🔒 مقفول</span>
                                @elseif (! $thread->answered_at)
                                    <span class="badge-sky">منشور — في انتظار الإجابة</span>
                                @endif
                            @endif
                            <p class="mt-1 text-xs font-semibold text-slate-400">{{ $thread->created_at->format('Y/m/d H:i') }}</p>
                        </div>
                    </div>

                    {{-- نص السؤال --}}
                    <div class="mt-4 rounded-xl bg-slate-50 p-4 dark:bg-slate-950/50">
                        @if ($thread->body)
                            <p class="whitespace-pre-line text-sm leading-7 text-slate-700 dark:text-slate-300">{{ $thread->body }}</p>
                        @endif
                        @if ($thread->image_path)
                            <a href="{{ $thread->image_path }}" target="_blank" rel="noopener noreferrer" class="mt-2 inline-block">
                                <img src="{{ $thread->image_path }}"
                                     alt="صورة مرفقة بالسؤال" class="max-h-48 rounded-xl border border-slate-300 object-cover dark:border-slate-700">
                            </a>
                        @endif
                        @if ($thread->audio_path)
                            <div class="mt-2 flex items-center gap-2">
                                <span class="shrink-0 text-lg">🎙️</span>
                                <audio controls preload="metadata" src="{{ $thread->audio_path }}" dir="ltr" class="h-10 w-full min-w-0 max-w-md"></audio>
                            </div>
                        @endif
                    </div>

                    {{-- الردود --}}
                    @foreach ($thread->replies as $reply)
                        <div class="mt-3 rounded-xl p-4 {{ $reply->is_official_answer ? 'bg-emerald-50 dark:bg-emerald-500/10' : 'bg-slate-50 dark:bg-slate-950/50' }}">
                            <p class="mb-1 text-xs font-bold {{ $reply->is_official_answer ? 'text-emerald-700 dark:text-emerald-300' : 'text-slate-400' }}">
                                {{ $reply->user->name }} {{ $reply->is_official_answer ? '— الإجابة المعتمدة ✅' : '' }}
                            </p>
                            @if ($reply->body)
                                <p class="whitespace-pre-line text-sm leading-7 text-slate-700 dark:text-slate-300">{{ $reply->body }}</p>
                            @endif
                            @if ($reply->image_path)
                                <a href="{{ $reply->image_path }}" target="_blank" rel="noopener noreferrer" class="mt-2 inline-block">
                                    <img src="{{ $reply->image_path }}"
                                         alt="صورة مرفقة بالإجابة" class="max-h-48 rounded-xl border border-slate-300 object-cover dark:border-slate-700">
                                </a>
                            @endif
                            @if ($reply->audio_path)
                                <div class="mt-2 flex items-center gap-2">
                                    <span class="shrink-0 text-lg">🎙️</span>
                                    <audio controls preload="metadata" src="{{ $reply->audio_path }}" dir="ltr" class="h-10 w-full min-w-0 max-w-md"></audio>
                                </div>
                            @endif
                        </div>
                    @endforeach

                    {{-- الموافقة أو الرفض + قفل/فتح التعليقات — تتطلب صلاحية qa.moderate --}}
                    @can('qa.moderate')
                        @if ($thread->status === \App\Models\QaThread::STATUS_PENDING)
                            <div class="mt-4 flex flex-wrap gap-2">
                                <form method="POST" action="{{ route('admin.qa.approve', $thread) }}">
                                    @csrf
                                    <button type="submit" class="btn-success btn-sm">نشر السؤال</button>
                                </form>
                                <form method="POST" action="{{ route('admin.qa.reject', $thread) }}"
                                      onsubmit="return confirm('رفض السؤال؟ مش هيظهر لباقي الطلاب.')">
                                    @csrf
                                    <button type="submit" class="btn-danger btn-sm">رفض</button>
                                </form>
                            </div>
                        @elseif ($thread->status === \App\Models\QaThread::STATUS_APPROVED)
                            <form method="POST" action="{{ route('admin.qa.toggle-lock', $thread) }}" class="mt-4">
                                @csrf
                                <div class="flex flex-wrap items-center gap-3">
                                    <button type="submit" class="btn-secondary btn-sm">
                                        {{ $thread->is_locked ? '🔓 فتح الموضوع للتعليقات' : '🔒 قفل الموضوع' }}
                                    </button>
                                    <p class="text-xs font-semibold text-slate-400">
                                        {{ $thread->is_locked
                                            ? 'الموضوع مقفول — التعليقات متاحة فقط للمدرسين وصاحب السؤال.'
                                            : 'القفل يوقف تعليقات الطلاب ويترك الرد للمدرسين وصاحب السؤال.' }}
                                    </p>
                                </div>
                            </form>
                        @endif
                    @endcan

                    {{-- الرد الرسمي — يتطلب صلاحية qa.answer، ومتاح حتى مع القفل --}}
                    @can('qa.answer')
                        @if ($thread->status !== \App\Models\QaThread::STATUS_REJECTED)
                            @php $answerFailed = old('_answer_thread') == $thread->id; @endphp
                            <form method="POST" action="{{ route('admin.qa.answer', $thread) }}" enctype="multipart/form-data" class="mt-4 space-y-2">
                                @csrf
                                <input type="hidden" name="_answer_thread" value="{{ $thread->id }}">

                                <label for="body-{{ $thread->id }}" class="label">{{ $thread->answered_at ? 'رد إضافي' : 'الإجابة الرسمية' }}</label>
                                <textarea id="body-{{ $thread->id }}" name="body" rows="3" maxlength="5000" class="input"
                                          placeholder="اكتب الإجابة هنا — أو سجّل رسالة صوتية تحت...">{{ $answerFailed ? old('body') : '' }}</textarea>
                                @if ($answerFailed) @error('body')<p class="error">{{ $message }}</p>@enderror @endif

                                <label for="image-{{ $thread->id }}" class="label">صورة مرفقة (اختياري)</label>
                                <input id="image-{{ $thread->id }}" name="image" type="file" accept="image/jpeg,image/png,image/webp" class="input">
                                @if ($answerFailed) @error('image')<p class="error">{{ $message }}</p>@enderror @endif

                                <x-qa-audio-recorder name="audio" :show-error="false" />
                                @if ($answerFailed) @error('audio')<p class="error">{{ $message }}</p>@enderror @endif

                                <div class="flex flex-wrap items-center gap-3">
                                    <button type="submit" class="btn-primary btn-sm">إرسال الإجابة</button>
                                    <p class="text-xs font-semibold text-slate-400">بعد الإجابة الموضوع بيتقفل تلقائياً كمرجع معتمد.</p>
                                </div>
                            </form>
                        @endif
                    @endcan
                </div>
            @endforeach
        </div>

        <div class="mt-4">{{ $threads->links() }}</div>
    @endif
@endsection
