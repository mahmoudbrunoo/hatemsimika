<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->text('syllabus')->nullable(); // تقسيمة المنهج
            $table->unsignedTinyInteger('academic_year')->index(); // 1|2|3 ثانوي
            // semester_1 | semester_2 | full_bundle (أولى ثانوي) — monthly | three_months (تانية وتالتة)
            $table->string('category', 30)->index();
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('discount_price', 10, 2)->nullable(); // السعر بعد الخصم (سلاش برايسنج)
            $table->string('thumbnail_path')->nullable();
            $table->boolean('is_featured')->default(false)->index();
            $table->boolean('is_published')->default(false)->index();
            $table->unsignedInteger('position')->default(0);
            $table->unsignedInteger('access_days')->nullable(); // مدة صلاحية الاشتراك بالأيام (null = دائم)
            $table->unsignedTinyInteger('passing_percent')->default(60); // نسبة النجاح الافتراضية
            $table->timestamps();
        });

        Schema::create('lectures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->unsignedTinyInteger('passing_percent')->nullable(); // يورث من الكورس لو فاضي
            $table->boolean('is_published')->default(true);
            $table->boolean('is_free_preview')->default(false);
            $table->timestamps();
            $table->index(['course_id', 'position']);
        });

        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lecture_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('takeaways')->nullable(); // أهم النقاط المستفادة
            $table->string('source', 20)->default('youtube'); // youtube|vimeo|bunny|file
            $table->string('url')->nullable();      // معرف/رابط الفيديو
            $table->string('file_path')->nullable(); // لو مرفوع محلياً
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
            $table->index(['lecture_id', 'position']);
        });

        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lecture_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('file_path');
            $table->string('type', 20)->default('pdf');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attachments');
        Schema::dropIfExists('videos');
        Schema::dropIfExists('lectures');
        Schema::dropIfExists('courses');
    }
};
