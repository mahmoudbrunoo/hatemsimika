<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * توحيد صيغة activity_date إلى Y-m-d — الـ cast القديم كان يخزنها
     * بلاحقة "00:00:00" فلا تطابقها استعلامات firstOrCreate/upsert النصية.
     */
    public function up(): void
    {
        foreach (DB::table('daily_activities')->orderBy('id')->cursor() as $row) {
            $clean = substr((string) $row->activity_date, 0, 10);

            if ($clean !== $row->activity_date) {
                DB::table('daily_activities')->where('id', $row->id)->update(['activity_date' => $clean]);
            }
        }
    }

    public function down(): void
    {
        // الصيغة النظيفة صالحة للقراءة في كل الأحوال — لا حاجة للتراجع
    }
};
