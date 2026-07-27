<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // الاسم رباعي
            $table->string('email')->unique();
            $table->string('phone', 20)->unique();            // موبايل الطالب
            $table->string('father_phone', 20)->nullable();   // موبايل الأب
            $table->string('mother_phone', 20)->nullable();   // موبايل الأم
            $table->string('national_id', 14)->nullable()->unique(); // الرقم القومي
            $table->string('id_photo_path')->nullable();      // صورة البطاقة / كارنيه الطالب
            $table->string('governorate', 60)->nullable();    // المحافظة
            $table->string('school')->nullable();             // المدرسة
            $table->unsignedTinyInteger('academic_year')->nullable()->index(); // 1|2|3 ثانوي
            $table->string('gender', 10)->nullable();         // male|female
            $table->string('status', 30)->default('PENDING_REVIEW')->index(); // PENDING_REVIEW|APPROVED|REJECTED|BANNED
            $table->text('rejection_reason')->nullable();
            $table->decimal('balance', 10, 2)->default(0);    // رصيد المحفظة
            $table->string('center_id', 30)->nullable()->index(); // ID طالب السنتر
            $table->string('theme', 10)->default('light');    // light|dark
            $table->string('avatar_path')->nullable();
            $table->string('current_session_id')->nullable()->index(); // إنفاذ الجلسة الواحدة
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
