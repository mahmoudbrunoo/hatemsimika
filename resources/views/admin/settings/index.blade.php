@extends('layouts.admin')

@section('title', 'محتوى الموقع — لوحة التحكم')
@section('page-title', 'محتوى الموقع')

@php
    $groupLabels = [
        'site' => 'بيانات الموقع الأساسية',
        'header' => 'الهيدر',
        'hero' => 'الواجهة الرئيسية (الهيرو)',
        'why' => 'قسم ليه تشترك معانا؟',
        'features' => 'قسم المميزات',
        'about' => 'عن المدرس',
        'footer' => 'الفوتر',
        'social' => 'روابط السوشيال ميديا',
        'payments' => 'أرقام وطرق الدفع',
        'payment' => 'أرقام وطرق الدفع',
        'whatsapp' => 'الواتساب',
        'contact' => 'بيانات التواصل',
        'auth' => 'صفحات الدخول والتسجيل',
        'help' => 'صفحة المساعدة',
        'books' => 'صفحة الكتب',
        'courses' => 'صفحة الكورسات',
    ];
@endphp

@section('page')
    @if ($groups->isEmpty())
        <div class="card-pad text-center text-slate-500 dark:text-slate-400">
            لا توجد إعدادات مسجلة بعد — الإعدادات بتتسجل تلقائياً أول ما صفحاتها تتفتح.
        </div>
    @else
        <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="space-y-6 pb-24">
            @csrf

            @foreach ($groups as $group => $settings)
                <div class="card-pad">
                    <h2 class="mb-4 text-lg font-extrabold text-slate-900 dark:text-white">
                        {{ $groupLabels[$group] ?? $group }}
                    </h2>

                    <div class="grid gap-5 sm:grid-cols-2">
                        @foreach ($settings as $setting)
                            <div @class(['sm:col-span-2' => in_array($setting->type, ['textarea', 'image'])])>
                                <label for="setting-{{ $setting->id }}" class="label">{{ $setting->label }}</label>

                                @if ($setting->type === 'textarea')
                                    <textarea id="setting-{{ $setting->id }}" name="settings[{{ $setting->key }}]" rows="4"
                                              class="input">{{ old('settings.' . $setting->key, $setting->value) }}</textarea>

                                @elseif ($setting->type === 'image')
                                    <div class="flex flex-wrap items-start gap-4" x-data="{ preview: null }">
                                        @if (setting_image($setting->key))
                                            <img x-show="!preview" src="{{ setting_image($setting->key) }}" alt="{{ $setting->label }}"
                                                 class="h-20 max-w-40 rounded-xl border border-slate-300 object-contain dark:border-slate-700">
                                        @endif
                                        <template x-if="preview">
                                            <img :src="preview" alt="معاينة" class="h-20 max-w-40 rounded-xl border border-brand-300 object-contain">
                                        </template>
                                        <div class="min-w-60 flex-1">
                                            <input id="setting-{{ $setting->id }}" type="file" name="settings_files[{{ $setting->key }}]"
                                                   accept="image/jpeg,image/png,image/webp,image/svg+xml"
                                                   @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null"
                                                   class="input cursor-pointer p-2 file:ml-3 file:cursor-pointer file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-1.5 file:text-xs file:font-bold file:text-brand-700 dark:file:bg-brand-500/10 dark:file:text-brand-300">
                                            <p class="mt-1 text-xs font-medium text-slate-400">ارفع صورة جديدة للاستبدال — أو اتركه فاضي للإبقاء على الحالية (بحد أقصى 4 ميجا)</p>
                                        </div>
                                    </div>
                                    @error('settings_files.' . $setting->key)<p class="error">{{ $message }}</p>@enderror

                                @elseif ($setting->type === 'bool')
                                    <select id="setting-{{ $setting->id }}" name="settings[{{ $setting->key }}]" class="input">
                                        <option value="1" @selected(old('settings.' . $setting->key, $setting->value) == '1')>مفعّل</option>
                                        <option value="0" @selected(old('settings.' . $setting->key, $setting->value) != '1')>موقوف</option>
                                    </select>

                                @elseif ($setting->type === 'number')
                                    <input id="setting-{{ $setting->id }}" type="number" name="settings[{{ $setting->key }}]"
                                           value="{{ old('settings.' . $setting->key, $setting->value) }}" class="input" dir="ltr">

                                @elseif ($setting->type === 'url')
                                    <input id="setting-{{ $setting->id }}" type="url" name="settings[{{ $setting->key }}]"
                                           value="{{ old('settings.' . $setting->key, $setting->value) }}" class="input" dir="ltr"
                                           placeholder="https://...">

                                @else
                                    <input id="setting-{{ $setting->id }}" type="text" name="settings[{{ $setting->key }}]"
                                           value="{{ old('settings.' . $setting->key, $setting->value) }}" class="input">
                                @endif

                                <p class="mt-1 text-xs font-medium text-slate-400" dir="ltr">{{ $setting->key }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

            {{-- شريط الحفظ الثابت --}}
            <div class="fixed inset-x-0 bottom-0 z-40 border-t border-slate-300 bg-surface/90 px-4 py-3 backdrop-blur dark:border-slate-800 dark:bg-slate-900/90">
                <div class="mx-auto flex max-w-7xl items-center justify-between gap-3">
                    <p class="hidden text-sm font-semibold text-slate-500 dark:text-slate-400 sm:block">
                        التغييرات بتظهر فوراً في كل صفحات الموقع بعد الحفظ.
                    </p>
                    <button type="submit" class="btn-primary">حفظ كل التغييرات</button>
                </div>
            </div>
        </form>
    @endif
@endsection
