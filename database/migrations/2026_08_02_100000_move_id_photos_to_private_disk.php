<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    /**
     * نقل صور البطاقات المرفوعة سابقاً من القرص العام (رابط مكشوف)
     * إلى القرص الخاص — نفس المسار النسبي فلا حاجة لتعديل قاعدة البيانات.
     * آمن لإعادة التشغيل ويتخطى أي ملف يتعذر نقله دون كسر الهجرة.
     */
    public function up(): void
    {
        $public = Storage::disk('public');
        $private = Storage::disk('local');

        try {
            $files = $public->files('id-photos');
        } catch (\Throwable) {
            return; // لا مجلد صور على هذا القرص
        }

        foreach ($files as $path) {
            try {
                if (! $private->exists($path)) {
                    $private->put($path, $public->get($path));
                }

                $public->delete($path);
            } catch (\Throwable) {
                continue; // ملف متعذر — يظل مقروءاً للإدارة عبر مسار العرض المحمي
            }
        }
    }

    public function down(): void
    {
        // لا تراجع — إعادة الصور للقرص العام تكشفها برابط مباشر
    }
};
