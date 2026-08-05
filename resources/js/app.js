import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';

// ------------------------------------------------------------------ أدوات عامة
const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

const postJson = (url, body) =>
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf(),
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify(body),
    });

// ------------------------------------------------------------------ الوضع الليلي/النهاري
// التفضيل محفوظ في localStorage ومتزامن مع قاعدة البيانات للمستخدم المسجل.
Alpine.store('theme', {
    dark: document.documentElement.classList.contains('dark'),

    toggle() {
        this.dark = !this.dark;
        const theme = this.dark ? 'dark' : 'light';
        document.documentElement.classList.toggle('dark', this.dark);
        localStorage.setItem('theme', theme);

        if (document.body.dataset.auth === '1') {
            postJson(document.body.dataset.themeUrl, { theme }).catch(() => {});
        }
    },
});

// ------------------------------------------------------------------ شريط تقدم التمرير أسفل الهيدر
const updateScrollProgress = () => {
    const max = document.documentElement.scrollHeight - window.innerHeight;
    const pct = max > 0 ? Math.min(100, (window.scrollY / max) * 100) : 0;
    document.documentElement.style.setProperty('--scroll-progress', pct + '%');
};

window.addEventListener('scroll', updateScrollProgress, { passive: true });
window.addEventListener('resize', updateScrollProgress, { passive: true });
document.addEventListener('DOMContentLoaded', updateScrollProgress);

// ------------------------------------------------------------------ العلامة المائية الديناميكية
// اسم الطالب + رقم موبايله يطفو فوق الفيديو في مواضع عشوائية بشفافية متغيرة.
Alpine.data('watermark', () => ({
    top: '10%',
    right: '10%',
    opacity: 0.5,
    timer: null,

    init() {
        this.move();
        this.timer = setInterval(() => this.move(), 4000 + Math.random() * 3000);
    },

    destroy() {
        clearInterval(this.timer);
    },

    move() {
        this.top = 5 + Math.random() * 80 + '%';
        this.right = 5 + Math.random() * 70 + '%';
        this.opacity = (0.25 + Math.random() * 0.45).toFixed(2);
    },
}));

// ------------------------------------------------------------------ تتبع مشاهدة الفيديو
// نبضة كل 15 ثانية أثناء ظهور الصفحة => إحصائيات المشاهدة والنشاط الأسبوعي.
Alpine.data('videoTracker', (url, startPosition = 0) => ({
    position: startPosition,
    delta: 0,
    completed: false,
    timer: null,

    init() {
        this.timer = setInterval(() => this.tick(), 1000);
    },

    destroy() {
        clearInterval(this.timer);
    },

    tick() {
        if (document.hidden) return;

        const video = this.$root.querySelector('video');
        if (video && video.paused) return;
        if (video) this.position = Math.floor(video.currentTime);
        else this.position += 1;

        this.delta += 1;

        if (this.delta >= 15) this.flush();
    },

    flush() {
        if (this.delta === 0) return;
        const payload = { position: this.position, delta: Math.min(this.delta, 60) };
        this.delta = 0;

        postJson(url, payload)
            .then((r) => (r.ok ? r.json() : null))
            .then((data) => {
                if (data?.completed) this.completed = true;
            })
            .catch(() => {});
    },
}));

// ------------------------------------------------------------------ مؤقت الامتحان
// عد تنازلي — عند انتهاء الوقت يسلم النموذج تلقائياً.
Alpine.data('examTimer', (seconds, formId) => ({
    remaining: seconds,
    timer: null,

    init() {
        this.timer = setInterval(() => {
            this.remaining -= 1;

            if (this.remaining <= 0) {
                clearInterval(this.timer);
                document.getElementById(formId)?.submit();
            }
        }, 1000);
    },

    destroy() {
        clearInterval(this.timer);
    },

    get display() {
        const h = Math.floor(this.remaining / 3600);
        const m = Math.floor((this.remaining % 3600) / 60);
        const s = this.remaining % 60;
        const pad = (n) => String(n).padStart(2, '0');

        return h > 0 ? `${pad(h)}:${pad(m)}:${pad(s)}` : `${pad(m)}:${pad(s)}`;
    },

    get danger() {
        return this.remaining <= 60;
    },
}));

// ------------------------------------------------------------------ محاكي الامتحان (واجهة المرجع)
// سؤال واحد في كل شاشة + لوحة تنقل ملونة بحالة كل سؤال + مؤقت تنازلي:
// أزرق = محلول، وردي = اتفتح من غير حل، رمادي = لسه متفتحش، إطار أزرق = الحالي.
//
// الوقت سيرفري بحت: المؤقت محسوب من "موعد نهاية" ثابت وبيتزامن مع السيرفر مع كل
// حفظ تلقائي — فقفل الصفحة أو فصل النت مبيوقفش العداد أبداً.
// أي اختيار بيتحفظ فوراً على السيرفر (fetch مؤجل قليلاً)، وأي خروج مفاجئ
// (قفل تاب/تنقل/كراش) بيبعت آخر الاختيارات بـ sendBeacon — فالخروج المفاجئ
// بيتعامل تماماً كأن الطالب داس "استكمال الاختبار لاحقًا".
// زر "عرض الإجابات" بيقلب الحاوية لوضع مراجعة بكل الأسئلة والإجابات المختارة.
Alpine.data('examSimulator', ({ seconds, formId, storageKey, exitUrl, draftUrl, questionIds }) => ({
    remaining: seconds,
    deadline: Date.now() + seconds * 1000,
    current: 0,
    opened: [0],
    answeredIds: [],
    selections: {}, // question_id => نص الإجابة المختارة (لوضع المراجعة)
    review: false,
    modal: null, // null | 'finish' | 'later'
    timer: null,
    saveTimer: null,
    lastSaved: '',
    submitting: false,
    leaving: false,

    init() {
        this.restoreDraft();
        this.$nextTick(() => {
            this.refreshAnswered();
            this.queueServerSave(); // مزامنة أولية: أي مسودة محلية بتتحفظ على السيرفر فوراً
        });

        const form = document.getElementById(formId);
        form?.addEventListener('change', () => this.sync());
        form?.addEventListener('input', () => this.sync());

        // مؤقت مبني على موعد نهاية ثابت — مبيبطأش مع خنق المتصفح للتابات الخلفية
        this.timer = setInterval(() => this.tick(), 500);

        // الخروج المفاجئ بكل أشكاله = حفظ فوري على السيرفر بـ sendBeacon
        this.beaconHandler = () => this.beacon();
        this.visibilityHandler = () => {
            if (document.hidden) this.beacon();
            else this.serverSave(true); // الرجوع للتاب: مزامنة فورية للوقت والإجابات
        };
        window.addEventListener('pagehide', this.beaconHandler);
        window.addEventListener('beforeunload', this.beaconHandler);
        document.addEventListener('visibilitychange', this.visibilityHandler);
    },

    destroy() {
        clearInterval(this.timer);
        clearTimeout(this.saveTimer);
        window.removeEventListener('pagehide', this.beaconHandler);
        window.removeEventListener('beforeunload', this.beaconHandler);
        document.removeEventListener('visibilitychange', this.visibilityHandler);
    },

    tick() {
        this.remaining = Math.max(0, Math.round((this.deadline - Date.now()) / 1000));

        if (this.remaining <= 0 && !this.submitting) {
            clearInterval(this.timer);
            this.submitForm(); // الوقت خلص جوه الصفحة — تسليم تلقائي
        }
    },

    // مزامنة المؤقت مع الوقت المتبقي الحقيقي من السيرفر
    resync(secondsLeft) {
        this.deadline = Date.now() + secondsLeft * 1000;
    },

    get count() {
        return questionIds.length;
    },

    get answeredCount() {
        return this.answeredIds.length;
    },

    get openedCount() {
        return this.opened.length;
    },

    // غير المحلولة = اللي اتفتحت وسابها من غير إجابة (نفس منطق المرجع)
    get unsolvedCount() {
        return this.opened.filter((i) => !this.answeredIds.includes(questionIds[i])).length;
    },

    // المؤقت بصيغة المرجع: إجمالي الدقائق (ممكن تعدي 60) : الثواني
    get minutesDisplay() {
        return String(Math.max(0, Math.floor(this.remaining / 60)));
    },

    get secondsDisplay() {
        return String(Math.max(0, this.remaining % 60)).padStart(2, '0');
    },

    isOpened(i) {
        return this.opened.includes(i);
    },

    isAnswered(i) {
        return this.answeredIds.includes(questionIds[i]);
    },

    paletteClass(i) {
        if (this.isAnswered(i)) return 'bg-blue-500';
        if (this.isOpened(i)) return 'bg-rose-500';
        return 'bg-slate-400';
    },

    go(i) {
        if (i < 0 || i >= this.count) return;
        this.current = i;
        if (!this.opened.includes(i)) this.opened.push(i);
        this.saveDraft();
        this.$root.scrollIntoView({ behavior: 'smooth', block: 'start' });
    },

    prev() {
        this.go(this.current - 1);
    },

    next() {
        this.go(this.current + 1);
    },

    sync() {
        this.refreshAnswered();
        this.saveDraft();
        this.queueServerSave();
    },

    // بيبني خريطة الإجابات المختارة (للوحة التنقل + شاشة المراجعة) من حالة النموذج الفعلية
    refreshAnswered() {
        const form = document.getElementById(formId);
        if (!form) return;

        const selections = {};

        questionIds.forEach((qid) => {
            const checked = form.querySelector(`input[name="answers[${qid}]"]:checked`);
            if (checked) {
                selections[qid] = checked.dataset.label || 'اختيار محدد';
                return;
            }

            const essay = form.querySelector(`[name="essays[${qid}]"]`);
            if (essay && essay.value.trim() !== '') {
                const text = essay.value.trim();
                selections[qid] = text.length > 60 ? text.slice(0, 60) + '…' : text;
                return;
            }

            const file = form.querySelector(`input[name="essay_images[${qid}]"]`);
            if (file && file.files.length) selections[qid] = 'تم إرفاق صورة الحل';
        });

        this.selections = selections;
        this.answeredIds = questionIds.filter((qid) => selections[qid] !== undefined);
    },

    hasSelection(qid) {
        return this.selections[qid] !== undefined;
    },

    selectionLabel(qid) {
        return this.selections[qid] ?? 'لم يتم الإجابة';
    },

    // الاختيارات الحالية بصيغة الحفظ على السيرفر
    payload() {
        const form = document.getElementById(formId);
        const answers = {};
        const essays = {};

        questionIds.forEach((qid) => {
            const checked = form?.querySelector(`input[name="answers[${qid}]"]:checked`);
            if (checked) answers[qid] = checked.value;

            const essay = form?.querySelector(`[name="essays[${qid}]"]`);
            if (essay) essays[qid] = essay.value.trim();
        });

        return { answers, essays };
    },

    // حفظ مؤجل قليلاً — بيلم التعديلات المتتالية في طلب واحد
    queueServerSave() {
        clearTimeout(this.saveTimer);
        this.saveTimer = setTimeout(() => this.serverSave(), 1200);
    },

    // الحفظ الفوري للمسودة على السيرفر + مزامنة الوقت المتبقي الحقيقي
    async serverSave(force = false) {
        if (this.submitting) return null;

        const payload = this.payload();
        const serialized = JSON.stringify(payload);

        if (!force && serialized === this.lastSaved) return 'ok'; // مفيش جديد يتحفظ

        try {
            const response = await postJson(draftUrl, payload);
            if (!response.ok) return null;

            const data = await response.json();

            // الوقت خلص (أو المحاولة اتسلمت من مكان تاني) — النموذج بيتسلم فوراً
            if (data.status === 'expired' || data.status === 'submitted') {
                this.submitForm();
                return data.status;
            }

            this.lastSaved = serialized;
            if (typeof data.remaining_seconds === 'number') this.resync(data.remaining_seconds);

            return 'ok';
        } catch {
            return null; // النت فاصل — sendBeacon عند الخروج هو خط الدفاع التاني
        }
    },

    // آخر قشة عند أي خروج مفاجئ: sendBeacon بيكمل الإرسال حتى بعد قفل الصفحة
    beacon() {
        if (this.submitting) return;

        const payload = { ...this.payload(), _token: csrf() };
        const blob = new Blob([JSON.stringify(payload)], { type: 'application/json' });
        navigator.sendBeacon?.(draftUrl, blob);
    },

    saveDraft() {
        if (this.submitting) return;

        const form = document.getElementById(formId);
        if (!form) return;

        const answers = {};
        const essays = {};

        questionIds.forEach((qid) => {
            const checked = form.querySelector(`input[name="answers[${qid}]"]:checked`);
            if (checked) answers[qid] = checked.value;

            const essay = form.querySelector(`[name="essays[${qid}]"]`);
            if (essay && essay.value.trim() !== '') essays[qid] = essay.value;
        });

        try {
            localStorage.setItem(
                storageKey,
                JSON.stringify({ answers, essays, current: this.current, opened: this.opened }),
            );
        } catch {
            // التخزين ممتلئ أو متعطل — الامتحان يكمل عادي بدون حفظ مؤقت
        }
    },

    restoreDraft() {
        let draft = null;

        try {
            draft = JSON.parse(localStorage.getItem(storageKey) ?? 'null');
        } catch {
            draft = null;
        }

        if (!draft) return;

        const form = document.getElementById(formId);

        // المحفوظ على السيرفر (المرسوم في الصفحة) هو الأصل — المحلي بيكمل النواقص بس
        Object.entries(draft.answers ?? {}).forEach(([qid, value]) => {
            if (form?.querySelector(`input[name="answers[${qid}]"]:checked`)) return;

            const input = form?.querySelector(`input[name="answers[${qid}]"][value="${value}"]`);
            if (input) input.checked = true;
        });

        Object.entries(draft.essays ?? {}).forEach(([qid, text]) => {
            const essay = form?.querySelector(`[name="essays[${qid}]"]`);
            if (essay && essay.value.trim() === '') essay.value = text;
        });

        if (Array.isArray(draft.opened)) {
            this.opened = [...new Set([0, ...draft.opened.filter((i) => i >= 0 && i < this.count)])];
        }

        if (Number.isInteger(draft.current) && draft.current >= 0 && draft.current < this.count) {
            this.current = draft.current;
        }
    },

    submitForm() {
        if (this.submitting) return;
        this.submitting = true;
        this.modal = null;
        clearTimeout(this.saveTimer);
        localStorage.removeItem(storageKey);
        document.getElementById(formId)?.submit();
    },

    // إنهاء الاختبار — نافذة تأكيد الأول منعاً للتسليم بالخطأ
    finish() {
        this.modal = 'finish';
    },

    confirmFinish() {
        this.submitForm();
    },

    // استكمال لاحقاً — نافذة تحذير إن العداد مش هيقف
    later() {
        this.modal = 'later';
    },

    async confirmLater() {
        if (this.leaving) return;
        this.leaving = true;

        this.saveDraft(); // نسخة محلية سريعة
        const result = await this.serverSave(true); // حفظ مؤكد على السيرفر قبل الخروج

        // الوقت كان خلص أثناء الحفظ — النموذج اتسلم بالفعل ومفيش داعي للتحويل
        if (result === 'expired' || result === 'submitted') return;

        window.location.href = exitUrl;
    },

    // التبديل بين واجهة الحل ووضع مراجعة الإجابات المختارة
    toggleReview() {
        this.review = !this.review;
        if (this.review) this.refreshAnswered();
        this.$root.scrollIntoView({ behavior: 'smooth', block: 'start' });
    },

    share() {
        const data = { title: document.title, url: window.location.href };

        if (navigator.share) {
            navigator.share(data).catch(() => {});
            return;
        }

        navigator.clipboard
            ?.writeText(data.url)
            .then(() => alert('اتنسخ لينك الصفحة.'))
            .catch(() => {});
    },
}));

// ------------------------------------------------------------------ مسجل الرسائل الصوتية
// تسجيل مباشر من المتصفح (MediaRecorder) لأسئلة الدروس وردودها — زي فويس الواتساب:
// تسجيل/إيقاف + عداد مدة حي + معاينة قبل الإرسال، والتسجيل يتحول لملف داخل
// حقل الرفع العادي نفسه (name="audio") فيترفع مع النموذج بلا أي كود إضافي.
Alpine.data('qaRecorder', () => ({
    supported: !!(navigator.mediaDevices?.getUserMedia && window.MediaRecorder),
    recording: false,
    seconds: 0,
    timer: null,
    recorder: null,
    chunks: [],
    audioUrl: null,
    fileLabel: '',
    error: '',

    init() {
        // منع إرسال النموذج أثناء التسجيل — لازم يقف الأول عشان الملف يتولّد
        this.$root.closest('form')?.addEventListener('submit', (e) => {
            if (this.recording) {
                e.preventDefault();
                this.error = 'أوقف التسجيل الأول قبل الإرسال.';
            }
        });
    },

    // أفضل صيغة يدعمها المتصفح: كروم/فايرفوكس webm|ogg — سفاري mp4 (m4a)
    pickMime() {
        return ['audio/webm;codecs=opus', 'audio/webm', 'audio/mp4', 'audio/ogg;codecs=opus']
            .find((t) => MediaRecorder.isTypeSupported(t)) ?? '';
    },

    async start() {
        this.error = '';

        if (!this.supported) {
            this.error = 'المتصفح لا يدعم التسجيل المباشر — ارفع ملف صوتي من جهازك بدلاً منه.';
            return;
        }

        try {
            const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            const mime = this.pickMime();

            this.recorder = new MediaRecorder(stream, mime ? { mimeType: mime } : undefined);
            this.chunks = [];
            this.recorder.ondataavailable = (e) => e.data.size && this.chunks.push(e.data);
            this.recorder.onstop = () => {
                stream.getTracks().forEach((t) => t.stop());
                this.attachRecording();
            };

            this.recorder.start();
            this.recording = true;
            this.seconds = 0;
            this.timer = setInterval(() => this.seconds++, 1000);
        } catch {
            this.error = 'تعذر الوصول للمايك — اسمح بالإذن من المتصفح أو ارفع ملف صوتي.';
        }
    },

    stop() {
        if (!this.recording) return;
        clearInterval(this.timer);
        this.recording = false;
        this.recorder?.stop();
    },

    // التسجيل المكتمل يوضع كملف داخل input الرفع نفسه ليُرسل مع النموذج
    attachRecording() {
        const type = this.recorder?.mimeType || 'audio/webm';
        const ext = type.includes('mp4') ? 'm4a' : type.includes('ogg') ? 'ogg' : 'webm';
        const file = new File([new Blob(this.chunks, { type })], `voice-note.${ext}`, { type });

        const dt = new DataTransfer();
        dt.items.add(file);
        this.$refs.input.files = dt.files;

        this.preview(file, `🎙️ تسجيل صوتي (${this.display})`);
    },

    // اختيار ملف صوتي يدوياً من الجهاز (البديل الاحتياطي)
    filePicked() {
        const file = this.$refs.input.files?.[0];
        file ? this.preview(file, `📎 ${file.name}`) : this.clear();
    },

    preview(file, label) {
        if (this.audioUrl) URL.revokeObjectURL(this.audioUrl);
        this.audioUrl = URL.createObjectURL(file);
        this.fileLabel = label;
    },

    clear() {
        if (this.audioUrl) URL.revokeObjectURL(this.audioUrl);
        this.audioUrl = null;
        this.fileLabel = '';
        this.error = '';
        if (this.$refs.input) this.$refs.input.value = '';
    },

    destroy() {
        clearInterval(this.timer);
        if (this.recording) this.recorder?.stop();
        if (this.audioUrl) URL.revokeObjectURL(this.audioUrl);
    },

    get display() {
        const pad = (n) => String(n).padStart(2, '0');
        return `${pad(Math.floor(this.seconds / 60))}:${pad(this.seconds % 60)}`;
    },
}));

// ------------------------------------------------------------------ الشات بوت التفاعلي
// شجرة أسئلة بالأزرار (بلا حدود للتداخل) + بحث بالكلمات المفتاحية للرسائل المكتوبة.
Alpine.data('chatbot', ({ url, welcome, tree }) => ({
    open: false,
    sending: false,
    draft: '',
    messages: [{ from: 'bot', text: welcome }],
    stack: [], // مستويات سابقة للرجوع خطوة خطوة
    options: tree, // الأزرار المعروضة حالياً

    // اختيار زر: نعرض الرد/الرابط ثم ننزل للفروع إن وجدت
    choose(option) {
        this.messages.push({ from: 'me', text: option.label });

        const link = option.link_url ? { url: option.link_url, label: option.link_label } : null;

        if (option.response) {
            this.messages.push({ from: 'bot', html: option.response, link });
        } else if (link) {
            this.messages.push({ from: 'bot', text: link.label || option.label, link });
        }

        if (option.children.length) {
            this.stack.push(this.options);
            this.options = option.children;
            if (!option.response && !link) {
                this.messages.push({ from: 'bot', text: 'اختار من الأسئلة دي 👇' });
            }
        } else if (!option.response && !link) {
            this.messages.push({ from: 'bot', text: 'معنديش تفاصيل إضافية هنا — جرب خيار تاني أو اكتب سؤالك تحت.' });
        }

        this.scroll();
    },

    back() {
        if (this.stack.length === 0) return;
        this.options = this.stack.pop();
        this.scroll();
    },

    home() {
        this.stack = [];
        this.options = tree;
        this.scroll();
    },

    async send() {
        const text = this.draft.trim();
        if (text === '' || this.sending) return;

        this.messages.push({ from: 'me', text });
        this.draft = '';
        this.sending = true;

        try {
            const response = await postJson(url, { message: text });
            const data = await response.json();
            this.messages.push({ from: 'bot', text: data.answer });
        } catch {
            this.messages.push({ from: 'bot', text: 'حصل خطأ في الاتصال — جرب تاني.' });
        }

        this.sending = false;
        this.scroll();
    },

    scroll() {
        this.$nextTick(() => {
            const box = this.$refs.box;
            if (box) box.scrollTop = box.scrollHeight;
        });
    },
}));

// ------------------------------------------------------------------ الرسوم البيانية
// أي عنصر canvas عليه data-chart يترسم تلقائياً حسب نوعه.
const chartPalette = {
    videos: '#800033',
    quizzes: '#9c6a82',
    hours: '#c08a45',
    line: '#800033',
};

function gridColor() {
    return document.documentElement.classList.contains('dark') ? 'rgba(210,168,155,.15)' : 'rgba(74,21,27,.12)';
}

// لون نصوص المحاور ووسائل الإيضاح — من نظام الألوان الموحّد
function chartTextColor() {
    return document.documentElement.classList.contains('dark') ? '#d2a89b' : '#7a4e52';
}

function renderCharts() {
    Chart.defaults.font.family = "'Cairo', sans-serif";
    Chart.defaults.color = chartTextColor();

    // نشاط المذاكرة الأسبوعي: فيديوهات + كويزات أعمدة، والساعات خط
    document.querySelectorAll('canvas[data-chart="weekly"]').forEach((canvas) => {
        const rows = JSON.parse(canvas.dataset.series ?? '[]');

        new Chart(canvas, {
            data: {
                labels: rows.map((r) => r.label),
                datasets: [
                    {
                        type: 'bar',
                        label: 'فيديوهات',
                        data: rows.map((r) => r.videos),
                        backgroundColor: chartPalette.videos,
                        borderRadius: 6,
                    },
                    {
                        type: 'bar',
                        label: 'كويزات',
                        data: rows.map((r) => r.quizzes),
                        backgroundColor: chartPalette.quizzes,
                        borderRadius: 6,
                    },
                    {
                        type: 'line',
                        label: 'ساعات مذاكرة',
                        data: rows.map((r) => r.hours),
                        borderColor: chartPalette.hours,
                        backgroundColor: chartPalette.hours,
                        tension: 0.35,
                        yAxisID: 'hours',
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: { grid: { display: false } },
                    y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: gridColor() } },
                    hours: { beginAtZero: true, position: 'left', grid: { drawOnChartArea: false } },
                },
                plugins: { legend: { rtl: true, labels: { usePointStyle: true } } },
            },
        });
    });

    // سلاسل خطية عامة (إيرادات/تسجيلات لوحة التحكم)
    document.querySelectorAll('canvas[data-chart="line"]').forEach((canvas) => {
        const rows = JSON.parse(canvas.dataset.series ?? '[]');

        new Chart(canvas, {
            type: 'line',
            data: {
                labels: rows.map((r) => r.label),
                datasets: [
                    {
                        label: canvas.dataset.label ?? '',
                        data: rows.map((r) => r.value),
                        borderColor: canvas.dataset.color ?? chartPalette.line,
                        backgroundColor: (canvas.dataset.color ?? chartPalette.line) + '22',
                        fill: true,
                        tension: 0.35,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: { grid: { display: false } },
                    y: { beginAtZero: true, grid: { color: gridColor() } },
                },
                plugins: { legend: { display: false } },
            },
        });
    });
}

document.addEventListener('DOMContentLoaded', renderCharts);

window.Alpine = Alpine;
Alpine.start();
