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

            {{-- تعديل بيانات الطالب --}}
            <div class="card-pad" x-data="{ editOpen: {{ $errors->editStudent->isNotEmpty() ? 'true' : 'false' }} }">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 class="text-lg font-extrabold text-slate-900 dark:text-white">تعديل بيانات الطالب</h2>
                    <button type="button" @click="editOpen = !editOpen" class="btn-secondary btn-sm">
                        <span x-text="editOpen ? 'إخفاء النموذج' : 'فتح نموذج التعديل'"></span>
                    </button>
                </div>

                <form x-show="editOpen" style="display: none;" x-transition method="POST"
                      action="{{ route('admin.users.update', $user) }}" class="mt-5 grid gap-4 sm:grid-cols-2">
                    @csrf
                    @method('PUT')

                    <div class="sm:col-span-2">
                        <label for="edit_name" class="label">الاسم رباعي</label>
                        <input id="edit_name" name="name" type="text" value="{{ old('name', $user->name) }}" class="input" required>
                        @error('name', 'editStudent')<p class="error">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="edit_email" class="label">البريد الإلكتروني</label>
                        <input id="edit_email" name="email" type="email" value="{{ old('email', $user->email) }}" class="input" dir="ltr" required>
                        @error('email', 'editStudent')<p class="error">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="edit_phone" class="label">موبايل الطالب</label>
                        <input id="edit_phone" name="phone" type="text" value="{{ old('phone', $user->phone) }}" class="input" dir="ltr" required>
                        @error('phone', 'editStudent')<p class="error">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="edit_father_phone" class="label">موبايل الأب</label>
                        <input id="edit_father_phone" name="father_phone" type="text" value="{{ old('father_phone', $user->father_phone) }}" class="input" dir="ltr">
                        @error('father_phone', 'editStudent')<p class="error">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="edit_mother_phone" class="label">موبايل الأم</label>
                        <input id="edit_mother_phone" name="mother_phone" type="text" value="{{ old('mother_phone', $user->mother_phone) }}" class="input" dir="ltr">
                        @error('mother_phone', 'editStudent')<p class="error">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="edit_national_id" class="label">الرقم القومي</label>
                        <input id="edit_national_id" name="national_id" type="text" value="{{ old('national_id', $user->national_id) }}" class="input" dir="ltr" maxlength="14">
                        @error('national_id', 'editStudent')<p class="error">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="edit_governorate" class="label">المحافظة</label>
                        <select id="edit_governorate" name="governorate" class="input">
                            <option value="">—</option>
                            @foreach (\App\Models\User::GOVERNORATES as $governorate)
                                <option value="{{ $governorate }}" @selected(old('governorate', $user->governorate) === $governorate)>{{ $governorate }}</option>
                            @endforeach
                        </select>
                        @error('governorate', 'editStudent')<p class="error">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="edit_school" class="label">المدرسة</label>
                        <input id="edit_school" name="school" type="text" value="{{ old('school', $user->school) }}" class="input">
                        @error('school', 'editStudent')<p class="error">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="edit_academic_year" class="label">الصف الدراسي</label>
                        <select id="edit_academic_year" name="academic_year" class="input">
                            <option value="">—</option>
                            @foreach (\App\Models\User::YEARS as $year => $label)
                                <option value="{{ $year }}" @selected((string) old('academic_year', $user->academic_year) === (string) $year)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('academic_year', 'editStudent')<p class="error">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="edit_gender" class="label">النوع</label>
                        <select id="edit_gender" name="gender" class="input">
                            <option value="">—</option>
                            <option value="male" @selected(old('gender', $user->gender) === 'male')>ذكر</option>
                            <option value="female" @selected(old('gender', $user->gender) === 'female')>أنثى</option>
                        </select>
                        @error('gender', 'editStudent')<p class="error">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="edit_status" class="label">حالة الحساب</label>
                        <select id="edit_status" name="status" class="input" required>
                            @foreach (\App\Models\User::STATUSES as $statusValue => $statusLabel)
                                <option value="{{ $statusValue }}" @selected(old('status', $user->status) === $statusValue)>{{ $statusLabel }}</option>
                            @endforeach
                        </select>
                        @error('status', 'editStudent')<p class="error">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="edit_center_id" class="label">ID طالب السنتر (اختياري)</label>
                        <input id="edit_center_id" name="center_id" type="text" value="{{ old('center_id', $user->center_id) }}" class="input" dir="ltr" maxlength="30">
                        @error('center_id', 'editStudent')<p class="error">{{ $message }}</p>@enderror
                    </div>

                    <div class="sm:col-span-2">
                        <button type="submit" class="btn-primary w-full sm:w-auto"
                                onclick="return confirm('هل أنت متأكد من حفظ التعديلات على بيانات الطالب؟')">حفظ التعديلات</button>
                    </div>
                </form>
            </div>

            {{-- صورة البطاقة --}}
            <div class="card-pad" x-data="{ idOpen: false }">
                <h2 class="mb-4 text-lg font-extrabold text-slate-900 dark:text-white">صورة البطاقة / الكارنيه</h2>
                @if ($idPhotoUrl)
                    {{-- رابط موقّع مؤقت من الباكت الخاص (صلاحيته 10 دقائق) — لا يوجد أي رابط عام دائم للملف --}}
                    <button type="button" @click="idOpen = true" class="block w-full cursor-zoom-in"
                            title="اضغط لعرض الصورة بالحجم الكامل">
                        <img src="{{ $idPhotoUrl }}" alt="صورة بطاقة {{ $user->name }}"
                             class="max-h-80 w-full rounded-xl border border-slate-300 object-contain dark:border-slate-800">
                    </button>
                    <button type="button" @click="idOpen = true" class="btn-secondary btn-sm mt-3">عرض الصورة بالحجم الكامل</button>
                    <p class="mt-2 text-xs font-semibold text-slate-400">
                        الرابط مؤقت وتنتهي صلاحيته بعد 10 دقائق — أعد تحميل الصفحة لتوليد رابط جديد.
                    </p>

                    {{-- معاينة داخل إطار لوحة التحكم بدون مغادرة الصفحة --}}
                    <div x-show="idOpen" style="display: none;" @keydown.escape.window="idOpen = false"
                         class="fixed inset-0 z-50 flex items-center justify-center p-4" role="dialog" aria-modal="true">
                        <div class="absolute inset-0 bg-slate-950/80" @click="idOpen = false"></div>
                        <div class="relative max-h-[90vh] max-w-4xl overflow-auto rounded-2xl bg-white p-3 shadow-2xl dark:bg-night-900">
                            <img src="{{ $idPhotoUrl }}" alt="صورة بطاقة {{ $user->name }}"
                                 class="mx-auto max-h-[80vh] rounded-lg object-contain">
                            <button type="button" @click="idOpen = false" class="btn-secondary btn-sm mt-3 w-full">إغلاق</button>
                        </div>
                    </div>
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

            {{-- تغيير كلمة المرور --}}
            <div class="card-pad">
                <h3 class="mb-3 font-extrabold text-slate-900 dark:text-white">تغيير كلمة المرور</h3>
                <p class="mb-4 text-sm font-semibold text-slate-500 dark:text-slate-400">
                    عيّن كلمة مرور جديدة للطالب مباشرة — بدون الحاجة لكلمة المرور القديمة.
                </p>
                <form method="POST" action="{{ route('admin.users.password', $user) }}">
                    @csrf
                    <div class="mb-3">
                        <label for="new_password" class="label">كلمة المرور الجديدة</label>
                        <input id="new_password" name="password" type="password" class="input" dir="ltr"
                               minlength="8" autocomplete="new-password" placeholder="8 أحرف على الأقل" required>
                        @error('password', 'resetPassword')<p class="error">{{ $message }}</p>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="new_password_confirmation" class="label">تأكيد كلمة المرور</label>
                        <input id="new_password_confirmation" name="password_confirmation" type="password" class="input" dir="ltr"
                               minlength="8" autocomplete="new-password" required>
                    </div>
                    <button type="submit" class="btn-primary w-full"
                            onclick="return confirm('سيتم تغيير كلمة مرور الطالب وإنهاء جلسته الحالية. متابعة؟')">
                        حفظ كلمة المرور الجديدة
                    </button>
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
