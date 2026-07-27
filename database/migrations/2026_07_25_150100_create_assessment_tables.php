<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // الواجبات
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lecture_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_path')->nullable(); // ملف الواجب PDF
            $table->unsignedInteger('max_score')->default(100);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('assignment_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('answer_text')->nullable();
            $table->string('file_path')->nullable(); // صورة الحل بخط اليد
            $table->string('status', 20)->default('submitted'); // submitted|graded
            $table->decimal('score', 8, 2)->nullable();
            $table->text('feedback')->nullable();
            $table->foreignId('graded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('graded_at')->nullable();
            $table->timestamps();
            $table->unique(['assignment_id', 'user_id']);
        });

        // الامتحانات والكويزات
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lecture_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type', 20)->default('quiz'); // quiz|shamel|evaluation|personal
            $table->unsignedInteger('duration_minutes')->default(30);
            $table->unsignedTinyInteger('passing_percent')->nullable();
            $table->boolean('hints_enabled')->default(false);
            // instant = النتيجة والاجابات فور التسليم | after_window = النتيجة فورا والإجابات النموذجية بعد غلق الامتحان
            $table->string('answers_policy', 20)->default('instant');
            $table->timestamp('window_opens_at')->nullable();
            $table->timestamp('window_closes_at')->nullable();
            $table->unsignedInteger('max_attempts')->default(1);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->nullable()->constrained()->cascadeOnDelete(); // null = بنك الأسئلة
            $table->string('subject', 60)->nullable()->index(); // المادة/الفرع
            $table->string('type', 20)->default('mcq'); // mcq|essay
            $table->text('body');
            $table->string('image_path')->nullable();
            $table->string('audio_path')->nullable();
            $table->text('hint')->nullable();
            $table->text('explanation')->nullable();          // شرح الإجابة
            $table->string('explanation_image')->nullable();
            $table->string('explanation_video_url')->nullable();
            $table->decimal('points', 8, 2)->default(1);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::create('question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->text('body')->nullable();
            $table->string('image_path')->nullable();
            $table->string('audio_path')->nullable();
            $table->boolean('is_correct')->default(false);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::create('exam_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('started_at');
            $table->timestamp('submitted_at')->nullable();
            $table->decimal('score', 8, 2)->default(0);
            $table->decimal('total', 8, 2)->default(0);
            $table->decimal('percent', 5, 2)->default(0);
            $table->boolean('passed')->default(false);
            $table->boolean('fully_graded')->default(false); // المقالي اتصحح كله
            $table->timestamps();
            $table->index(['exam_id', 'user_id']);
        });

        Schema::create('attempt_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_attempt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_option_id')->nullable()->constrained()->nullOnDelete();
            $table->text('essay_text')->nullable();
            $table->string('essay_image')->nullable();
            $table->boolean('is_correct')->nullable(); // null = لسه متصححش (مقالي)
            $table->decimal('points_awarded', 8, 2)->default(0);
            $table->foreignId('graded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['exam_attempt_id', 'question_id']);
        });

        // اختبر أخطاءك — بنك الأخطاء الشخصي
        Schema::create('mistakes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->string('subject', 60)->nullable()->index();
            $table->unsignedInteger('times_wrong')->default(1);
            $table->timestamp('last_wrong_at')->nullable();
            $table->boolean('resolved')->default(false); // جاوبها صح في إعادة الاختبار
            $table->timestamps();
            $table->unique(['user_id', 'question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mistakes');
        Schema::dropIfExists('attempt_answers');
        Schema::dropIfExists('exam_attempts');
        Schema::dropIfExists('question_options');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('exams');
        Schema::dropIfExists('assignment_submissions');
        Schema::dropIfExists('assignments');
    }
};
