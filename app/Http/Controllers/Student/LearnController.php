<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\DailyActivity;
use App\Models\Lecture;
use App\Models\Video;
use App\Models\VideoView;
use App\Services\ProgressionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LearnController extends Controller
{
    public function __construct(protected ProgressionService $progression)
    {
    }

    /** صفحة الكورس: المحاضرات بحالات القفل */
    public function course(Request $request, Course $course): View
    {
        $this->authorizeAccess($request, $course);

        $lectures = $course->lectures()->where('is_published', true)->get()
            ->map(function (Lecture $lecture) use ($request) {
                $lecture->setAttribute('unlocked', $this->progression->isLectureUnlocked($request->user(), $lecture));
                $lecture->setAttribute('progress', $this->progression->progressFor($request->user(), $lecture));

                return $lecture;
            });

        return view('student.learn.course', compact('course', 'lectures'));
    }

    /** صفحة المحاضرة: فيديوهات ثم ملفات ثم واجب ثم امتحان (بالترتيب الإجباري) */
    public function lecture(Request $request, Course $course, Lecture $lecture): View
    {
        $this->authorizeAccess($request, $course);
        abort_unless($lecture->course_id === $course->id && $lecture->is_published, 404);
        abort_unless($this->progression->isLectureUnlocked($request->user(), $lecture), 403, 'اجتز امتحان المحاضرة السابقة الأول');

        $user = $request->user();
        $assignment = $lecture->assignment()->where('is_published', true)->first();
        $exam = $lecture->exam()->where('is_published', true)->first();

        return view('student.learn.lecture', [
            'course' => $course,
            'lecture' => $lecture,
            'videos' => $lecture->videos,
            'attachments' => $lecture->attachments,
            'assignment' => $assignment,
            'submission' => $assignment?->submissionFor($user),
            'exam' => $exam,
            'examUnlocked' => $this->progression->isExamUnlocked($user, $lecture),
            'progress' => $this->progression->progressFor($user, $lecture),
            'views' => $user->videoViews()->whereIn('video_id', $lecture->videos->pluck('id'))->get()->keyBy('video_id'),
        ]);
    }

    /** مشغل الفيديو مع العلامة المائية */
    public function video(Request $request, Course $course, Video $video): View
    {
        $this->authorizeAccess($request, $course);
        $lecture = $video->lecture;
        abort_unless($lecture->course_id === $course->id, 404);
        abort_unless($this->progression->isLectureUnlocked($request->user(), $lecture), 403);

        // أسئلة الدرس المعتمدة + سؤال الطالب نفسه أياً كانت حالته
        $threads = $lecture->qaThreads()
            ->with(['user', 'replies.user'])
            ->where(fn ($q) => $q->where('status', 'APPROVED')->orWhere('user_id', $request->user()->id))
            ->latest()
            ->take(20)
            ->get();

        return view('student.learn.video', [
            'course' => $course,
            'lecture' => $lecture,
            'video' => $video,
            'view' => VideoView::firstOrCreate(['user_id' => $request->user()->id, 'video_id' => $video->id]),
            'threads' => $threads,
        ]);
    }

    /** تتبع المشاهدة (نبضة كل 15 ثانية من المشغل) */
    public function progress(Request $request, Video $video): JsonResponse
    {
        $data = $request->validate([
            'position' => ['required', 'integer', 'min:0'],
            'delta' => ['required', 'integer', 'min:0', 'max:60'],
        ]);

        $user = $request->user();
        abort_unless($user->isEnrolledIn($video->lecture->course), 403);

        $view = VideoView::firstOrCreate(['user_id' => $user->id, 'video_id' => $video->id]);

        $completedNow = false;
        $duration = max(1, (int) $video->duration_seconds);
        $watched = min($view->seconds_watched + $data['delta'], $duration * 2);

        if (! $view->completed && $watched >= $duration * 0.85) {
            $completedNow = true;
        }

        $view->update([
            'seconds_watched' => $watched,
            'last_position' => $data['position'],
            'completed' => $view->completed || $completedNow,
        ]);

        DailyActivity::track($user->id, 'seconds_spent', $data['delta']);

        if ($completedNow) {
            DailyActivity::track($user->id, 'videos_watched');
        }

        return response()->json(['ok' => true, 'completed' => $view->completed]);
    }

    protected function authorizeAccess(Request $request, Course $course): void
    {
        abort_unless($request->user()->isEnrolledIn($course) || $request->user()->isStaff(), 403, 'اشترك في الكورس الأول');
    }
}
