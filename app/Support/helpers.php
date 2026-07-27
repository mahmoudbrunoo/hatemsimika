<?php

use App\Services\SettingsService;
use Illuminate\Support\Facades\Storage;

if (! function_exists('setting')) {
    /**
     * نص/رابط قابل للتعديل من لوحة التحكم.
     * setting('hero.title', 'العنوان الافتراضي')
     */
    function setting(string $key, ?string $default = null): ?string
    {
        return SettingsService::get($key, $default);
    }
}

if (! function_exists('setting_image')) {
    /** صورة قابلة للتعديل: ترجع رابط الصورة المرفوعة أو الافتراضية */
    function setting_image(string $key, ?string $default = null): ?string
    {
        $value = SettingsService::get($key);

        if ($value === null || $value === '') {
            return $default;
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://') || str_starts_with($value, '/')) {
            return $value;
        }

        return Storage::url($value);
    }
}

if (! function_exists('egp')) {
    /** تنسيق مبلغ بالجنيه المصري */
    function egp(float|int|string|null $amount): string
    {
        return number_format((float) $amount, 2) . ' جنيه';
    }
}
