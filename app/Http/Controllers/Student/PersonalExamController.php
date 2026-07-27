<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\ExamService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** اختبر أخطاءك — امتحان خاص بيك من أخطائك السابقة */
class PersonalExamController extends Controller
{
    public function __construct(protected ExamService $examService)
    {
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $subject = $request->string('subject')->toString() ?: null;

        $subjects = $user->mistakes()
            ->where('resolved', false)
            ->whereNotNull('subject')
            ->distinct()
            ->pluck('subject');

        $count = $user->mistakes()
            ->where('resolved', false)
            ->when($subject, fn ($q) => $q->where('subject', $subject))
            ->count();

        return view('student.personal-exam', compact('subjects', 'subject', 'count'));
    }

    public function start(Request $request): RedirectResponse
    {
        $subject = $request->string('subject')->toString() ?: null;

        try {
            $exam = $this->examService->buildPersonalExam($request->user(), $subject, 55);
            $attempt = $this->examService->startAttempt($exam, $request->user());
        } catch (\RuntimeException $e) {
            return back()->withErrors(['subject' => $e->getMessage()]);
        }

        return redirect()->route('student.exams.take', $attempt);
    }

    /** بنك الأسئلة: تصفح أخطائك ومراجعتها */
    public function bank(Request $request): View
    {
        $mistakes = $request->user()->mistakes()
            ->with(['question.options'])
            ->orderByDesc('last_wrong_at')
            ->paginate(10);

        return view('student.question-bank', ['mistakes' => $mistakes]);
    }
}
