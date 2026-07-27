@extends('layouts.student')

@section('title', 'تفاصيل المشاهدة — ' . setting('site.name', 'منصة حاتم سميكة'))
@section('page-title', 'تفاصيل المشاهدات')

@section('page')
    {{-- إجمالي ساعات المشاهدة --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div class="stat-card">
            <span class="stat-value">{{ $totalHours }}</span>
            <span class="stat-label">إجمالي ساعات المشاهدة على المنصة</span>
        </div>
    </div>

    <div class="mt-6">
        <h2 class="mb-3 text-lg font-extrabold text-slate-900 dark:text-white">سجل مشاهداتك</h2>

        @if ($views->isEmpty())
            <div class="card-pad text-center">
                <div class="mx-auto grid size-14 place-items-center rounded-2xl bg-brand-50 text-2xl dark:bg-brand-500/10">🎬</div>
                <h2 class="mt-4 text-lg font-extrabold text-slate-900 dark:text-white">لسه مشوفتش أي فيديو</h2>
                <p class="mt-2 text-sm font-semibold text-slate-500 dark:text-slate-400">
                    ابدأ أول فيديو في كورسك وهتلاقي تفاصيل مشاهداتك كلها هنا.
                </p>
                <a href="{{ route('student.courses') }}" class="btn-primary mt-5">روح لكورساتي</a>
            </div>
        @else
            <div class="table-box">
                <table class="table">
                    <thead>
                        <tr>
                            <th>الفيديو</th>
                            <th>المحاضرة / الكورس</th>
                            <th>مدة المشاهدة</th>
                            <th>الحالة</th>
                            <th>آخر مشاهدة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($views as $view)
                            <tr>
                                <td class="font-semibold text-slate-800 dark:text-slate-200">
                                    {{ $view->video?->title ?? 'فيديو محذوف' }}
                                </td>
                                <td>
                                    <span class="font-semibold">{{ $view->video?->lecture?->title ?? '—' }}</span>
                                    <span class="block text-xs text-slate-400">{{ $view->video?->lecture?->course?->title ?? '—' }}</span>
                                </td>
                                <td>
                                    @php $minutes = intdiv($view->seconds_watched, 60); @endphp
                                    <span class="font-semibold">{{ $minutes }} دقيقة</span>
                                    <span class="block text-xs text-slate-400">{{ $view->seconds_watched % 60 }} ثانية</span>
                                </td>
                                <td>
                                    @if ($view->completed)
                                        <span class="badge-green">مكتمل</span>
                                    @else
                                        <span class="badge-amber">لسه بيتشاف</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="font-semibold">{{ $view->updated_at->format('Y/m/d') }}</span>
                                    <span class="block text-xs text-slate-400">{{ $view->updated_at->diffForHumans() }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $views->links() }}</div>
        @endif
    </div>
@endsection
