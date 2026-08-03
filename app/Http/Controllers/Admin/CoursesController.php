<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CoursesController extends Controller
{
    public function index(): View
    {
        return view('admin.courses.index', [
            'courses' => Course::withCount('lectures', 'enrollments')
                ->orderBy('academic_year')->orderBy('position')->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.courses.form', ['course' => new Course]);
    }

    public function store(Request $request): RedirectResponse
    {
        $course = Course::create($this->validated($request));

        return redirect()->route('admin.lectures.index', $course)
            ->with('status', 'تم إنشاء الكورس — أضف المحاضرات الآن.');
    }

    public function edit(Course $course): View
    {
        return view('admin.courses.form', ['course' => $course]);
    }

    public function update(Request $request, Course $course): RedirectResponse
    {
        $course->update($this->validated($request, $course));

        return redirect()->route('admin.courses.index')->with('status', 'تم حفظ التعديلات.');
    }

    public function destroy(Course $course): RedirectResponse
    {
        $course->delete();

        return back()->with('status', 'تم حذف الكورس.');
    }

    protected function validated(Request $request, ?Course $course = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'syllabus' => ['nullable', 'string'],
            'academic_year' => ['required', Rule::in([1, 2, 3])],
            'category' => ['required', Rule::in(array_keys(Course::CATEGORIES))],
            'price' => ['required', 'numeric', 'min:0'],
            'discount_price' => ['nullable', 'numeric', 'min:0', 'lt:price'],
            'access_days' => ['nullable', 'integer', 'min:1'],
            'passing_percent' => ['required', 'integer', 'between:1,100'],
            'position' => ['nullable', 'integer', 'min:0'],
            'is_featured' => ['nullable', 'boolean'],
            'is_published' => ['nullable', 'boolean'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ], [], [
            'title' => 'اسم الكورس', 'price' => 'السعر', 'discount_price' => 'السعر بعد الخصم',
            'academic_year' => 'الصف الدراسي', 'category' => 'التصنيف', 'thumbnail' => 'صورة الغلاف',
        ]);

        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_published'] = $request->boolean('is_published');
        $data['position'] = $data['position'] ?? 0;

        if ($course === null || $course->title !== $data['title']) {
            $base = Str::slug($data['title']) ?: 'course';
            $slug = $base . '-' . Str::lower(Str::random(5));
            $data['slug'] = $slug;
        }

        if ($request->hasFile('thumbnail')) {
            $path = Storage::disk('supabase_public')->putFile('courses', $request->file('thumbnail'));
            $data['thumbnail_path'] = Storage::disk('supabase_public')->url($path);
        }

        unset($data['thumbnail']);

        return $data;
    }
}
