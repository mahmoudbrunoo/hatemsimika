<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * ترحيل الملفات المحلية القديمة إلى Supabase Storage:
 * - كل ملفات القرص العام المحلي => باكت platform-uploads بنفس المسارات (فتظل الروابط القديمة في قاعدة البيانات صالحة).
 * - صور البطاقات من القرص الخاص المحلي => الباكت الخاص تحت student-ids/ مع تحديث مسارات المستخدمين.
 */
class MigrateStorageToSupabase extends Command
{
    protected $signature = 'supabase:migrate-storage {--dry-run : عرض ما سيتم نقله دون تنفيذ}';

    protected $description = 'نقل الملفات المرفوعة محلياً إلى باكتات Supabase (العام والخاص)';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');

        $this->migratePublicFiles($dry);
        $this->migrateIdPhotos($dry);

        $this->info($dry ? 'انتهت المعاينة — لم يُنقل أي ملف.' : 'اكتمل الترحيل.');

        return self::SUCCESS;
    }

    protected function migratePublicFiles(bool $dry): void
    {
        $source = Storage::disk('public');
        $target = Storage::disk('supabase_public');

        foreach ($source->allFiles() as $path) {
            if (str_starts_with(basename($path), '.')) {
                continue; // ملفات نظام مثل .gitignore
            }

            if (str_starts_with($path, 'id-photos/')) {
                continue; // بطاقات قديمة على القرص العام تُنقل للباكت الخاص
            }

            if ($target->exists($path)) {
                $this->line("موجود مسبقاً: {$path}");
                continue;
            }

            if (! $dry) {
                $stream = $source->readStream($path);
                $target->writeStream($path, $stream);
                is_resource($stream) && fclose($stream);
            }

            $this->info("عام => platform-uploads/{$path}");
        }
    }

    protected function migrateIdPhotos(bool $dry): void
    {
        $target = Storage::disk('supabase_private');

        foreach (['local', 'public'] as $diskName) {
            $source = Storage::disk($diskName);

            foreach ($source->allFiles('id-photos') as $path) {
                $newPath = 'student-ids/' . basename($path);

                if (! $target->exists($newPath)) {
                    if (! $dry) {
                        $stream = $source->readStream($path);
                        $target->writeStream($newPath, $stream);
                        is_resource($stream) && fclose($stream);
                    }

                    $this->info("خاص => private/{$newPath}");
                } else {
                    $this->line("موجود مسبقاً: {$newPath}");
                }

                if (! $dry) {
                    $updated = User::where('id_photo_path', $path)->update(['id_photo_path' => $newPath]);

                    if ($updated > 0) {
                        $this->line("تم تحديث {$updated} مستخدم: {$path} => {$newPath}");
                    }
                }
            }
        }
    }
}
