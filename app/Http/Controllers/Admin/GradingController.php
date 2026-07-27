<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssignmentSubmission;
use App\Models\AttemptAnswer;
use App\Models\AuditLog;
use App\Models\Question;
use App\Services\ExamService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** تصحيح الواجبات والأسئلة المقالية (مهمة المدرس والمساعدين) */
class GradingController extends Controller
{
    public function __construct(protected ExamService $examService)
    {
    }

    public function index(Request $request): View
    {
        $homework = AssignmentSubmission::query()
            ->where('status', 'submitted')
            ->with(['user', 'assignment.lecture.course'])
            ->oldest()
            ->paginate(10, ['*'], 'hw_page');

        $essays = AttemptAnswer::query()
            ->whereNull('is_correct')
            ->whereHas('question', fn ($q) => $q->where('type', Question::TYPE_ESSAY))
            ->whereHas('attempt', fn ($q) => $q->whereNotNull('submitted_at'))
            ->with(['question', 'attempt.user', 'attempt.exam'])
            ->oldest()
            ->paginate(10, ['*'], 'essay_page');

        return view('admin.grading.index', compact('homework', 'essays'));
    }

    /** رصد درجة الواجب */
    public function gradeHomework(Request $request, AssignmentSubmission $submission): RedirectResponse
    {
        $data = $request->validate([
            'score' => ['required', 'numeric', 'min:0', 'max:' . $submission->assignment->max_score],
            'feedback' => ['nullable', 'string', 'max:1000'],
        ], [], ['score' => 'الدرجة', 'feedback' => 'ملاحظات']);

        $submission->update([
            'score' => $data['score'],
            'feedback' => $data['feedback'] ?? null,
            'status' => 'graded',
            'graded_by' => $request->user()->id,
            'graded_at' => now(),
        ]);

        AuditLog::record('homework.grade', $submission, ['score' => $data['score']]);

        return back()->with('status', 'تم رصد درجة الواجب.');
    }

    /** تصحيح سؤال مقالي — يعيد حساب نتيجة المحاولة تلقائياً */
    public function gradeEssay(Request $request, AttemptAnswer $answer): RedirectResponse
    {
        $data = $request->validate([
            'points' => ['required', 'numeric', 'min:0', 'max:' . (float) $answer->question->points],
        ], [], ['points' => 'الدرجة']);

        $this->examService->gradeEssay($answer, (float) $data['points'], $request->user());

        AuditLog::record('essay.grade', $answer, ['points' => $data['points']]);

        return back()->with('status', 'تم تصحيح الإجابة المقالية.');
    }
}
