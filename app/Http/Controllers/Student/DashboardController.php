<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $showAll = $request->boolean('all');

        // الكورسات المميزة لصف الطالب فقط — مع زر "عرض كل الصفوف"
        $featured = Course::published()
            ->when(! $showAll && $user->academic_year, fn ($q) => $q->forYear($user->academic_year))
            ->orderByDesc('is_featured')
            ->orderBy('position')
            ->take(9)
            ->get();

        // إحصائيات الحلقات
        $totalVideos = \App\Models\Video::whereIn(
            'lecture_id',
            \App\Models\Lecture::whereIn(
                'course_id',
                $user->enrollments()->where('status', 'active')->pluck('course_id'),
            )->pluck('id'),
        )->count();

        $watchedVideos = $user->videoViews()->where('completed', true)->count();
        $submittedAttempts = $user->examAttempts()->whereNotNull('submitted_at');
        $examsTaken = (clone $submittedAttempts)->count();
        $examsAvailable = \App\Models\Exam::where('is_published', true)
            ->where('type', '!=', \App\Models\Exam::TYPE_PERSONAL)
            ->count();
        $avgPercent = round((float) (clone $submittedAttempts)->avg('percent'), 1);

        // نشاط آخر 7 أيام للرسم البياني
        $week = $user->dailyActivities()
            ->whereDate('activity_date', '>=', now()->subDays(6)->toDateString())
            ->get()
            ->keyBy(fn ($a) => $a->activity_date->toDateString());

        $chart = collect(range(6, 0))->map(function ($daysAgo) use ($week) {
            $date = now()->subDays($daysAgo);
            $row = $week->get($date->toDateString());

            return [
                'label' => $date->locale('ar')->translatedFormat('l'),
                'videos' => $row->videos_watched ?? 0,
                'quizzes' => $row->quizzes_completed ?? 0,
                'hours' => round(($row->seconds_spent ?? 0) / 3600, 2),
            ];
        })->values();

        return view('student.dashboard', compact(
            'featured', 'showAll', 'totalVideos', 'watchedVideos',
            'examsTaken', 'examsAvailable', 'avgPercent', 'chart',
        ));
    }
}
