<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ResultsController extends Controller
{
    /** نتائج الامتحانات (كويزات وشوامل) */
    public function exams(Request $request): View
    {
        return view('student.results.exams', [
            'attempts' => $this->attemptsQuery($request, [Exam::TYPE_QUIZ, Exam::TYPE_SHAMEL, Exam::TYPE_PERSONAL]),
            'title' => 'نتائج الامتحانات',
        ]);
    }

    /** نتائج اختبارات التقييم */
    public function evaluations(Request $request): View
    {
        return view('student.results.exams', [
            'attempts' => $this->attemptsQuery($request, [Exam::TYPE_EVALUATION]),
            'title' => 'نتائج اختبارات التقييم',
        ]);
    }

    /** نتائج الواجب */
    public function homework(Request $request): View
    {
        $submissions = $request->user()->assignmentSubmissions()
            ->with(['assignment.lecture'])
            ->latest()
            ->paginate(10);

        return view('student.results.homework', ['submissions' => $submissions]);
    }

    /** تفاصيل المشاهدات */
    public function watchDetails(Request $request): View
    {
        $views = $request->user()->videoViews()
            ->with(['video.lecture.course'])
            ->latest('updated_at')
            ->paginate(10);

        $totalSeconds = (int) $request->user()->videoViews()->sum('seconds_watched');

        return view('student.watch-details', [
            'views' => $views,
            'totalHours' => round($totalSeconds / 3600, 1),
        ]);
    }

    protected function attemptsQuery(Request $request, array $types)
    {
        return $request->user()->examAttempts()
            ->whereNotNull('submitted_at')
            ->whereHas('exam', fn ($q) => $q->whereIn('type', $types))
            ->with('exam')
            ->latest('submitted_at')
            ->paginate(10);
    }
}
