@extends('layouts.admin')

@section('title', $user->name . ' — ملف الطالب')
@section('page-title', 'ملف الطالب')

@section('page-actions')
    <a href="{{ route('admin.users.index') }}" class="btn-secondary btn-sm">رجوع لقائمة الطلاب</a>
@endsection

@section('page')
    <div class="grid gap-6 lg:grid-cols-3">

        {{-- العمود الرئيسي: بيانات التسجيل --}}
        <div class="space-y-6 lg:col-span-2">
            <div class="card-pad">
                <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                    <h2 class="text-lg font-extrabold text-slate-900 dark:text-white">بيانات التسجيل</h2>
                    @switch($user->status)
                        @case(\App\Models\User::STATUS_PENDING)
                            <span class="badge-amber">قيد المراجعة</span>
                            @break
                        @case(\App\Models\User::STATUS_APPROVED)
                            <span class="badge-green">مفعل</span>
                            @break
                        @case(\App\Models\User::STATUS_REJECTED)
                            <span class="badge-red">مرفوض</span>
                            @break
                        @case(\App\Models\User::STATUS_BANNED)
                            <span class="badge-gray">محظور</span>
                            @break
                        @default
                            <span class="badge-gray">{{ $user->status }}</span>
                    @endswitch
                </div>

                @if ($user->status === \App\Models\User::STATUS_REJECTED && $user->rejection_reason)
                    <p class="mb-5 rounded-xl bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700 dark:bg-rose-500/10 dark:text-rose-300">
                        سبب الرفض: {{ $user->rejection_reason }}
                    </p>
                @endif

                <dl class="grid gap-x-6 gap-y-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-bold text-slate-400">الاسم رباعي</dt>
                        <dd class="mt-1 font-bold text-slate-900 dark:text-white">{{ $user->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold text-slate-400">البريد الإلكتروني</dt>
                        <dd class="mt-1 font-semibold" dir="ltr">{{ $user->email }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold text-slate-400">موبايل الطالب</dt>
                        <dd class="mt-1 font-semibold" dir="ltr">{{ $user->phone }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold text-slate-400">موبايل الأب</dt>
                        <dd class="mt-1 font-semibold" dir="ltr">{{ $user->father_phone ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold text-slate-400">موبايل الأم</dt>
                        <dd class="mt-1 font-semibold" dir="ltr">{{ $user->mother_phone ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold text-slate-400">الرقم القومي</dt>
                        <dd class="mt-1 font-semibold" dir="ltr">{{ $user->national_id ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold text-slate-400">المحافظة</dt>
                        <dd class="mt-1 font-semibold">{{ $user->governorate ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold text-slate-400">المدرسة</dt>
                        <dd class="mt-1 font-semibold">{{ $user->school ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold text-slate-400">الصف الدراسي</dt>
                        <dd class="mt-1 font-semibold">{{ $user->yearLabel() }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold text-slate-400">النوع</dt>
                        <dd class="mt-1 font-semibold">{{ $user->gender === 'male' ? 'ذكر' : ($user->gender === 'female' ? 'أنثى' : '—') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold text-slate-400">تاريخ التسجيل</dt>
                        <dd class="mt-1 font-semibold">{{ $user->created_at->format('Y/m/d — h:i A') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold text-slate-400">رصيد المحفظة</dt>
                        <dd class="mt-1 font-extrabold text-brand-600 dark:text-brand-300">{{ egp($user->balance) }}</dd>
                    </div>
                </dl>
            </div>

            {{-- صورة البطاقة --}}
            <div class="card-pad">
                <h2 class="mb-4 text-lg font-extrabold text-slate-900 dark:text-white">صورة البطاقة / الكارنيه</h2>
                @if ($user->id_photo_path)
                    {{-- الصورة تُعرض عبر مسار محمي للإدارة فقط — الملف في تخزين خاص بلا رابط عام --}}
                    <a href="{{ route('admin.users.idphoto', $user) }}" target="_blank" rel="noopener"
                       title="اضغط لفتح الصورة بالحجم الكامل">
                        <img src="{{ route('admin.users.idphoto', $user) }}" alt="صورة بطاقة {{ $user->name }}"
                             class="max-h-80 w-full rounded-xl border border-slate-200 object-contain dark:border-slate-800">
                    </a>
                    <a href="{{ route('admin.users.idphoto', $user) }}" target="_blank" rel="noopener"
                       class="btn-secondary btn-sm mt-3">فتح الصورة بالحجم الكامل</a>
                @else
                    <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">لم يتم رفع صورة بطاقة.</p>
                @endif
            </div>

            {{-- الاشتراكات --}}
            <div class="card-pad">
                <h2 class="mb-4 text-lg font-extrabold text-slate-900 dark:text-white">الكورسات المشترك فيها</h2>
                @if ($user->enrollments->isEmpty())
                    <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">الطالب غير مشترك في أي كورس حتى الآن.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>الكورس</th>
                                    <th>الحالة</th>
                                    <th>تاريخ الانتهاء</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($user->enrollments as $enrollment)
                                    <tr>
                                        <td class="font-bold text-slate-900 dark:text-white">{{ $enrollment->course?->title ?? '—' }}</td>
                                        <td>
                                            @if ($enrollment->isActive())
                                                <span class="badge-green">ساري</span>
                                            @else
                                                <span class="badge-gray">منتهي</span>
                                            @endif
                                        </td>
                                        <td>{{ $enrollment->expires_at?->format('Y/m/d') ?? 'دائم' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- آخر الطلبات --}}
            <div class="card-pad">
                <h2 class="mb-4 text-lg font-extrabold text-slate-900 dark:text-white">آخر الطلبات</h2>
                @if ($user->orders->isEmpty())
                    <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">لا توجد طلبات لهذا الطالب.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>رقم الطلب</th>
                                    <th>الإجمالي</th>
                                    <th>طريقة الدفع</th>
                                    <th>الحالة</th>
                                    <th>التاريخ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($user->orders as $order)
                                    <tr>
                                        <td dir="ltr">{{ $order->number }}</td>
                                        <td>{{ egp($order->total) }}</td>
                                        <td>{{ $order->paymentMethodLabel() }}</td>
                                        <td>
                                            @if ($order->status === \App\Models\Order::STATUS_PAID)
                                                <span class="badge-green">{{ $order->statusLabel() }}</span>
                                            @elseif ($order->status === \App\Models\Order::STATUS_PENDING)
                                                <span class="badge-amber">{{ $order->statusLabel() }}</span>
                                            @else
                                                <span class="badge-gray">{{ $order->statusLabel() }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $order->created_at->format('Y/m/d') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        {{-- عمود الإجراءات --}}
        <div class="space-y-6">

            {{-- الموافقة --}}
            @if ($user->status !== \App\Models\User::STATUS_APPROVED)
                <div class="card-pad">
                    <h3 class="mb-3 font-extrabold text-slate-900 dark:text-white">تفعيل الحساب</h3>
                    <p class="mb-4 text-sm font-semibold text-slate-500 dark:text-slate-400">راجع بيانات الطالب وصورة البطاقة قبل التفعيل.</p>
                    <form method="POST" action="{{ route('admin.users.approve', $user) }}">
                        @csrf
                        <button type="submit" class="btn-success w-full">الموافقة وتفعيل الحساب</button>
                    </form>
                </div>
            @endif

            {{-- الرفض --}}
            @if ($user->status !== \App\Models\User::STATUS_REJECTED)
                <div class="card-pad">
                    <h3 class="mb-3 font-extrabold text-slate-900 dark:text-white">رفض الحساب</h3>
                    <form method="POST" action="{{ route('admin.users.reject', $user) }}">
                        @csrf
                        <div class="mb-3">
                            <label for="reason" class="label">سبب الرفض</label>
                            <textarea id="reason" name="reason" rows="3" class="input" maxlength="500"
                                      placeholder="مثال: صورة البطاقة غير واضحة" required>{{ old('reason') }}</textarea>
                            @error('reason')<p class="error">{{ $message }}</p>@enderror
                        </div>
                        <button type="submit" class="btn-danger w-full"
                                onclick="return confirm('هل أنت متأكد من رفض هذا الحساب؟')">رفض الحساب</button>
                    </form>
                </div>
            @endif

            {{-- تعديل الرصيد --}}
            <div class="card-pad">
                <h3 class="mb-3 font-extrabold text-slate-900 dark:text-white">تعديل رصيد المحفظة</h3>
                <form method="POST" action="{{ route('admin.users.wallet', $user) }}">
                    @csrf
                    <div class="mb-3">
                        <label for="amount" class="label">المبلغ (بالجنيه)</label>
                        <input id="amount" name="amount" type="number" step="0.01" value="{{ old('amount') }}"
                               class="input" dir="ltr" placeholder="موجب للإضافة — سالب للخصم" required>
                        @error('amount')<p class="error">{{ $message }}</p>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="note" class="label">ملحوظة (اختياري)</label>
                        <input id="note" name="note" type="text" value="{{ old('note') }}" class="input" maxlength="255"
                               placeholder="سبب التعديل">
                        @error('note')<p class="error">{{ $message }}</p>@enderror
                    </div>
                    <button type="submit" class="btn-secondary w-full">تعديل الرصيد</button>
                </form>
            </div>

            {{-- فتح كورس يدوياً --}}
            <div class="card-pad">
                <h3 class="mb-3 font-extrabold text-slate-900 dark:text-white">فتح كورس للطالب</h3>
                <form method="POST" action="{{ route('admin.users.enroll', $user) }}">
                    @csrf
                    <div class="mb-3">
                        <label for="course_id" class="label">الكورس</label>
                        <select id="course_id" name="course_id" class="input" required>
                            <option value="" disabled selected>اختر الكورس</option>
                            @foreach ($courses as $course)
                                <option value="{{ $course->id }}" @selected((int) old('course_id') === $course->id)>
                                    {{ $course->yearLabel() }} — {{ $course->title }}
                                </option>
                            @endforeach
                        </select>
                        @error('course_id')<p class="error">{{ $message }}</p>@enderror
                    </div>
                    <button type="submit" class="btn-primary w-full">فتح الكورس</button>
                </form>
            </div>

            @unless ($user->isStaff())
                {{-- الدخول كحساب الطالب --}}
                @if (auth()->user()->hasRole('super_admin'))
                    <div class="card-pad">
                        <h3 class="mb-3 font-extrabold text-slate-900 dark:text-white">الدعم الفني</h3>
                        <form method="POST" action="{{ route('admin.users.impersonate', $user) }}">
                            @csrf
                            <button type="submit" class="btn-secondary w-full"
                                    onclick="return confirm('سيتم الدخول على حساب الطالب — وسيتم تسجيل العملية في سجل التدقيق. متابعة؟')">
                                دخول كحساب الطالب
                            </button>
                        </form>
                    </div>
                @endif

                {{-- الحظر --}}
                @if ($user->status !== \App\Models\User::STATUS_BANNED)
                    <div class="card-pad">
                        <h3 class="mb-3 font-extrabold text-slate-900 dark:text-white">حظر الحساب</h3>
                        <p class="mb-4 text-sm font-semibold text-slate-500 dark:text-slate-400">سيتم إنهاء جميع جلسات الطالب فوراً.</p>
                        <form method="POST" action="{{ route('admin.users.ban', $user) }}">
                            @csrf
                            <button type="submit"
                                    class="btn-secondary w-full border-rose-300 text-rose-600 hover:bg-rose-50 dark:border-rose-500/40 dark:text-rose-400 dark:hover:bg-rose-500/10"
                                    onclick="return confirm('هل أنت متأكد من حظر هذا الحساب وإنهاء جلساته؟')">
                                حظر الحساب
                            </button>
                        </form>
                    </div>
                @endif
            @endunless
        </div>
    </div>
@endsection
