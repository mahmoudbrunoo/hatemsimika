@extends('layouts.student')

@section('title', 'المحفظة — ' . setting('site.name', 'منصة حاتم سميكة'))
@section('page-title', 'رصيدي')

@section('page')
    {{-- الرصيد والشحن — كما في المرجع --}}
    <div class="mx-auto max-w-3xl">
        <div class="flex flex-wrap items-end justify-center gap-6">
            <a href="{{ route('student.charge') }}"
               class="inline-flex items-center justify-center rounded-xl bg-brand-500 px-8 py-5 text-center text-base font-black leading-7 text-white shadow-md transition hover:-translate-y-0.5 hover:bg-brand-600">
                شحن<br>المحفظة !<br>(فوري)
            </a>

            <div class="relative min-w-64 flex-1">
                <p class="flex items-center justify-end gap-2 text-sm font-extrabold text-slate-600 dark:text-slate-300">
                    <span>رصيدك الحالي</span>
                    <svg class="size-5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 003 12m18 0v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6m18 0V9M3 12V9"/></svg>
                </p>
                <p class="mt-2 border-b-2 border-slate-300 pb-3 text-right text-3xl font-black text-slate-900 dark:border-night-700 dark:text-white">
                    {{ number_format((float) $user->balance, 0) }} <span class="text-base font-bold text-slate-500 dark:text-slate-400">جنيه</span>
                </p>
            </div>
        </div>

        <div class="mt-10 flex flex-wrap items-center justify-center gap-4">
            <span class="text-2xl font-black text-slate-900 dark:text-white">أو :</span>
            <a href="{{ route('student.charge') }}"
               class="rounded-xl bg-brand-500 px-6 py-3 text-sm font-black text-white shadow-md transition hover:-translate-y-0.5 hover:bg-brand-600">
                شحن كود سنتر
            </a>
        </div>
        <p class="mt-4 text-center text-sm font-bold text-slate-600 dark:text-slate-300">
            دي بتدوس عليها لو انت اشتريت كود من السنتر و هتشحنه
        </p>
    </div>

    {{-- سجل الحركات --}}
    <div class="mt-12">
        <a href="{{ route('student.wallet.export') }}" class="btn-excel mb-6">تحميل ملف اكسيل</a>
        <h2 class="mb-3 text-lg font-extrabold text-slate-900 dark:text-white">سجل حركات المحفظة</h2>

        @if ($transactions->isEmpty())
            <div class="card-pad text-center text-slate-500 dark:text-slate-400">
                لسه مفيش أي حركات على محفظتك — أول ما تشحن أو تشتري هتلاقي كل حاجة متسجلة هنا.
            </div>
        @else
            <div class="table-box">
                <table class="table">
                    <thead>
                        <tr>
                            <th>التاريخ</th>
                            <th>نوع العملية</th>
                            <th>ملحوظة</th>
                            <th>المبلغ</th>
                            <th>الرصيد بعد العملية</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($transactions as $t)
                            @php
                                $typeLabel = match ($t->type) {
                                    'charge' => 'شحن رصيد',
                                    'purchase' => 'شراء',
                                    'refund' => 'استرداد',
                                    'admin_adjust' => 'تعديل إداري',
                                    'center_code' => 'كود سنتر',
                                    default => $t->type,
                                };
                            @endphp
                            <tr>
                                <td>
                                    <span class="font-semibold">{{ $t->created_at->format('Y/m/d') }}</span>
                                    <span class="block text-xs text-slate-400">{{ $t->created_at->diffForHumans() }}</span>
                                </td>
                                <td><span class="badge-gray">{{ $typeLabel }}</span></td>
                                <td class="text-slate-500 dark:text-slate-400">{{ $t->note ?: '—' }}</td>
                                <td>
                                    @if ((float) $t->amount >= 0)
                                        <span class="font-bold text-emerald-600 dark:text-emerald-400" dir="ltr">+{{ number_format((float) $t->amount, 2) }}</span>
                                    @else
                                        <span class="font-bold text-rose-600 dark:text-rose-400" dir="ltr">{{ number_format((float) $t->amount, 2) }}</span>
                                    @endif
                                    <span class="text-xs text-slate-400">جنيه</span>
                                </td>
                                <td class="font-semibold">{{ egp($t->balance_after) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $transactions->links() }}</div>
        @endif
    </div>
@endsection
