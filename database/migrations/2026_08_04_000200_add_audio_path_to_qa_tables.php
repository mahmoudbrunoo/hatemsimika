<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * الرسائل الصوتية في أسئلة الدروس: السؤال أو الرد ممكن يكون نص، نص بصورة،
 * أو تسجيل صوتي مباشر من المتصفح (أو ملف صوتي مرفوع) — نفس نمط image_path.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('qa_threads', function (Blueprint $table) {
            $table->string('audio_path')->nullable()->after('image_path');
            $table->text('body')->nullable()->change(); // الرسالة الصوتية تغني عن النص
        });

        Schema::table('qa_replies', function (Blueprint $table) {
            $table->string('audio_path')->nullable()->after('image_path');
            $table->text('body')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('qa_threads', function (Blueprint $table) {
            $table->dropColumn('audio_path');
            $table->text('body')->nullable(false)->change();
        });

        Schema::table('qa_replies', function (Blueprint $table) {
            $table->dropColumn('audio_path');
            $table->text('body')->nullable(false)->change();
        });
    }
};
