<?php

namespace App\Services;

use App\Models\AttemptAnswer;
use App\Models\DailyActivity;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Mistake;
use App\Models\Question;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ExamService
{
    public function __construct(protected ProgressionService $progression)
    {
    }

    public function startAttempt(Exam $exam, User $user): ExamAttempt
    {
        $open = $exam->attemptsFor($user)->whereNull('submitted_at')->first();

        if ($open !== null) {
            if ($open->remainingSeconds() > 0) {
                return $open; // استكمال محاولة جارية
            }
            $this->submitAttempt($open, []); // انتهى الوقت — تسليم تلقائي
        }

        if ($exam->attemptsFor($user)->whereNotNull('submitted_at')->count() >= $exam->max_attempts) {
            throw new RuntimeException('استنفدت عدد المحاولات المسموح بها');
        }

        if (! $exam->isWindowOpen()) {
            throw new RuntimeException('الامتحان غير متاح حالياً');
        }

        return ExamAttempt::create([
            'exam_id' => $exam->id,
            'user_id' => $user->id,
            'started_at' => now(),
            'total' => $exam->totalPoints(),
        ]);
    }

    /**
     * حفظ مسودة الإجابات أولاً بأول أثناء الحل (الحفظ التلقائي الفوري).
     * بتتخزن في attempt_answers نفسها (is_correct = null لحد التسليم) بدون أي تصحيح،
     * فلو النت فصل أو الجهاز اتقفل — آخر اختيارات الطالب محفوظة على السيرفر.
     * $answers: [question_id => option_id] · $essays: [question_id => text]
     */
    public function saveDraft(ExamAttempt $attempt, array $answers, array $essays = []): void
    {
        if ($attempt->isSubmitted()) {
            return;
        }

        $questions = $attempt->exam->questions()->with('options')->get()->keyBy('id');

        foreach ($answers as $questionId => $optionId) {
            $question = $questions->get((int) $questionId);

            // نتجاهل أي سؤال أو اختيار مش تابع للامتحان (حماية من عبث الطلبات)
            if ($question === null || ! $question->isMcq()) {
                continue;
            }

            if ($optionId !== null && ! $question->options->contains('id', (int) $optionId)) {
                continue;
            }

            AttemptAnswer::updateOrCreate(
                ['exam_attempt_id' => $attempt->id, 'question_id' => $question->id],
                ['question_option_id' => $optionId !== null ? (int) $optionId : null],
            );
        }

        foreach ($essays as $questionId => $text) {
            $question = $questions->get((int) $questionId);

            if ($question === null || $question->isMcq()) {
                continue;
            }

            AttemptAnswer::updateOrCreate(
                ['exam_attempt_id' => $attempt->id, 'question_id' => $question->id],
                ['essay_text' => $text !== '' ? $text : null],
            );
        }
    }

    /** المسودة المحفوظة بصيغة submitAttempt: [question_id => ['option_id', 'essay_text', 'essay_image']] */
    public function draftAnswers(ExamAttempt $attempt): array
    {
        return $attempt->answers()
            ->get()
            ->mapWithKeys(fn (AttemptAnswer $answer) => [
                $answer->question_id => [
                    'option_id' => $answer->question_option_id,
                    'essay_text' => $answer->essay_text,
                    'essay_image' => $answer->essay_image,
                ],
            ])
            ->all();
    }

    /**
     * تسليم كل المحاولات اللي انتهى وقتها لمستخدم معيّن (أو للجميع) بآخر مسودة محفوظة.
     * بتتنادى قبل عرض السجل وبأمر مجدول — فالمحاولة المنتهية بتتقفل تلقائياً حتى لو الطالب مرجعش.
     */
    public function submitExpired(?User $user = null): int
    {
        $query = ExamAttempt::whereNull('submitted_at')->with('exam');

        if ($user !== null) {
            $query->where('user_id', $user->id);
        }

        $count = 0;

        foreach ($query->get() as $attempt) {
            if ($attempt->exam !== null && $attempt->isExpired()) {
                $this->submitAttempt($attempt, []);
                $count++;
            }
        }

        return $count;
    }

    /**
     * تسليم المحاولة وتصحيح الاختياري فوراً.
     * $answers: [question_id => ['option_id' => ?, 'essay_text' => ?, 'essay_image' => ?]]
     * الإجابات المرسلة بتتدمج فوق المسودة المحفوظة — فالتسليم التلقائي (بمصفوفة فاضية)
     * بيحتسب آخر اختيارات اتحفظت قبل الخروج أو انقطاع النت.
     */
    public function submitAttempt(ExamAttempt $attempt, array $answers): ExamAttempt
    {
        if ($attempt->isSubmitted()) {
            return $attempt;
        }

        return DB::transaction(function () use ($attempt, $answers) {
            $exam = $attempt->exam;
            $drafts = $this->draftAnswers($attempt);
            $score = 0.0;
            $hasEssay = false;

            foreach ($exam->questions as $question) {
                $given = ($answers[$question->id] ?? []) + ($drafts[$question->id] ?? []);
                $optionId = $given['option_id'] ?? null;
                $isCorrect = null;
                $points = 0.0;

                if ($question->isMcq()) {
                    $correct = $question->correctOption();
                    $isCorrect = $optionId !== null && $correct !== null && (int) $optionId === $correct->id;
                    $points = $isCorrect ? (float) $question->points : 0.0;
                    $score += $points;

                    $this->recordMistake($attempt->user_id, $question, $isCorrect);
                } else {
                    $hasEssay = true; // المقالي يتصحح يدوياً
                }

                // updateOrCreate: صف المسودة المحفوظ أثناء الحل بيتحوّل لإجابة نهائية مصححة
                AttemptAnswer::updateOrCreate(
                    ['exam_attempt_id' => $attempt->id, 'question_id' => $question->id],
                    [
                        'question_option_id' => $question->isMcq() ? $optionId : null,
                        'essay_text' => $given['essay_text'] ?? null,
                        'essay_image' => $given['essay_image'] ?? null,
                        'is_correct' => $isCorrect,
                        'points_awarded' => $points,
                    ],
                );
            }

            $total = (float) $attempt->total ?: $exam->totalPoints();
            $percent = $total > 0 ? round($score / $total * 100, 2) : 0;

            $attempt->update([
                'submitted_at' => now(),
                'score' => $score,
                'percent' => $percent,
                'passed' => $percent >= $exam->passingPercent(),
                'fully_graded' => ! $hasEssay,
            ]);

            DailyActivity::track($attempt->user_id, 'quizzes_completed');

            // النجاح في امتحان المحاضرة يفتح المحاضرة التالية
            if ($attempt->passed && $exam->lecture !== null) {
                $this->progression->markExamPassed($attempt->user, $exam->lecture);
            }

            return $attempt->refresh();
        });
    }

    /** تصحيح سؤال مقالي يدوياً (مساعد/مدرس) */
    public function gradeEssay(AttemptAnswer $answer, float $points, User $grader): void
    {
        $question = $answer->question;
        $points = max(0, min($points, (float) $question->points));

        $answer->update([
            'points_awarded' => $points,
            'is_correct' => $points >= (float) $question->points / 2,
            'graded_by' => $grader->id,
        ]);

        $this->recordMistake($answer->attempt->user_id, $question, (bool) $answer->is_correct);
        $this->recalculate($answer->attempt);
    }

    protected function recalculate(ExamAttempt $attempt): void
    {
        $attempt->refresh();
        $score = (float) $attempt->answers()->sum('points_awarded');
        $total = (float) $attempt->total ?: $attempt->exam->totalPoints();
        $percent = $total > 0 ? round($score / $total * 100, 2) : 0;
        $fullyGraded = $attempt->answers()->whereNull('is_correct')->doesntExist();
        $passed = $percent >= $attempt->exam->passingPercent();

        $attempt->update([
            'score' => $score,
            'percent' => $percent,
            'passed' => $passed,
            'fully_graded' => $fullyGraded,
        ]);

        if ($passed && $attempt->exam->lecture !== null) {
            $this->progression->markExamPassed($attempt->user, $attempt->exam->lecture);
        }
    }

    /** تحديث بنك الأخطاء الشخصي (اختبر أخطاءك) */
    protected function recordMistake(int $userId, Question $question, ?bool $isCorrect): void
    {
        if ($isCorrect === false) {
            $mistake = Mistake::firstOrNew([
                'user_id' => $userId,
                'question_id' => $question->id,
            ]);
            $mistake->subject = $question->subject;
            $mistake->times_wrong = ($mistake->exists ? $mistake->times_wrong : 0) + 1;
            $mistake->last_wrong_at = now();
            $mistake->resolved = false;
            $mistake->save();
        } elseif ($isCorrect === true) {
            Mistake::where('user_id', $userId)
                ->where('question_id', $question->id)
                ->update(['resolved' => true]);
        }
    }

    /** إنشاء امتحان خاص من أخطاء الطالب */
    public function buildPersonalExam(User $user, ?string $subject, int $count): Exam
    {
        $query = $user->mistakes()->where('resolved', false);

        if ($subject) {
            $query->where('subject', $subject);
        }

        $questionIds = $query->orderByDesc('last_wrong_at')->limit($count)->pluck('question_id');

        if ($questionIds->isEmpty()) {
            throw new RuntimeException('لا توجد أخطاء متاحة لإعادة الاختبار');
        }

        $exam = Exam::create([
            'title' => 'امتحان خاص بيك — ' . ($subject ?: 'كل المواد'),
            'type' => Exam::TYPE_PERSONAL,
            'duration_minutes' => max(10, $questionIds->count() * 2),
            'passing_percent' => 60,
            'answers_policy' => 'instant',
            'max_attempts' => 100,
            'is_published' => true,
        ]);

        // نسخ الأسئلة داخل الامتحان الشخصي عشان تصحيحه مستقل
        foreach (Question::with('options')->findMany($questionIds) as $i => $original) {
            $clone = $original->replicate(['exam_id', 'position']);
            $clone->exam_id = $exam->id;
            $clone->position = $i;
            $clone->save();

            foreach ($original->options as $option) {
                $optionClone = $option->replicate(['question_id']);
                $optionClone->question_id = $clone->id;
                $optionClone->save();
            }
        }

        return $exam;
    }
}
