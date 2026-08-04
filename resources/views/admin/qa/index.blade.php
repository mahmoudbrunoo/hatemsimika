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
                            @elseif ($thread->is_locked)
                                <span class="badge-green">تم الرد ومقفول</span>
                            @else
                                <span class="badge-sky">منشور — في انتظار الإجابة</span>
                            @endif
                            <p class="mt-1 text-xs font-semibold text-slate-400">{{ $thread->created_at->format('Y/m/d H:i') }}</p>
                        </div>
                    </div>

                    {{-- نص السؤال --}}
                    <div class="mt-4 rounded-xl bg-slate-50 p-4 dark:bg-slate-950/50">
                        <p class="whitespace-pre-line text-sm leading-7 text-slate-700 dark:text-slate-300">{{ $thread->body }}</p>
                        @if ($thread->image_path)
                            <a href="{{ $thread->image_path }}" target="_blank" rel="noopener noreferrer" class="mt-2 inline-block">
                                <img src="{{ $thread->image_path }}"
                                     alt="صورة مرفقة بالسؤال" class="max-h-48 rounded-xl border border-slate-300 object-cover dark:border-slate-700">
                            </a>
                        @endif
                    </div>

                    {{-- الردود --}}
                    @foreach ($thread->replies as $reply)
                        <div class="mt-3 rounded-xl p-4 {{ $reply->is_official_answer ? 'bg-emerald-50 dark:bg-emerald-500/10' : 'bg-slate-50 dark:bg-slate-950/50' }}">
                            <p class="mb-1 text-xs font-bold {{ $reply->is_official_answer ? 'text-emerald-700 dark:text-emerald-300' : 'text-slate-400' }}">
                                {{ $reply->user->name }} {{ $reply->is_official_answer ? '— الإجابة المعتمدة ✅' : '' }}
                            </p>
                            <p class="whitespace-pre-line text-sm leading-7 text-slate-700 dark:text-slate-300">{{ $reply->body }}</p>
                            @if ($reply->image_path)
                                <a href="{{ $reply->image_path }}" target="_blank" rel="noopener noreferrer" class="mt-2 inline-block">
                                    <img src="{{ $reply->image_path }}"
                                         alt="صورة مرفقة بالإجابة" class="max-h-48 rounded-xl border border-slate-300 object-cover dark:border-slate-700">
                                </a>
                            @endif
                        </div>
                    @endforeach

                    {{-- الموافقة أو الرفض — تتطلب صلاحية qa.moderate --}}
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
                        @endif
                    @endcan

                    {{-- الرد الرسمي — يتطلب صلاحية qa.answer --}}
                    @can('qa.answer')
                        @if (! $thread->is_locked && $thread->status !== \App\Models\QaThread::STATUS_REJECTED)
                            <form method="POST" action="{{ route('admin.qa.answer', $thread) }}" enctype="multipart/form-data" class="mt-4 space-y-2">
                                @csrf
                                <label for="body-{{ $thread->id }}" class="label">الإجابة الرسمية</label>
                                <textarea id="body-{{ $thread->id }}" name="body" rows="3" maxlength="5000" class="input"
                                          placeholder="اكتب الإجابة هنا..." required>{{ old('body') }}</textarea>
                                @error('body')<p class="error">{{ $message }}</p>@enderror

                                <label for="image-{{ $thread->id }}" class="label">صورة مرفقة (اختياري)</label>
                                <input id="image-{{ $thread->id }}" name="image" type="file" accept="image/jpeg,image/png,image/webp" class="input">
                                @error('image')<p class="error">{{ $message }}</p>@enderror

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
