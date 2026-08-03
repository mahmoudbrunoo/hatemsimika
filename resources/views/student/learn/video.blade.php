{{-- صفحة مشاهدة الفيديو: مشغل بعلامة مائية + تتبع مشاهدة — بدون مشتتات --}}
@extends('layouts.app')

@section('title', $video->title . ' — ' . $lecture->title)

@section('content')
    <div class="mx-auto max-w-5xl px-4 py-8 sm:px-6">

        {{-- رجوع للمحاضرة --}}
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('student.learn.lecture', [$course, $lecture]) }}" class="btn-secondary btn-sm">
                ← الرجوع للمحاضرة: {{ $lecture->title }}
            </a>
            @if ($view->completed)
                <span class="badge-green">✔ اتفرجت على الفيديو ده</span>
            @endif
        </div>

        {{-- المشغل --}}
        @php
            $embedSrc = match ($video->source) {
                'youtube' => $video->youtubeId()
                    ? 'https://www.youtube-nocookie.com/embed/' . $video->youtubeId() . '?rel=0&modestbranding=1'
                    : $video->url,
                'vimeo' => preg_match('/(\d{6,})/', (string) $video->url, $m)
                    ? 'https://player.vimeo.com/video/' . $m[1]
                    : $video->url,
                'bunny' => $video->url,
                default => null,
            };
        @endphp

        <div class="player-frame" x-data="videoTracker('{{ route('student.learn.progress', $video) }}', {{ (int) $view->last_position }})">
            @if ($video->source === 'file' && $video->file_path)
                <video controls playsinline preload="metadata" controlslist="nodownload"
                       oncontextmenu="return false"
                       src="{{ $video->file_path }}"></video>
            @elseif ($embedSrc)
                <iframe src="{{ $embedSrc }}"
                        title="{{ $video->title }}"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; fullscreen"
                        allowfullscreen></iframe>
            @else
                <div class="grid size-full place-items-center text-sm font-bold text-white/70">
                    الفيديو غير متاح حالياً — كلمنا لو المشكلة استمرت.
                </div>
            @endif

            {{-- العلامة المائية المتحركة: اسم الطالب + رقم موبايله --}}
            <span x-data="watermark" class="watermark" :style="`top:${top};right:${right};opacity:${opacity}`">{{ auth()->user()->name }} — {{ auth()->user()->phone }}</span>
        </div>

        {{-- بيانات الفيديو --}}
        <div class="card-pad mt-6">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h1 class="text-xl font-black text-slate-900 dark:text-white">{{ $video->title }}</h1>
                <span class="badge-gray">⏱️ المدة: <span dir="ltr">{{ $video->durationHuman() }}</span></span>
            </div>

            @if ($video->description)
                <div class="mt-4 border-t border-slate-300/60 pt-4 dark:border-slate-800">
                    <h2 class="text-sm font-extrabold text-slate-900 dark:text-white">عن الفيديو</h2>
                    <p class="mt-2 whitespace-pre-line text-sm leading-8 text-slate-600 dark:text-slate-300">{{ $video->description }}</p>
                </div>
            @endif

            @php
                $takeaways = collect(preg_split('/\r\n|\r|\n/', (string) $video->takeaways))
                    ->map(fn ($line) => trim($line, " \t-•"))
                    ->filter();
            @endphp

            @if ($takeaways->isNotEmpty())
                <div class="mt-4 border-t border-slate-300/60 pt-4 dark:border-slate-800">
                    <h2 class="text-sm font-extrabold text-slate-900 dark:text-white">🎯 أهم النقاط المستفادة</h2>
                    <ul class="mt-2 space-y-1.5 text-sm leading-7 text-slate-600 dark:text-slate-300">
                        @foreach ($takeaways as $point)
                            <li class="flex gap-2"><span class="text-emerald-500">✔</span> {{ $point }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        {{-- التنقل بين الفيديوهات --}}
        @php
            $orderedVideos = $lecture->videos->values();
            $currentIndex = $orderedVideos->search(fn ($v) => $v->id === $video->id);
            $prevVideo = $currentIndex !== false && $currentIndex > 0 ? $orderedVideos[$currentIndex - 1] : null;
            $nextVideo = $currentIndex !== false && $currentIndex < $orderedVideos->count() - 1 ? $orderedVideos[$currentIndex + 1] : null;
        @endphp

        @if ($prevVideo || $nextVideo)
            <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                @if ($prevVideo)
                    <a href="{{ route('student.learn.video', [$course, $prevVideo]) }}" class="btn-secondary justify-start">
                        → الفيديو السابق: <span class="max-w-48 truncate">{{ $prevVideo->title }}</span>
                    </a>
                @else
                    <span></span>
                @endif

                @if ($nextVideo)
                    <a href="{{ route('student.learn.video', [$course, $nextVideo]) }}" class="btn-primary justify-end">
                        الفيديو التالي: <span class="max-w-48 truncate">{{ $nextVideo->title }}</span> ←
                    </a>
                @endif
            </div>
        @endif

        {{-- عندك سؤال؟ --}}
        <div class="mt-6 text-center">
            <a href="{{ route('student.learn.lecture', [$course, $lecture]) }}#qa" class="text-sm font-bold text-brand-600 hover:underline dark:text-brand-300">
                💬 عندك سؤال في الدرس ده؟ اسأل من صفحة المحاضرة
            </a>
        </div>
    </div>
@endsection
