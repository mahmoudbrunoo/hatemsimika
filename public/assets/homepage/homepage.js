// ------------------------------------------------------------------
// صفحة الطالب الرئيسية (تصميم fullmark) — تفاعلات فانيلا بدون مكتبات
// الوضع الليلي، القوائم المنسدلة، القائمة الجانبية، شريط التمرير، الرسم البياني
// ------------------------------------------------------------------
(function () {
    'use strict';

    var body = document.body;
    var root = document.documentElement;

    var csrf = function () {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.content : '';
    };

    var postJson = function (url, payload) {
        return fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(payload),
        });
    };

    // -------------------------------------------------- الوضع الليلي/النهاري
    var isDark = function () {
        return root.classList.contains('dark');
    };

    // شكل المفتاح (.theme-pill) يتحرك بالكامل عبر CSS اعتماداً على كلاس dark —
    // هنا فقط نحدّث حالة إمكانية الوصول
    var paintSwitches = function (dark) {
        document.querySelectorAll('[data-theme-switch]').forEach(function (btn) {
            btn.setAttribute('aria-checked', dark ? 'true' : 'false');
        });
    };

    var applyTheme = function (dark, persist) {
        root.classList.toggle('dark', dark);
        root.classList.toggle('darkmode', dark);
        body.classList.toggle('dark', dark);
        body.classList.toggle('darkmode', dark);
        paintSwitches(dark);

        if (persist) {
            localStorage.setItem('theme', dark ? 'dark' : 'light');

            if (body.dataset.auth === '1' && body.dataset.themeUrl) {
                postJson(body.dataset.themeUrl, { theme: dark ? 'dark' : 'light' }).catch(function () {});
            }
        }
    };

    document.querySelectorAll('[data-theme-switch]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            applyTheme(!isDark(), true);
        });
    });

    // مزامنة شكل المفاتيح مع الحالة المحملة من السيرفر/التخزين المحلي
    applyTheme(isDark(), false);

    // -------------------------------------------------- القوائم المنسدلة
    var closeAllMenus = function (except) {
        document.querySelectorAll('[data-menu-panel]').forEach(function (panel) {
            if (panel !== except) panel.classList.add('hidden');
        });
        document.querySelectorAll('[data-menu-button]').forEach(function (btn) {
            var panel = btn.closest('[data-menu-root]');
            panel = panel && panel.querySelector('[data-menu-panel]');
            btn.setAttribute('aria-expanded', panel && !panel.classList.contains('hidden') ? 'true' : 'false');
        });
    };

    document.querySelectorAll('[data-menu-button]').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            var rootEl = btn.closest('[data-menu-root]');
            var panel = rootEl && rootEl.querySelector('[data-menu-panel]');
            if (!panel) return;

            var willOpen = panel.classList.contains('hidden');
            closeAllMenus(null);
            if (willOpen) panel.classList.remove('hidden');
            btn.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
        });
    });

    document.addEventListener('click', function (e) {
        if (!e.target.closest('[data-menu-root]')) closeAllMenus(null);
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeAllMenus(null);
    });

    // -------------------------------------------------- تصغير القائمة الجانبية
    document.querySelectorAll('[data-collapse-toggle]').forEach(function (el) {
        el.addEventListener('click', function () {
            body.classList.toggle('collapsed-side-nav');
            var collapsed = body.classList.contains('collapsed-side-nav');
            document.querySelectorAll('[data-collapse-arrow]').forEach(function (arrow) {
                arrow.classList.toggle('rotate-180', !collapsed);
            });
        });
    });

    // -------------------------------------------------- قائمة الموبايل الجانبية
    document.querySelectorAll('[data-sidenav-toggle]').forEach(function (el) {
        el.addEventListener('click', function () {
            body.classList.toggle('sidenav-mobile-open');
        });
    });

    // -------------------------------------------------- شريط تقدم التمرير في الهيدر
    var progressBar = document.querySelector('.progress-bar__moving');

    var updateScrollProgress = function () {
        if (!progressBar) return;
        var max = root.scrollHeight - window.innerHeight;
        var pct = max > 0 ? Math.min(100, (window.scrollY / max) * 100) : 0;
        progressBar.style.width = pct + '%';
    };

    window.addEventListener('scroll', updateScrollProgress, { passive: true });
    window.addEventListener('resize', updateScrollProgress, { passive: true });
    updateScrollProgress();

    // -------------------------------------------------- إغلاق رسائل الفلاش
    document.querySelectorAll('[data-dismiss]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var flash = btn.closest('[data-flash]');
            if (flash) flash.remove();
        });
    });

    // -------------------------------------------------- الرسم البياني الأسبوعي
    // نفس إعدادات وألوان resources/js/app.js — لوحة الألوان الموحّدة للمنصة
    var renderWeeklyChart = function () {
        if (typeof Chart === 'undefined') return;

        Chart.defaults.font.family = "'Cairo', 'Almarai', sans-serif";
        Chart.defaults.color = isDark() ? '#d2a89b' : '#7a4e52';

        var palette = { videos: '#800033', quizzes: '#9c6a82', hours: '#c08a45' };
        var gridColor = isDark() ? 'rgba(210,168,155,.15)' : 'rgba(74,21,27,.12)';

        document.querySelectorAll('canvas[data-chart="weekly"]').forEach(function (canvas) {
            var rows;

            try {
                rows = JSON.parse(canvas.dataset.series || '[]');
            } catch (e) {
                rows = [];
            }

            new Chart(canvas, {
                data: {
                    labels: rows.map(function (r) { return r.label; }),
                    datasets: [
                        {
                            type: 'bar',
                            label: 'فيديوهات',
                            data: rows.map(function (r) { return r.videos; }),
                            backgroundColor: palette.videos,
                            borderRadius: 6,
                        },
                        {
                            type: 'bar',
                            label: 'كويزات',
                            data: rows.map(function (r) { return r.quizzes; }),
                            backgroundColor: palette.quizzes,
                            borderRadius: 6,
                        },
                        {
                            type: 'line',
                            label: 'ساعات مذاكرة',
                            data: rows.map(function (r) { return r.hours; }),
                            borderColor: palette.hours,
                            backgroundColor: palette.hours,
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
                        y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: gridColor } },
                        hours: { beginAtZero: true, position: 'left', grid: { drawOnChartArea: false } },
                    },
                    plugins: { legend: { rtl: true, labels: { usePointStyle: true } } },
                },
            });
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', renderWeeklyChart);
    } else {
        renderWeeklyChart();
    }
})();
