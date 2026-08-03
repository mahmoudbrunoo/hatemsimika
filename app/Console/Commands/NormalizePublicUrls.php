<?php

namespace App\Console\Commands;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Attachment;
use App\Models\AttemptAnswer;
use App\Models\Book;
use App\Models\Course;
use App\Models\Order;
use App\Models\QaThread;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\User;
use App\Models\Video;
use App\Services\SettingsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * توحيد روابط الأصول العامة: تحويل المسارات النسبية القديمة المخزنة في قاعدة البيانات
 * إلى روابط Supabase العامة الكاملة — لأن الواجهات صارت تعرض العمود مباشرة بلا Storage::url().
 * (المسارات الخاصة مثل بطاقات الطلاب id_photo_path تبقى نسبية وتُعرض عبر روابط موقعة مؤقتة.)
 */
class NormalizePublicUrls extends Command
{
    protected $signature = 'supabase:normalize-public-urls {--dry-run : عرض ما سيتم تحويله دون تنفيذ}';

    protected $description = 'تحويل مسارات الأصول العامة المخزنة نسبياً إلى روابط Supabase عامة كاملة';

    /** أعمدة الأصول العامة لكل موديل */
    protected array $targets = [
        Course::class => ['thumbnail_path'],
        Book::class => ['cover_path', 'preview_pdf_path'],
        Attachment::class => ['file_path'],
        Assignment::class => ['file_path'],
        AssignmentSubmission::class => ['file_path'],
        Video::class => ['file_path'],
        Question::class => ['image_path', 'audio_path', 'explanation_image'],
        QuestionOption::class => ['image_path', 'audio_path'],
        QaThread::class => ['image_path'],
        Order::class => ['receipt_path'],
        AttemptAnswer::class => ['essay_image'],
        User::class => ['avatar_path'],
    ];

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $disk = Storage::disk('supabase_public');
        $total = 0;

        foreach ($this->targets as $model => $columns) {
            $table = (new $model)->getTable();

            foreach ($columns as $column) {
                $rows = DB::table($table)
                    ->select('id', $column)
                    ->whereNotNull($column)
                    ->where($column, '!=', '')
                    ->where($column, 'not like', 'http%')
                    ->get();

                foreach ($rows as $row) {
                    $url = $disk->url(ltrim($row->{$column}, '/'));
                    $this->line("{$table}.{$column} #{$row->id}: {$row->{$column}} => {$url}");

                    if (! $dry) {
                        DB::table($table)->where('id', $row->id)->update([$column => $url]);
                    }

                    $total++;
                }
            }
        }

        // قيم الإعدادات: نحوّل فقط ما يبدو مسار صورة مرفوعة تحت settings/ حتى لا نلمس النصوص والروابط
        $settings = DB::table('site_settings')
            ->select('id', 'key', 'value')
            ->where('value', 'like', 'settings/%')
            ->get();

        foreach ($settings as $setting) {
            $url = $disk->url($setting->value);
            $this->line("site_settings.{$setting->key}: {$setting->value} => {$url}");

            if (! $dry) {
                DB::table('site_settings')->where('id', $setting->id)->update(['value' => $url]);
            }

            $total++;
        }

        if (! $dry && $settings->isNotEmpty()) {
            SettingsService::flush();
        }

        $this->info($dry ? "معاينة فقط — {$total} قيمة ستتحول." : "تم تحويل {$total} قيمة.");

        return self::SUCCESS;
    }
}
