<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attachment;
use App\Models\Lecture;
use App\Models\Video;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/** إدارة محتوى المحاضرة: فيديوهات + ملفات + واجب */
class ContentController extends Controller
{
    public function storeVideo(Request $request, Lecture $lecture): RedirectResponse
    {
        $data = $this->videoRules($request);
        $data['position'] = $lecture->videos()->max('position') + 1;

        $lecture->videos()->create($data);

        return back()->with('status', 'تم إضافة الفيديو.');
    }

    public function updateVideo(Request $request, Video $video): RedirectResponse
    {
        $video->update($this->videoRules($request));

        return back()->with('status', 'تم تحديث الفيديو.');
    }

    public function destroyVideo(Video $video): RedirectResponse
    {
        $video->delete();

        return back()->with('status', 'تم حذف الفيديو.');
    }

    public function storeAttachment(Request $request, Lecture $lecture): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'file' => ['required', 'file', 'mimes:pdf', 'max:51200'],
        ], [], ['title' => 'اسم الملف', 'file' => 'ملف PDF']);

        $lecture->attachments()->create([
            'title' => $data['title'],
            'file_path' => Storage::disk('supabase_public')
                ->putFile('public-uploads/attachments', $request->file('file')),
            'type' => 'pdf',
            'position' => $lecture->attachments()->max('position') + 1,
        ]);

        return back()->with('status', 'تم رفع الملف.');
    }

    public function destroyAttachment(Attachment $attachment): RedirectResponse
    {
        $attachment->delete();

        return back()->with('status', 'تم حذف الملف.');
    }

    /** إنشاء/تعديل واجب المحاضرة */
    public function saveAssignment(Request $request, Lecture $lecture): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'max_score' => ['required', 'integer', 'min:1'],
            'file' => ['nullable', 'file', 'mimes:pdf', 'max:51200'],
            'is_published' => ['nullable', 'boolean'],
        ], [], ['title' => 'عنوان الواجب', 'max_score' => 'الدرجة الكلية']);

        if ($request->hasFile('file')) {
            $data['file_path'] = Storage::disk('supabase_public')
                ->putFile('public-uploads/assignments', $request->file('file'));
        }

        unset($data['file']);
        $data['is_published'] = $request->boolean('is_published', true);

        $lecture->assignment()->updateOrCreate(['lecture_id' => $lecture->id], $data);

        return back()->with('status', 'تم حفظ الواجب.');
    }

    protected function videoRules(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'takeaways' => ['nullable', 'string'],
            'source' => ['required', Rule::in(['youtube', 'vimeo', 'bunny', 'file'])],
            'url' => ['required', 'string', 'max:500'],
            'duration_seconds' => ['required', 'integer', 'min:1'],
        ], [], [
            'title' => 'عنوان الفيديو', 'url' => 'رابط/معرف الفيديو',
            'duration_seconds' => 'المدة بالثواني',
        ]);
    }
}
