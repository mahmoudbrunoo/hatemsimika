<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\QaReplyRequest;
use App\Http\Requests\QaThreadRequest;
use App\Models\Lecture;
use App\Models\QaThread;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class QaController extends Controller
{
    /** نشر سؤال على الدرس — يدخل قائمة المراجعة قبل الظهور */
    public function store(QaThreadRequest $request, Lecture $lecture): RedirectResponse
    {
        abort_unless($request->user()->isEnrolledIn($lecture->course), 403);

        QaThread::create([
            'lecture_id' => $lecture->id,
            'user_id' => $request->user()->id,
            'body' => $request->validated('body'),
            'image_path' => $this->uploadAttachment($request, 'image', 'qa-images'),
            'audio_path' => $this->uploadAttachment($request, 'audio', 'qa-audio'),
            'status' => QaThread::STATUS_PENDING,
        ]);

        return back()->with('status', 'تم إرسال سؤالك — سيظهر بعد مراجعته من المساعدين.');
    }

    /**
     * تعليق/رد على سؤال منشور — القفل يمنع الطلاب ما عدا صاحب السؤال،
     * والأدمن والمدرسون يردون دائماً (QaThreadPolicy@reply)
     */
    public function reply(QaReplyRequest $request, QaThread $thread): RedirectResponse
    {
        Gate::authorize('reply', $thread);

        $thread->replies()->create([
            'user_id' => $request->user()->id,
            'body' => $request->validated('body'),
            'image_path' => $this->uploadAttachment($request, 'image', 'qa-images'),
            'audio_path' => $this->uploadAttachment($request, 'audio', 'qa-audio'),
            'is_official_answer' => false,
        ]);

        return back()->with('status', 'تم إضافة تعليقك على السؤال.');
    }

    /** رفع مرفق (صورة/صوت) على الباكت العام وإرجاع رابطه — أو null إذا لم يُرفق */
    private function uploadAttachment(Request $request, string $field, string $folder): ?string
    {
        if (! $request->hasFile($field)) {
            return null;
        }

        $path = Storage::disk('supabase_public')->putFile($folder, $request->file($field));

        return Storage::disk('supabase_public')->url($path);
    }
}
