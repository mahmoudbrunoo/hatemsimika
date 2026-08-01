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
    videos: '#1b5df5',
    quizzes: '#10b981',
    hours: '#f59e0b',
    line: '#1b5df5',
};

function gridColor() {
    return document.documentElement.classList.contains('dark') ? 'rgba(148,163,184,.15)' : 'rgba(100,116,139,.15)';
}

function renderCharts() {
    Chart.defaults.font.family = "'Cairo', sans-serif";

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
