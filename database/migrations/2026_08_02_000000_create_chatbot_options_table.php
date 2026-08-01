<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // شجرة خيارات الشات بوت التفاعلي: كل خيار له أب اختياري => تداخل بلا حدود
        Schema::create('chatbot_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('chatbot_options')->cascadeOnDelete();
            $table->string('label', 120);                // نص الزر اللي يدوس عليه الطالب
            $table->text('response')->nullable();        // الرد النصي (يدعم HTML لروابط واتساب وغيرها)
            $table->string('link_url', 500)->nullable(); // زر رابط اختياري (واتساب/فيسبوك/...)
            $table->string('link_label', 120)->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['parent_id', 'is_active', 'position']);
        });

        // تسجيل مفاتيح فيديو شرح التسجيل في إعدادات الموقع (الإنتاج يشغل الهجرات فقط دون البذر)
        if (Schema::hasTable('site_settings')) {
            DB::table('site_settings')->insertOrIgnore([
                [
                    'key' => 'auth.video_url', 'group' => 'auth',
                    'label' => 'رابط فيديو شرح التسجيل (يوتيوب) — اتركه فاضي لإخفاء الزر',
                    'type' => 'url', 'value' => null, 'position' => 10,
                    'created_at' => now(), 'updated_at' => now(),
                ],
                [
                    'key' => 'auth.video_label', 'group' => 'auth',
                    'label' => 'نص زر فيديو الشرح',
                    'type' => 'text', 'value' => 'شاهد طريقة التسجيل', 'position' => 20,
                    'created_at' => now(), 'updated_at' => now(),
                ],
            ]);

            Cache::forget('site_settings.all');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbot_options');

        if (Schema::hasTable('site_settings')) {
            DB::table('site_settings')->whereIn('key', ['auth.video_url', 'auth.video_label'])->delete();
            Cache::forget('site_settings.all');
        }
    }
};
