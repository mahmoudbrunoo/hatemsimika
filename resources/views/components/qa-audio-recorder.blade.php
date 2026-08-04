{{--
    مسجل الرسائل الصوتية (زي فويس الواتساب): تسجيل مباشر من المتصفح + عداد مدة حي
    + معاينة قبل الإرسال — والتسجيل يتحول لملف داخل حقل الرفع العادي name="{{ $name }}"
    فيترفع مع النموذج. الحقل نفسه يفضل شغالاً كبديل لرفع ملف صوتي جاهز.
--}}
@props(['name' => 'audio', 'label' => '🎙️ رسالة صوتية (اختياري)', 'showError' => true])

<div x-data="qaRecorder" {{ $attributes }}>
    <span class="label">{{ $label }}</span>

    <div class="mt-1 rounded-xl border border-slate-300/60 p-3 dark:border-slate-800">
        <div class="flex flex-wrap items-center gap-2">
            {{-- بدء التسجيل --}}
            <button type="button" x-show="!recording && !audioUrl" @click="start" class="btn-secondary btn-sm">
                🎙️ سجّل رسالة صوتية
            </button>

            {{-- أثناء التسجيل: إيقاف + نقطة حمراء نابضة + عداد المدة --}}
            <template x-if="recording">
                <div class="flex items-center gap-3">
                    <button type="button" @click="stop" class="btn-danger btn-sm">⏹ إيقاف التسجيل</button>
                    <span class="flex items-center gap-2 text-sm font-extrabold text-rose-600 dark:text-rose-400">
                        <span class="relative flex size-2.5">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-rose-500 opacity-75"></span>
                            <span class="relative inline-flex size-2.5 rounded-full bg-rose-600"></span>
                        </span>
                        <span dir="ltr" x-text="display"></span>
                    </span>
                </div>
            </template>

            {{-- معاينة التسجيل أو الملف المختار قبل الإرسال --}}
            <template x-if="audioUrl && !recording">
                <div class="flex w-full flex-wrap items-center gap-2">
                    <audio controls preload="metadata" :src="audioUrl" dir="ltr" class="h-10 min-w-0 flex-1"></audio>
                    <span class="text-xs font-bold text-slate-500 dark:text-slate-400" x-text="fileLabel"></span>
                    <button type="button" @click="clear" class="btn-danger btn-sm">🗑 حذف</button>
                </div>
            </template>
        </div>

        <p x-show="error" x-text="error" class="error" x-cloak></p>

        {{-- الرفع اليدوي البديل — والتسجيل المكتمل يتوضع في نفس الحقل --}}
        <input type="file" name="{{ $name }}" x-ref="input" @change="filePicked" x-show="!recording && !audioUrl"
               accept="audio/*,.mp3,.m4a,.wav,.webm,.ogg"
               class="input mt-2 file:ml-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-1.5 file:text-xs file:font-bold file:text-brand-700 dark:file:bg-brand-500/10 dark:file:text-brand-300">
    </div>

    @if ($showError)
        @error($name) <p class="error">{{ $message }}</p> @enderror
    @endif
</div>
