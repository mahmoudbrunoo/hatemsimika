@php
    $whatsapp = preg_replace('/\D/', '', setting('support.whatsapp', '01003878666'));
    $whatsappLink = 'https://wa.me/2' . $whatsapp;
@endphp

{{-- زر واتساب المباشر للدعم الفني — أسفل اليسار كما في المرجع --}}
<a href="{{ $whatsappLink }}" target="_blank" rel="noopener" title="تواصل واتساب"
   class="fixed bottom-5 left-5 z-50 grid size-16 place-items-center rounded-full bg-[#25D366] text-white shadow-lg transition hover:scale-105">
    <svg class="size-8" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
</a>

{{-- الشات بوت التفاعلي — شجرة أسئلة بالأزرار + بحث بالكلمات — أسفل اليمين كما في المرجع --}}
<div x-data="chatbot({
        url: @js(route('chatbot')),
        welcome: @js(setting('chatbot.welcome', 'أهلاً بيك! اسألني عن الاشتراك أو الدفع أو المنصة.')),
        tree: @js(\App\Models\ChatbotOption::tree()),
     })"
     class="fixed bottom-5 right-5 z-50">

    <button type="button" @click="open = !open" title="المساعد الذكي"
            class="grid size-14 place-items-center rounded-full bg-night-900 text-white shadow-lg transition hover:scale-105 hover:bg-night-800 dark:bg-white dark:text-night-900 dark:hover:bg-slate-200">
        <svg x-show="!open" class="size-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
        <svg x-show="open" x-cloak class="size-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>

    <div x-show="open" x-cloak x-transition
         class="absolute bottom-[4.5rem] right-0 flex h-[28rem] w-[19rem] flex-col overflow-hidden rounded-2xl border border-slate-300 bg-surface shadow-2xl dark:border-night-700 dark:bg-night-900 sm:w-80">
        <div class="bg-brand-500 px-4 py-3 text-sm font-extrabold text-white">المساعد الذكي — بجاوبك فوراً</div>

        <div x-ref="box" class="flex-1 space-y-2.5 overflow-y-auto p-3">
            {{-- سجل المحادثة --}}
            <template x-for="(m, i) in messages" :key="i">
                <div :class="m.from === 'me' ? 'mr-auto bg-brand-500 text-white' : 'ml-auto bg-slate-100 text-slate-700 dark:bg-night-800 dark:text-slate-200'"
                     class="max-w-[85%] rounded-2xl px-3.5 py-2 text-sm font-medium leading-6">
                    <template x-if="m.html">
                        <div x-html="m.html"
                             class="whitespace-pre-line [&_a]:font-bold [&_a]:text-brand-600 [&_a]:underline dark:[&_a]:text-brand-400"></div>
                    </template>
                    <template x-if="!m.html">
                        <div x-text="m.text" class="whitespace-pre-line"></div>
                    </template>
                    <template x-if="m.link">
                        <a :href="m.link.url" target="_blank" rel="noopener"
                           class="mt-2 inline-flex items-center gap-1.5 rounded-xl bg-brand-500 px-3 py-1.5 text-xs font-bold text-white transition hover:bg-brand-600">
                            <span x-text="m.link.label || 'افتح الرابط'"></span>
                            <svg class="size-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>
                    </template>
                </div>
            </template>
            <div x-show="sending" class="ml-auto w-fit rounded-2xl bg-slate-100 px-3.5 py-2 text-sm dark:bg-night-800">...</div>

            {{-- أزرار الاختيارات الحالية في الشجرة --}}
            <div x-show="options.length || stack.length" class="pt-1">
                <template x-for="o in options" :key="o.id">
                    <button type="button" @click="choose(o)"
                            class="mb-1.5 flex w-full items-center justify-between gap-2 rounded-xl border border-slate-300 bg-surface px-3.5 py-2.5 text-right text-sm font-bold text-slate-700 transition hover:border-brand-500 hover:text-brand-600 dark:border-night-700 dark:bg-night-800 dark:text-slate-200 dark:hover:border-brand-500 dark:hover:text-brand-300">
                        <span x-text="o.label"></span>
                        <svg x-show="o.children.length" class="size-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                </template>

                {{-- الرجوع خطوة / القائمة الرئيسية --}}
                <div x-show="stack.length" class="mt-1 flex gap-2">
                    <button type="button" @click="back()"
                            class="flex flex-1 items-center justify-center gap-1.5 rounded-xl bg-slate-100 px-3 py-2 text-xs font-bold text-slate-600 transition hover:bg-slate-200 dark:bg-night-800 dark:text-slate-300 dark:hover:bg-night-700">
                        <svg class="size-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 5l7 7-7 7M20 12H4"/></svg>
                        رجوع
                    </button>
                    <button type="button" @click="home()"
                            class="flex flex-1 items-center justify-center gap-1.5 rounded-xl bg-slate-100 px-3 py-2 text-xs font-bold text-slate-600 transition hover:bg-slate-200 dark:bg-night-800 dark:text-slate-300 dark:hover:bg-night-700">
                        <svg class="size-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l9-9 9 9M5 10v10a1 1 0 001 1h4v-6h4v6h4a1 1 0 001-1V10"/></svg>
                        القائمة الرئيسية
                    </button>
                </div>
            </div>
        </div>

        <form @submit.prevent="send()" class="flex items-center gap-2 border-t border-slate-300/60 p-2.5 dark:border-night-800">
            <input type="text" x-model="draft" placeholder="أو اكتب سؤالك هنا..." class="input py-2">
            <button class="btn-primary btn-sm shrink-0" :disabled="sending">إرسال</button>
        </form>
    </div>
</div>
