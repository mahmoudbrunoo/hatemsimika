<?php

namespace App\Console\Commands;

use App\Services\ExamService;
use Illuminate\Console\Command;

class SubmitExpiredExamAttempts extends Command
{
    protected $signature = 'exams:submit-expired';

    protected $description = 'تسليم محاولات الامتحان اللي انتهى وقتها تلقائياً بآخر مسودة إجابات محفوظة';

    public function handle(ExamService $examService): int
    {
        $count = $examService->submitExpired();

        $this->info("تم تسليم {$count} محاولة منتهية الوقت.");

        return self::SUCCESS;
    }
}
