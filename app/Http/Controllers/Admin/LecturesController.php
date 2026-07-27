<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lecture;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LecturesController extends Controller
{
    public function index(Course $course): View
    {
        return view('admin.lectures.index', [
            'course' => $course,
            'lectures' => $course->lectures()->withCount('videos', 'attachments')->get(),
        ]);
    }

    public function store(Request $request, Course $course): RedirectResponse
    {
        $data = $this->validated($request);
        $data['position'] = $data['position'] ?? ($course->lectures()->max('position') + 1);

        $lecture = $course->lectures()->create($data);

        return redirect()->route('admin.lectures.edit', $lecture)
            ->with('status', 'تم إنشاء المحاضرة — أضف الفيديوهات والملفات والواجب والامتحان.');
    }

    /** صفحة إدارة محتوى المحاضرة بالكامل */
    public function edit(Lecture $lecture): View
    {
        $lecture->load(['videos', 'attachments', 'assignment', 'exam.questions']);

        return view('admin.lectures.edit', ['lecture' => $lecture, 'course' => $lecture->course]);
    }

    public function update(Request $request, Lecture $lecture): RedirectResponse
    {
        $lecture->update($this->validated($request));

        return back()->with('status', 'تم حفظ بيانات المحاضرة.');
    }

    public function destroy(Lecture $lecture): RedirectResponse
    {
        $courseId = $lecture->course_id;
        $lecture->delete();

        return redirect()->route('admin.lectures.index', $courseId)->with('status', 'تم حذف المحاضرة.');
    }

    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'position' => ['nullable', 'integer', 'min:0'],
            'passing_percent' => ['nullable', 'integer', 'between:1,100'],
            'is_published' => ['nullable', 'boolean'],
            'is_free_preview' => ['nullable', 'boolean'],
        ], [], ['title' => 'اسم المحاضرة']);

        $data['is_published'] = $request->boolean('is_published', true);
        $data['is_free_preview'] = $request->boolean('is_free_preview');

        return $data;
    }
}
