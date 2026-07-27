<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // متابعة مشاهدة الفيديو
        Schema::create('video_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('video_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('seconds_watched')->default(0);
            $table->unsignedInteger('last_position')->default(0);
            $table->boolean('completed')->default(false);
            $table->timestamps();
            $table->unique(['user_id', 'video_id']);
        });

        // تقدم الطالب في المحاضرة (بوابات الفتح)
        Schema::create('lecture_progresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lecture_id')->constrained()->cascadeOnDelete();
            $table->boolean('homework_submitted')->default(false);
            $table->boolean('exam_passed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'lecture_id']);
        });

        // نشاط يومي لرسم المذاكرة الأسبوعي
        Schema::create('daily_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('activity_date')->index();
            $table->unsignedInteger('videos_watched')->default(0);
            $table->unsignedInteger('quizzes_completed')->default(0);
            $table->unsignedInteger('seconds_spent')->default(0);
            $table->timestamps();
            $table->unique(['user_id', 'activity_date']);
        });

        // منتدى الأسئلة على الدروس
        Schema::create('qa_threads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lecture_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('body');
            $table->string('image_path')->nullable();
            $table->string('status', 30)->default('PENDING_MODERATION'); // PENDING_MODERATION|APPROVED|REJECTED
            $table->boolean('is_locked')->default(false);
            $table->foreignId('moderated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('answered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('answered_at')->nullable();
            $table->timestamps();
            $table->index(['lecture_id', 'status']);
        });

        Schema::create('qa_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qa_thread_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('body');
            $table->boolean('is_official_answer')->default(false); // إجابة مدرس/مساعد => قفل
            $table->timestamps();
        });

        // سجل أجهزة الدخول (صفحة الأمان)
        Schema::create('login_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('session_id')->nullable()->index();
            $table->string('device_type', 30)->nullable();  // Desktop|Mobile|Tablet
            $table->string('device_name', 100)->nullable();
            $table->string('os', 60)->nullable();
            $table->string('browser', 60)->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamp('logged_in_at')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamp('logged_out_at')->nullable();
            $table->string('logout_reason', 40)->nullable(); // manual|new_device|admin
            $table->timestamps();
        });

        // سجل تدقيق شامل (الانتحال وغيره)
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 60)->index(); // impersonate.start|user.approve|order.approve|...
            $table->nullableMorphs('target');
            $table->json('meta')->nullable();
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });

        // إعدادات الموقع القابلة للتعديل (نصوص وصور كل الصفحات)
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('group', 60)->index();   // header|hero|footer|about|payments|social|...
            $table->string('label');                // وصف بالعربي للوحة التحكم
            $table->string('type', 20)->default('text'); // text|textarea|image|url|number|bool
            $table->text('value')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });

        // الأسئلة الشائعة (للشات بوت والمساعدة)
        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->string('question');
            $table->text('answer');
            $table->string('keywords')->nullable(); // كلمات مفتاحية للبوت
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('faqs');
        Schema::dropIfExists('site_settings');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('login_activities');
        Schema::dropIfExists('qa_replies');
        Schema::dropIfExists('qa_threads');
        Schema::dropIfExists('daily_activities');
        Schema::dropIfExists('lecture_progresses');
        Schema::dropIfExists('video_views');
    }
};
