<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * إدارة نصوص وصور الموقع القابلة للتعديل من لوحة التحكم.
 * أي نص أو صورة في أي صفحة تمر من هنا عبر الدالة المساعدة setting().
 */
class SettingsService
{
    /** @var array<string, string|null>|null */
    protected static ?array $cache = null;

    public static function get(string $key, ?string $default = null): ?string
    {
        if (static::$cache === null) {
            static::$cache = static::load();
        }

        $value = static::$cache[$key] ?? null;

        return ($value === null || $value === '') ? $default : $value;
    }

    /** يسجل المفتاح تلقائياً في لوحة التحكم أول ما يستخدم في صفحة */
    public static function remember(string $key, string $group, string $label, string $type, ?string $default = null): ?string
    {
        if (static::$cache === null) {
            static::$cache = static::load();
        }

        if (! array_key_exists($key, static::$cache)) {
            SiteSetting::firstOrCreate(
                ['key' => $key],
                ['group' => $group, 'label' => $label, 'type' => $type, 'value' => $default],
            );
            static::$cache[$key] = $default;
            Cache::forget('site_settings.all');
        }

        return static::get($key, $default);
    }

    public static function set(string $key, ?string $value): void
    {
        SiteSetting::where('key', $key)->update(['value' => $value]);
        static::$cache = null;
        Cache::forget('site_settings.all');
    }

    /** @return array<string, string|null> */
    protected static function load(): array
    {
        try {
            if (! Schema::hasTable('site_settings')) {
                return [];
            }

            return Cache::rememberForever(
                'site_settings.all',
                fn () => SiteSetting::pluck('value', 'key')->all(),
            );
        } catch (\Throwable) {
            return []; // قبل تشغيل الهجرات
        }
    }

    public static function flush(): void
    {
        static::$cache = null;
        Cache::forget('site_settings.all');
    }
}
