<?php

namespace App\Services;

use App\Models\Lecture;
use App\Models\LectureProgress;
use App\Models\User;

/**
 * منطق التقدم الإجباري:
 * الفيديوهات والملفات مفتوحة داخل المحاضرة المفتوحة —
 * تسليم الواجب يفتح الامتحان — النجاح في الامتحان يفتح المحاضرة التالية.
 */
class ProgressionService
{
    /** هل المحاضرة مفتوحة للطالب؟ (أول محاضرة مفتوحة دائماً) */
    public function isLectureUnlocked(User $user, Lecture $lecture): bool
    {
        if ($lecture->is_free_preview) {
            return true;
        }

        $previous = $lecture->previous();

        if ($previous === null) {
            return true; // أول محاضرة في الكورس
        }

        return $this->progressFor($user, $previous)->exam_passed
            || $previous->exam()->where('is_published', true)->doesntExist();
    }

    /** هل امتحان المحاضرة مفتوح؟ (لازم الواجب يتسلم الأول) */
    public function isExamUnlocked(User $user, Lecture $lecture): bool
    {
        $assignment = $lecture->assignment()->where('is_published', true)->first();

        if ($assignment === null) {
            return true; // مفيش واجب => الامتحان مفتوح
        }

        return $this->progressFor($user, $lecture)->homework_submitted;
    }

    public function markHomeworkSubmitted(User $user, Lecture $lecture): void
    {
        $this->progressFor($user, $lecture)->update(['homework_submitted' => true]);
    }

    public function markExamPassed(User $user, Lecture $lecture): void
    {
        $this->progressFor($user, $lecture)->update([
            'exam_passed' => true,
            'completed_at' => now(),
        ]);
    }

    public function progressFor(User $user, Lecture $lecture): LectureProgress
    {
        return LectureProgress::firstOrCreate([
            'user_id' => $user->id,
            'lecture_id' => $lecture->id,
        ]);
    }
}
