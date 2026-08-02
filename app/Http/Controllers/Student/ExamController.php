<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Services\ExamService;
use App\Services\ProgressionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ExamController extends Controller
{
    public function __construct(
        protected ExamService $examService,
        protected ProgressionService $progression,
    ) {
    }

    /** صفحة تفاصيل الامتحان قبل البدء */
    public function show(Request $request, Exam $exam): View
    {
        $this->authorizeExam($request, $exam);

        return view('student.exams.show', [
            'exam' => $exam,
            'attempts' => $exam->attemptsFor($request->user())->whereNotNull('submitted_at')->latest()->get(),
            'questionsCount' => $exam->questions()->count(),
        ]);
    }

    public function start(Request $request, Exam $exam): RedirectResponse
    {
        $this->authorizeExam($request, $exam);

        try {
            $attempt = $this->examService->startAttempt($exam, $request->user());
        } catch (\RuntimeException $e) {
            return back()->withErrors(['exam' => $e->getMessage()]);
        }

        return redirect()->route('student.exams.take', $attempt);
    }

    /** واجهة حل الامتحان */
    public function take(Request $request, ExamAttempt $attempt): View|RedirectResponse
    {
        abort_unless($attempt->user_id === $request->user()->id, 403);

        if ($attempt->isSubmitted()) {
            return redirect()->route('student.exams.result', $attempt);
        }

        if ($attempt->remainingSeconds() <= 0) {
            $this->examService->submitAttempt($attempt, []);

            return redirect()->route('student.exams.result', $attempt);
        }

        return view('student.exams.take', [
            'attempt' => $attempt,
            'exam' => $attempt->exam,
            'questions' => $attempt->exam->questions()->with('options')->get(),
        ]);
    }

    public function submit(Request $request, ExamAttempt $attempt): RedirectResponse
    {
        abort_unless($attempt->user_id === $request->user()->id, 403);

        if ($attempt->isSubmitted()) {
            return redirect()->route('student.exams.result', $attempt);
        }

        $answers = [];

        foreach ((array) $request->input('answers', []) as $questionId => $optionId) {
            $answers[(int) $questionId]['option_id'] = $optionId ?: null;
        }

        foreach ((array) $request->input('essays', []) as $questionId => $text) {
            $answers[(int) $questionId]['essay_text'] = $text ?: null;
        }

        foreach ((array) $request->file('essay_images', []) as $questionId => $file) {
            if ($file !== null) {
                $answers[(int) $questionId]['essay_image'] = Storage::disk('supabase_public')
                    ->putFile('public-uploads/essay-answers', $file);
            }
        }

        $this->examService->submitAttempt($attempt, $answers);

        return redirect()->route('student.exams.result', $attempt);
    }

    /** النتيجة: الدرجة فورية، والإجابات النموذجية حسب سياسة الامتحان */
    public function result(Request $request, ExamAttempt $attempt): View
    {
        abort_unless($attempt->user_id === $request->user()->id, 403);
        abort_unless($attempt->isSubmitted(), 404);

        $exam = $attempt->exam;

        return view('student.exams.result', [
            'attempt' => $attempt,
            'exam' => $exam,
            'showAnswers' => $exam->canShowModelAnswers(),
            'answers' => $attempt->answers()->with(['question.options', 'option'])->get(),
        ]);
    }

    protected function authorizeExam(Request $request, Exam $exam): void
    {
        $user = $request->user();

        if ($exam->type === Exam::TYPE_PERSONAL) {
            return; // الامتحان الشخصي ملك صاحبه فقط ويتحقق عند البدء
        }

        $course = $exam->course ?? $exam->lecture?->course;

        if ($course !== null) {
            abort_unless($user->isEnrolledIn($course) || $user->isStaff(), 403, 'اشترك في الكورس الأول');
        }

        if ($exam->lecture !== null) {
            abort_unless($this->progression->isLectureUnlocked($user, $exam->lecture), 403, 'المحاضرة لسه مقفولة');
            abort_unless(
                $this->progression->isExamUnlocked($user, $exam->lecture),
                403,
                'سلم الواجب الأول عشان يتفتح الامتحان',
            );
        }
    }
}
