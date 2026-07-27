<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Exam;
use App\Models\Lecture;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** إدارة الامتحانات: كويزات المحاضرات والشوامل واختبارات التقييم */
class ExamsController extends Controller
{
    public function index(Request $request): View
    {
        $type = $request->string('type')->toString() ?: null;

        $exams = Exam::query()
            ->where('type', '!=', Exam::TYPE_PERSONAL)
            ->when($type, fn ($q) => $q->where('type', $type))
            ->with(['lecture.course', 'course'])
            ->withCount(['questions', 'attempts'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.exams.index', compact('exams', 'type'));
    }

    public function create(): View
    {
        return view('admin.exams.form', [
            'exam' => new Exam,
            'courses' => Course::orderBy('academic_year')->orderBy('position')->get(),
            'lectures' => Lecture::with('course')->orderBy('course_id')->orderBy('position')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $exam = Exam::create($this->validated($request));

        return redirect()->route('admin.questions.index', $exam)
            ->with('status', 'تم إنشاء الامتحان — أضف الأسئلة الآن.');
    }

    public function edit(Exam $exam): View
    {
        return view('admin.exams.form', [
            'exam' => $exam,
            'courses' => Course::orderBy('academic_year')->orderBy('position')->get(),
            'lectures' => Lecture::with('course')->orderBy('course_id')->orderBy('position')->get(),
        ]);
    }

    public function update(Request $request, Exam $exam): RedirectResponse
    {
        $exam->update($this->validated($request));

        return redirect()->route('admin.exams.index')->with('status', 'تم حفظ الامتحان.');
    }

    public function destroy(Exam $exam): RedirectResponse
    {
        $exam->delete();

        return back()->with('status', 'تم حذف الامتحان.');
    }

    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', Rule::in([Exam::TYPE_QUIZ, Exam::TYPE_SHAMEL, Exam::TYPE_EVALUATION])],
            'lecture_id' => ['nullable', 'exists:lectures,id'],
            'course_id' => ['nullable', 'exists:courses,id'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:600'],
            'passing_percent' => ['nullable', 'integer', 'between:1,100'],
            'max_attempts' => ['required', 'integer', 'min:1', 'max:100'],
            'answers_policy' => ['required', Rule::in(['instant', 'after_window'])],
            'window_opens_at' => ['nullable', 'date'],
            'window_closes_at' => ['nullable', 'date', 'after:window_opens_at'],
            'hints_enabled' => ['nullable', 'boolean'],
            'is_published' => ['nullable', 'boolean'],
        ], [], [
            'title' => 'اسم الامتحان', 'type' => 'نوع الامتحان',
            'duration_minutes' => 'المدة بالدقائق', 'max_attempts' => 'عدد المحاولات',
            'answers_policy' => 'سياسة إظهار الإجابات',
            'window_opens_at' => 'يفتح في', 'window_closes_at' => 'يغلق في',
        ]);

        $data['hints_enabled'] = $request->boolean('hints_enabled');
        $data['is_published'] = $request->boolean('is_published', true);

        return $data;
    }
}
