@extends('layouts.app')

@section('title', 'إنشاء حساب جديد — ' . setting('site.name', 'منصة حاتم سميكة'))

@section('content')
    <section class="mx-auto max-w-2xl px-4 py-12 sm:px-6 lg:py-16">
        <div class="text-center">
            <div class="mx-auto grid size-14 place-items-center rounded-2xl bg-brand-50 text-2xl dark:bg-brand-500/10">🚀</div>
            <h1 class="mt-4 text-2xl font-black text-slate-900 dark:text-white">ابدأ رحلتك الآن</h1>
            <p class="mt-2 text-sm font-semibold text-slate-500 dark:text-slate-400">
                املأ بياناتك بدقة — هنراجع الحساب ونفعّله في أقرب وقت.
            </p>
        </div>

        <div class="card-pad mt-8">
            <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data" class="grid gap-5 sm:grid-cols-2">
                @csrf

                <div class="sm:col-span-2">
                    <label for="name" class="label">اسم الطالب</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}"
                           class="input" placeholder="مثال: أحمد محمد علي حسن"
                           autocomplete="name" autofocus required>
                    <p class="mt-1 text-xs font-medium text-slate-400 dark:text-slate-500">الاسم رباعي باللغة العربية</p>
                    @error('name')<p class="error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="email" class="label">البريد الإلكتروني</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}"
                           class="input" dir="ltr" placeholder="example@mail.com"
                           autocomplete="email" required>
                    @error('email')<p class="error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="phone" class="label">رقم موبايل الطالب</label>
                    <input id="phone" name="phone" type="tel" value="{{ old('phone') }}"
                           class="input" dir="ltr" inputmode="numeric" maxlength="11" placeholder="01XXXXXXXXX"
                           autocomplete="tel" required>
                    @error('phone')<p class="error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="father_phone" class="label">رقم موبايل الأب</label>
                    <input id="father_phone" name="father_phone" type="tel" value="{{ old('father_phone') }}"
                           class="input" dir="ltr" inputmode="numeric" maxlength="11" placeholder="01XXXXXXXXX" required>
                    @error('father_phone')<p class="error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="mother_phone" class="label">رقم موبايل الأم</label>
                    <input id="mother_phone" name="mother_phone" type="tel" value="{{ old('mother_phone') }}"
                           class="input" dir="ltr" inputmode="numeric" maxlength="11" placeholder="01XXXXXXXXX" required>
                    @error('mother_phone')<p class="error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="governorate" class="label">المحافظة</label>
                    <select id="governorate" name="governorate" class="input" required>
                        <option value="" disabled @selected(!old('governorate'))>اختر محافظتك</option>
                        @foreach (\App\Models\User::GOVERNORATES as $governorate)
                            <option value="{{ $governorate }}" @selected(old('governorate') === $governorate)>{{ $governorate }}</option>
                        @endforeach
                    </select>
                    @error('governorate')<p class="error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="school" class="label">المدرسة</label>
                    <input id="school" name="school" type="text" value="{{ old('school') }}"
                           class="input" placeholder="اسم مدرستك" required>
                    @error('school')<p class="error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="academic_year" class="label">الصف الدراسي</label>
                    <select id="academic_year" name="academic_year" class="input" required>
                        <option value="" disabled @selected(!old('academic_year'))>اختر صفك الدراسي</option>
                        @foreach (\App\Models\User::YEARS as $year => $label)
                            <option value="{{ $year }}" @selected((int) old('academic_year') === $year)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('academic_year')<p class="error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <span class="label">النوع</span>
                    <div class="flex gap-3">
                        <label class="flex flex-1 cursor-pointer items-center justify-center gap-2 rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm font-semibold text-slate-700 has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50 has-[:checked]:text-brand-700 dark:border-slate-700 dark:text-slate-200 dark:has-[:checked]:border-brand-500 dark:has-[:checked]:bg-brand-500/10 dark:has-[:checked]:text-brand-300">
                            <input type="radio" name="gender" value="male" @checked(old('gender') === 'male')
                                   class="size-4 border-slate-300 text-brand-600 focus:ring-brand-500/40 dark:border-slate-700 dark:bg-slate-950" required>
                            ذكر
                        </label>
                        <label class="flex flex-1 cursor-pointer items-center justify-center gap-2 rounded-xl border border-slate-300 px-3.5 py-2.5 text-sm font-semibold text-slate-700 has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50 has-[:checked]:text-brand-700 dark:border-slate-700 dark:text-slate-200 dark:has-[:checked]:border-brand-500 dark:has-[:checked]:bg-brand-500/10 dark:has-[:checked]:text-brand-300">
                            <input type="radio" name="gender" value="female" @checked(old('gender') === 'female')
                                   class="size-4 border-slate-300 text-brand-600 focus:ring-brand-500/40 dark:border-slate-700 dark:bg-slate-950" required>
                            أنثى
                        </label>
                    </div>
                    @error('gender')<p class="error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="national_id" class="label">الرقم القومي</label>
                    <input id="national_id" name="national_id" type="text" value="{{ old('national_id') }}"
                           class="input" dir="ltr" inputmode="numeric" maxlength="14" placeholder="14 رقم" required>
                    <p class="mt-1 text-xs font-medium text-slate-400 dark:text-slate-500">زي ما هو مكتوب في البطاقة أو شهادة الميلاد (<span dir="ltr">14</span> رقم)</p>
                    @error('national_id')<p class="error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="id_photo" class="label">صورة البطاقة / الكارنيه</label>
                    <input id="id_photo" name="id_photo" type="file" accept="image/*"
                           class="input cursor-pointer p-2 file:ml-3 file:cursor-pointer file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-1.5 file:text-xs file:font-bold file:text-brand-700 dark:file:bg-brand-500/10 dark:file:text-brand-300" required>
                    <p class="mt-1 text-xs font-medium text-slate-400 dark:text-slate-500">صورة واضحة (JPG أو PNG — بحد أقصى 5 ميجا)</p>
                    @error('id_photo')<p class="error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="password" class="label">كلمة المرور</label>
                    <input id="password" name="password" type="password"
                           class="input" placeholder="8 أحرف على الأقل"
                           autocomplete="new-password" required>
                    @error('password')<p class="error">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="password_confirmation" class="label">تأكيد كلمة المرور</label>
                    <input id="password_confirmation" name="password_confirmation" type="password"
                           class="input" placeholder="اكتب كلمة المرور تاني"
                           autocomplete="new-password" required>
                    @error('password_confirmation')<p class="error">{{ $message }}</p>@enderror
                </div>

                <div class="sm:col-span-2">
                    <button type="submit" class="btn-primary w-full py-3 text-base">إنشاء الحساب</button>
                </div>
            </form>
        </div>

        <p class="mt-6 text-center text-sm font-semibold text-slate-500 dark:text-slate-400">
            عندك حساب بالفعل؟
            <a href="{{ route('login') }}" class="font-extrabold text-brand-600 hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300">
                سجل دخولك
            </a>
        </p>
    </section>
@endsection
