<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', setting('site.name', config('app.name')))</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=cairo:400,600,700,800,900" rel="stylesheet">

    {{-- منع وميض الثيم الخاطئ: نقرأ التفضيل قبل تحميل CSS --}}
    <script>
        (function () {
            var theme = localStorage.getItem('theme') || '{{ auth()->user()->theme ?? 'light' }}';
            if (theme === 'dark') document.documentElement.classList.add('dark');
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body data-auth="{{ auth()->check() ? 1 : 0 }}" data-theme-url="{{ route('account.theme') }}" class="flex min-h-screen flex-col">

    @if (session('impersonated_by'))
        <div class="bg-amber-500 px-4 py-2 text-center text-sm font-bold text-amber-950">
            أنت الآن داخل حساب الطالب: {{ auth()->user()->name }} (وضع الدعم الفني)
            <form method="POST" action="{{ route('impersonate.stop') }}" class="inline">
                @csrf
                <button class="mr-3 underline hover:no-underline">الرجوع لحساب الأدمن</button>
            </form>
        </div>
    @endif

    @include('partials.navbar')

    <main class="flex-1">
        @include('partials.flash')
        @yield('content')
    </main>

    @include('partials.footer')
    @include('partials.floating')

    @stack('scripts')
</body>
</html>
