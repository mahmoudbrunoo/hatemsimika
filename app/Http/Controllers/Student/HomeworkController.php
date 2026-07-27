<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\HomeworkSubmitRequest;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Services\ProgressionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeworkController extends Controller
{
    public function __construct(protected ProgressionService $progression)
    {
    }

    public function show(Request $request, Assignment $assignment): View
    {
        $this->authorizeAssignment($request, $assignment);

        return view('student.learn.homework', [
            'assignment' => $assignment,
            'lecture' => $assignment->lecture,
            'course' => $assignment->lecture->course,
            'submission' => $assignment->submissionFor($request->user()),
        ]);
    }

    /** تسليم الواجب => فتح الامتحان */
    public function submit(HomeworkSubmitRequest $request, Assignment $assignment): RedirectResponse
    {
        $this->authorizeAssignment($request, $assignment);

        if ($assignment->submissionFor($request->user()) !== null) {
            return back()->withErrors(['answer_text' => 'تم تسليم الواجب بالفعل.']);
        }

        $filePath = $request->hasFile('file')
            ? $request->file('file')->store('homework', 'public')
            : null;

        AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $request->user()->id,
            'answer_text' => $request->validated('answer_text'),
            'file_path' => $filePath,
            'status' => 'submitted',
        ]);

        $this->progression->markHomeworkSubmitted($request->user(), $assignment->lecture);

        return redirect()
            ->route('student.learn.lecture', [$assignment->lecture->course, $assignment->lecture])
            ->with('status', 'تم تسليم الواجب — الامتحان اتفتح لك دلوقتي!');
    }

    protected function authorizeAssignment(Request $request, Assignment $assignment): void
    {
        abort_unless($request->user()->isEnrolledIn($assignment->lecture->course), 403);
        abort_unless($this->progression->isLectureUnlocked($request->user(), $assignment->lecture), 403);
    }
}
