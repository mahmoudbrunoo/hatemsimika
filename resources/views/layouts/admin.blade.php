{{-- تخطيط لوحة التحكم: قائمة جانبية للإدارة + محتوى الصفحة --}}
@extends('layouts.app')

@section('content')
    <div class="mx-auto grid max-w-7xl gap-6 px-4 py-8 sm:px-6 lg:grid-cols-[16rem_1fr]">

        {{-- القائمة الجانبية --}}
        <aside x-data="{ open: false }" class="lg:sticky lg:top-24 lg:self-start">
            <button type="button" @click="open = !open"
                    class="btn-secondary mb-3 w-full justify-between lg:hidden">
                <span>قائمة لوحة التحكم</span>
                <svg class="size-4" :class="open && 'rotate-180'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </button>

            {{-- كل رابط يظهر فقط لمن يملك صلاحيته — والسوبر أدمن يرى الكل تلقائياً --}}
            <nav class="card space-y-0.5 p-3" :class="open ? 'block' : 'hidden lg:block'">
                @can('dashboard.view')
                    <a href="{{ route('admin.dashboard') }}" class="side-link {{ request()->routeIs('admin.dashboard') ? 'side-link-active' : '' }}">لوحة التحكم</a>
                @endcan
                @can('users.view')
                    <a href="{{ route('admin.users.index') }}" class="side-link {{ request()->routeIs('admin.users.*') ? 'side-link-active' : '' }}">الطلاب والموافقات</a>
                @endcan

                @canany(['courses.manage', 'exams.manage', 'grading.manage', 'qa.view', 'books.manage'])
                    <p class="px-3.5 pb-1 pt-4 text-xs font-bold text-slate-400">المحتوى</p>
                @endcanany
                @can('courses.manage')
                    <a href="{{ route('admin.courses.index') }}" class="side-link {{ request()->routeIs('admin.courses.*', 'admin.lectures.*') ? 'side-link-active' : '' }}">الكورسات والمحاضرات</a>
                @endcan
                @can('exams.manage')
                    <a href="{{ route('admin.exams.index') }}" class="side-link {{ request()->routeIs('admin.exams.*', 'admin.questions.*') ? 'side-link-active' : '' }}">الامتحانات والأسئلة</a>
                @endcan
                @can('grading.manage')
                    <a href="{{ route('admin.grading.index') }}" class="side-link {{ request()->routeIs('admin.grading.*') ? 'side-link-active' : '' }}">التصحيح</a>
                @endcan
                @can('qa.view')
                    <a href="{{ route('admin.qa.index') }}" class="side-link {{ request()->routeIs('admin.qa.*') ? 'side-link-active' : '' }}">أسئلة الطلاب</a>
                @endcan
                @can('books.manage')
                    <a href="{{ route('admin.books.index') }}" class="side-link {{ request()->routeIs('admin.books.*') ? 'side-link-active' : '' }}">الكتب</a>
                @endcan

                @canany(['orders.manage', 'coupons.manage', 'codes.manage'])
                    <p class="px-3.5 pb-1 pt-4 text-xs font-bold text-slate-400">المبيعات</p>
                @endcanany
                @can('orders.manage')
                    <a href="{{ route('admin.orders.index') }}" class="side-link {{ request()->routeIs('admin.orders.*') ? 'side-link-active' : '' }}">الطلبات والإيصالات</a>
                @endcan
                @can('coupons.manage')
                    <a href="{{ route('admin.coupons.index') }}" class="side-link {{ request()->routeIs('admin.coupons.*') ? 'side-link-active' : '' }}">الكوبونات</a>
                @endcan
                @can('codes.manage')
                    <a href="{{ route('admin.codes.index') }}" class="side-link {{ request()->routeIs('admin.codes.*') ? 'side-link-active' : '' }}">أكواد السنتر</a>
                @endcan

                @canany(['settings.manage', 'faqs.manage', 'chatbot.manage', 'audit.view'])
                    <p class="px-3.5 pb-1 pt-4 text-xs font-bold text-slate-400">النظام</p>
                @endcanany
                @can('settings.manage')
                    <a href="{{ route('admin.settings.index') }}" class="side-link {{ request()->routeIs('admin.settings.*') ? 'side-link-active' : '' }}">محتوى الموقع</a>
                @endcan
                @can('faqs.manage')
                    <a href="{{ route('admin.faqs.index') }}" class="side-link {{ request()->routeIs('admin.faqs.*') ? 'side-link-active' : '' }}">الأسئلة الشائعة</a>
                @endcan
                @can('chatbot.manage')
                    <a href="{{ route('admin.chatbot.index') }}" class="side-link {{ request()->routeIs('admin.chatbot.*') ? 'side-link-active' : '' }}">الشات بوت التفاعلي</a>
                @endcan
                @can('audit.view')
                    <a href="{{ route('admin.audit.index') }}" class="side-link {{ request()->routeIs('admin.audit.*') ? 'side-link-active' : '' }}">سجل التدقيق</a>
                @endcan
            </nav>
        </aside>

        {{-- محتوى الصفحة --}}
        <div class="min-w-0">
            <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                @hasSection('page-title')
                    <h1 class="text-2xl font-black text-slate-900 dark:text-white">@yield('page-title')</h1>
                @endif
                @yield('page-actions')
            </div>
            @yield('page')
        </div>
    </div>
@endsection
