@extends('layouts.student')

@section('title', 'الملف الشخصي — ' . setting('site.name', 'منصة حاتم سميكة'))
@section('page-title', 'ملف المستخدم')

@section('page')
    {{-- البيانات المسجلة --}}
    <div class="card-pad">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-4">
                @if ($user->avatar_path)
                    <img src="{{ Storage::url($user->avatar_path) }}" alt="{{ $user->name }}" class="size-16 rounded-2xl object-cover">
                @else
                    <div class="grid size-16 place-items-center rounded-2xl bg-brand-50 text-2xl font-black text-brand-600 dark:bg-brand-500/10 dark:text-brand-300">
                        {{ mb_substr($user->name, 0, 1) }}
                    </div>
                @endif
                <div>
                    <h2 class="text-lg font-extrabold text-slate-900 dark:text-white">{{ $user->name }}</h2>
                    <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">{{ $user->yearLabel() }}</p>
                </div>
            </div>

            @php
                $statusBadge = match ($user->status) {
                    \App\Models\User::STATUS_APPROVED => 'badge-green',
                    \App\Models\User::STATUS_PENDING => 'badge-amber',
                    default => 'badge-red',
                };
            @endphp
            <span class="{{ $statusBadge }}">{{ $user->statusLabel() }}</span>
        </div>

        <dl class="mt-6 grid gap-x-6 gap-y-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ([
                ['البريد الإلكتروني', $user->email, 'ltr'],
                ['رقم الموبايل', $user->phone, 'ltr'],
                ['رقم موبايل الأب', $user->father_phone, 'ltr'],
                ['رقم موبايل الأم', $user->mother_phone, 'ltr'],
                ['المحافظة', $user->governorate, null],
                ['المدرسة', $user->school, null],
                ['الصف الدراسي', $user->yearLabel(), null],
                ['النوع', $user->gender === 'male' ? 'ذكر' : ($user->gender === 'female' ? 'أنثى' : null), null],
                ['الرقم القومي', $user->national_id, 'ltr'],
            ] as [$dt, $dd, $dir])
                <div>
                    <dt class="text-xs font-bold text-slate-400 dark:text-slate-500">{{ $dt }}</dt>
                    <dd class="mt-0.5 text-right text-sm font-semibold text-slate-800 dark:text-slate-200" @if ($dir) dir="{{ $dir }}" @endif>
                        {{ $dd ?: '—' }}
                    </dd>
                </div>
            @endforeach
        </dl>

        @if ($user->id_photo_path)
            <div class="mt-6 border-t border-slate-100 pt-5 dark:border-slate-800">
                <p class="label">صورة البطاقة / الكارنيه المسجلة</p>
                <img src="{{ Storage::url($user->id_photo_path) }}" alt="صورة البطاقة" class="max-h-56 rounded-xl border border-slate-200 object-contain dark:border-slate-800">
            </div>
        @endif
    </div>

    {{-- تنبيه الاستعلام عن الرصيد — كما في المرجع --}}
    <div class="mx-auto mt-8 w-fit rounded-2xl bg-brand-500 p-3.5 shadow-md">
        <a href="{{ route('student.wallet') }}"
           class="block rounded-xl bg-[#f4536b] px-6 py-3 text-sm font-black text-white transition hover:brightness-110">
            لو شحنت كود , يمكنك استعلام عن الرصيد هنا
        </a>
    </div>

    {{-- احصائيات كورساتك --}}
    <div class="mt-12">
        <div class="mx-auto h-0.5 w-2/3 rounded-full bg-teal-600/70"></div>

        <h2 class="mt-10 flex items-center justify-center gap-4 text-center text-3xl font-black">
            <svg class="size-9 text-cyan-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 21.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"/></svg>
            <span><span class="text-[#f4536b]">احصائيات</span> <span class="text-slate-900 dark:text-white">كورساتك</span></span>
            <svg class="size-9 text-cyan-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 21.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z"/></svg>
        </h2>

        <div class="mt-10 grid gap-10 sm:grid-cols-3">
            {{-- عدد الفيديوهات --}}
            <div class="flex flex-col items-center gap-5">
                <div class="donut" style="--donut-value: {{ $stats['videos_percent'] }}; --donut-color: #f4536b;">
                    <div class="donut-inner">
                        <div>
                            <p class="text-2xl font-black text-slate-900 dark:text-white">{{ $stats['videos_percent'] }} %</p>
                            <p class="mt-1 text-sm font-extrabold text-slate-500 dark:text-slate-300">
                                {{ $stats['videos_percent'] >= 85 ? 'اشطر واحد ❤' : ($stats['videos_percent'] >= 40 ? 'فاضل حبة!' : 'يلا ابدأ!') }}
                            </p>
                        </div>
                    </div>
                </div>
                <p class="text-lg font-black text-slate-900 dark:text-white">عدد الفيديوهات شوفتها</p>
                <p class="flex items-center overflow-hidden rounded-full bg-white text-sm font-black shadow dark:bg-white">
                    <span class="rounded-full bg-[#f4536b] px-4 py-2 text-white">{{ $stats['videos_watched'] }} فيديو</span>
                    <span class="px-4 py-2 text-night-900">من {{ $stats['videos_total'] }}</span>
                </p>
            </div>

            {{-- عدد الاختبارات --}}
            <div class="flex flex-col items-center gap-5">
                <div class="donut" style="--donut-value: {{ $stats['exams_percent'] }}; --donut-color: #22d3ee;">
                    <div class="donut-inner">
                        <div>
                            <p class="text-2xl font-black text-slate-900 dark:text-white">{{ $stats['exams_percent'] }} %</p>
                            <p class="mt-1 text-sm font-extrabold text-slate-500 dark:text-slate-300">
                                {{ $stats['exams_percent'] >= 85 ? 'اشطر واحد ❤' : ($stats['exams_percent'] >= 40 ? 'فاضل حبة!' : 'يلا ابدأ!') }}
                            </p>
                        </div>
                    </div>
                </div>
                <p class="text-lg font-black text-slate-900 dark:text-white">عدد الاختبارات اللي خلصتها</p>
                <p class="flex items-center overflow-hidden rounded-full bg-white text-sm font-black shadow dark:bg-white">
                    <span class="rounded-full bg-cyan-500 px-4 py-2 text-white">{{ $stats['exams_done'] }} امتحان</span>
                    <span class="px-4 py-2 text-night-900">من {{ $stats['exams_total'] }}</span>
                </p>
            </div>

            {{-- متوسط النتائج --}}
            <div class="flex flex-col items-center gap-5">
                <div class="donut" style="--donut-value: {{ $stats['avg_percent'] }}; --donut-color: #a855f7;">
                    <div class="donut-inner">
                        <div>
                            <p class="text-2xl font-black text-slate-900 dark:text-white">{{ $stats['avg_percent'] }} %</p>
                            <p class="mt-1 text-sm font-extrabold text-slate-500 dark:text-slate-300">
                                {{ $stats['avg_percent'] >= 85 ? 'اشطر واحد ❤' : ($stats['avg_percent'] >= 40 ? 'فاضل حبة!' : 'يلا ابدأ!') }}
                            </p>
                        </div>
                    </div>
                </div>
                <p class="text-lg font-black text-slate-900 dark:text-white">متوسط النتائج اللي جبتها</p>
            </div>
        </div>
    </div>

    {{-- تعديل البيانات --}}
    <div class="card-pad mt-6">
        <h2 class="text-lg font-extrabold text-slate-900 dark:text-white">تعديل بياناتي</h2>
        <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">
            تقدر تعدل البيانات دي بنفسك — باقي البيانات (الاسم والموبايل والصف) بيتعدلوا عن طريق الدعم.
        </p>

        <form method="POST" action="{{ route('student.profile.update') }}" enctype="multipart/form-data" class="mt-5 grid gap-5 sm:grid-cols-2">
            @csrf
            @method('PUT')

            <div>
                <label for="email" class="label">البريد الإلكتروني</label>
                <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}"
                       class="input" dir="ltr" autocomplete="email" required>
                @error('email')<p class="error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="school" class="label">المدرسة</label>
                <input id="school" name="school" type="text" value="{{ old('school', $user->school) }}"
                       class="input" placeholder="اسم مدرستك">
                @error('school')<p class="error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="father_phone" class="label">رقم موبايل الأب</label>
                <input id="father_phone" name="father_phone" type="tel" value="{{ old('father_phone', $user->father_phone) }}"
                       class="input" dir="ltr" inputmode="numeric" maxlength="11" placeholder="01XXXXXXXXX">
                @error('father_phone')<p class="error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="mother_phone" class="label">رقم موبايل الأم</label>
                <input id="mother_phone" name="mother_phone" type="tel" value="{{ old('mother_phone', $user->mother_phone) }}"
                       class="input" dir="ltr" inputmode="numeric" maxlength="11" placeholder="01XXXXXXXXX">
                @error('mother_phone')<p class="error">{{ $message }}</p>@enderror
            </div>

            <div class="sm:col-span-2">
                <label for="avatar" class="label">الصورة الشخصية</label>
                <input id="avatar" name="avatar" type="file" accept="image/*"
                       class="input cursor-pointer p-2 file:ml-3 file:cursor-pointer file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-1.5 file:text-xs file:font-bold file:text-brand-700 dark:file:bg-brand-500/10 dark:file:text-brand-300">
                <p class="mt-1 text-xs font-medium text-slate-400 dark:text-slate-500">JPG أو PNG أو WEBP — بحد أقصى 2 ميجا</p>
                @error('avatar')<p class="error">{{ $message }}</p>@enderror
            </div>

            <div class="sm:col-span-2">
                <button type="submit" class="btn-primary">حفظ التعديلات</button>
            </div>
        </form>
    </div>

    {{-- تغيير كلمة المرور --}}
    <div class="card-pad mt-6">
        <h2 class="text-lg font-extrabold text-slate-900 dark:text-white">تغيير كلمة المرور</h2>

        <form method="POST" action="{{ route('student.profile.password') }}" class="mt-5 grid gap-5 sm:grid-cols-3">
            @csrf
            @method('PUT')

            <div>
                <label for="current_password" class="label">كلمة المرور الحالية</label>
                <input id="current_password" name="current_password" type="password"
                       class="input" placeholder="••••••••" autocomplete="current-password" required>
                @error('current_password')<p class="error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="password" class="label">كلمة المرور الجديدة</label>
                <input id="password" name="password" type="password"
                       class="input" placeholder="8 أحرف على الأقل" autocomplete="new-password" required>
                @error('password')<p class="error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="password_confirmation" class="label">تأكيد كلمة المرور</label>
                <input id="password_confirmation" name="password_confirmation" type="password"
                       class="input" placeholder="اكتبها تاني" autocomplete="new-password" required>
                @error('password_confirmation')<p class="error">{{ $message }}</p>@enderror
            </div>

            <div class="sm:col-span-3">
                <button type="submit" class="btn-primary">تغيير كلمة المرور</button>
            </div>
        </form>
    </div>
@endsection
