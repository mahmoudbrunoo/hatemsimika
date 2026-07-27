{{-- تخطيط منطقة الطالب: قائمة جانبية بنمط بسطتهالك + محتوى الصفحة --}}
@extends('layouts.app')

@section('content')
    <div class="mx-auto grid max-w-7xl gap-8 px-4 py-8 sm:px-6 lg:grid-cols-[17rem_1fr]">

        {{-- القائمة الجانبية — أزرار متراصة كما في المرجع --}}
        <aside x-data="{ open: false }" class="lg:sticky lg:top-24 lg:self-start">
            {{-- زر فتح القائمة على الموبايل --}}
            <button type="button" @click="open = !open"
                    class="btn-secondary mb-3 w-full justify-between lg:hidden">
                <span>قائمة حسابي</span>
                <svg class="size-4" :class="open && 'rotate-180'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </button>

            <nav class="space-y-2.5" :class="open ? 'block' : 'hidden lg:block'">
                <a href="{{ route('student.dashboard') }}" class="dash-link {{ request()->routeIs('student.dashboard') ? 'dash-link-active' : '' }}">الرئيسية</a>
                <a href="{{ route('student.profile') }}" class="dash-link {{ request()->routeIs('student.profile') ? 'dash-link-active' : '' }}">ملف المستخدم</a>
                <a href="{{ route('student.charge') }}" class="dash-link {{ request()->routeIs('student.charge') ? 'dash-link-active' : '' }}">شحن كود سنتر</a>
                <a href="{{ route('student.link') }}" class="dash-link {{ request()->routeIs('student.link') ? 'dash-link-active' : '' }}">ربط ID السنتر</a>
                <a href="{{ route('student.wallet') }}" class="dash-link {{ request()->routeIs('student.wallet') ? 'dash-link-active' : '' }}">رصيدي</a>
                <a href="{{ route('student.courses') }}" class="dash-link {{ request()->routeIs('student.courses') || request()->routeIs('student.learn.*') ? 'dash-link-active' : '' }}">كورساتي</a>
                <a href="{{ route('student.security') }}" class="dash-link {{ request()->routeIs('student.security') ? 'dash-link-active' : '' }}">الآمان و تاريخ تسجيل الدخول</a>
                <a href="{{ route('student.watch') }}" class="dash-link {{ request()->routeIs('student.watch') ? 'dash-link-active' : '' }}">تفاصيل المشاهدات</a>
                <a href="{{ route('student.invoices') }}" class="dash-link {{ request()->routeIs('student.invoices') ? 'dash-link-active' : '' }}">الفواتير</a>
                <a href="{{ route('student.subscriptions') }}" class="dash-link {{ request()->routeIs('student.subscriptions') ? 'dash-link-active' : '' }}">الاشتراكات</a>
                <a href="{{ route('student.results.exams') }}" class="dash-link {{ request()->routeIs('student.results.exams') ? 'dash-link-active' : '' }}">نتائج الامتحانات</a>
                <a href="{{ route('student.results.evaluations') }}" class="dash-link {{ request()->routeIs('student.results.evaluations') ? 'dash-link-active' : '' }}">نتائج اختبارات التقييم</a>
                <a href="{{ route('student.results.homework') }}" class="dash-link {{ request()->routeIs('student.results.homework') ? 'dash-link-active' : '' }}">نتائج الواجب</a>
                <a href="{{ route('student.personal') }}" class="dash-link {{ request()->routeIs('student.personal') ? 'dash-link-active' : '' }}">امتحان خاص بيك</a>
                <a href="{{ route('student.bank') }}" class="dash-link {{ request()->routeIs('student.bank') ? 'dash-link-active' : '' }}">بنك الاسئلة</a>
            </nav>
        </aside>

        {{-- محتوى الصفحة --}}
        <div class="min-w-0">
            @hasSection('page-title')
                <div class="page-pill mb-10">
                    <span class="px-2">@yield('page-title')</span>
                    <span class="page-pill-icon">
                        <svg class="size-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </span>
                </div>
            @endif
            @yield('page')
        </div>
    </div>
@endsection
