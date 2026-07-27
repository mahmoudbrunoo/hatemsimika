{{-- بطاقة كورس موحدة: تستخدم في الرئيسية وصفحة الكورسات ولوحة الطالب --}}
<a href="{{ route('courses.show', $course) }}" class="card group flex flex-col overflow-hidden transition hover:-translate-y-1 hover:shadow-lg">
    <div class="relative aspect-video bg-slate-100 dark:bg-slate-800">
        @if ($course->thumbnail_path)
            <img src="{{ Storage::url($course->thumbnail_path) }}" alt="{{ $course->title }}" class="size-full object-cover">
        @else
            <div class="grid size-full place-items-center bg-gradient-to-br from-brand-500 to-brand-800 text-4xl font-black text-white/90">
                {{ mb_substr($course->title, 0, 1) }}
            </div>
        @endif
        <span class="absolute right-3 top-3 badge-sky">{{ $course->yearLabel() }}</span>
        @if ($course->discountPercent())
            <span class="absolute left-3 top-3 badge-red">خصم {{ $course->discountPercent() }}%</span>
        @endif
    </div>

    <div class="flex flex-1 flex-col gap-3 p-4">
        <div>
            <span class="badge-gray">{{ $course->categoryLabel() }}</span>
            <h3 class="mt-2 font-extrabold text-slate-900 group-hover:text-brand-600 dark:text-white">{{ $course->title }}</h3>
            @if ($course->description)
                <p class="mt-1 line-clamp-2 text-sm leading-6 text-slate-500 dark:text-slate-400">{{ $course->description }}</p>
            @endif
        </div>

        <div class="mt-auto flex items-center gap-4 text-xs font-bold text-slate-500 dark:text-slate-400">
            <span class="flex items-center gap-1">
                <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                {{ $course->totalVideos() }} درس
            </span>
            <span class="flex items-center gap-1">
                <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ floor($course->totalDurationSeconds() / 3600) }} ساعة
            </span>
        </div>

        <div class="flex items-center justify-between border-t border-slate-100 pt-3 dark:border-slate-800">
            <div>
                @if ($course->discount_price !== null)
                    <span class="text-lg font-black text-brand-600">{{ egp($course->discount_price) }}</span>
                    <span class="mr-1 text-sm font-bold text-slate-400 line-through">{{ egp($course->price) }}</span>
                @elseif ((float) $course->price > 0)
                    <span class="text-lg font-black text-brand-600">{{ egp($course->price) }}</span>
                @else
                    <span class="text-lg font-black text-emerald-600">مجاني</span>
                @endif
            </div>
            <span class="btn-primary btn-sm">التفاصيل</span>
        </div>
    </div>
</a>
