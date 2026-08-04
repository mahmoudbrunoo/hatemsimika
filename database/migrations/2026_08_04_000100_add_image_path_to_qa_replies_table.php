<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** مرفق صورة اختياري مع رد المدرس على أسئلة الطلاب — مثل صورة سؤال الطالب نفسها */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('qa_replies', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('body');
        });
    }

    public function down(): void
    {
        Schema::table('qa_replies', function (Blueprint $table) {
            $table->dropColumn('image_path');
        });
    }
};
