<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\QaThreadRequest;
use App\Models\AuditLog;
use App\Models\QaThread;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * مراجعة أسئلة الطلاب على الدروس:
 * السؤال يظهر بعد الموافقة — وإجابة المساعد/المدرس تقفل الموضوع تلقائياً
 * ليبقى مرجعاً معتمداً دائماً.
 */
class QaModerationController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString() ?: QaThread::STATUS_PENDING;

        $threads = QaThread::query()
            ->where('status', $status)
            ->with(['user', 'lecture.course', 'replies.user'])
            ->oldest()
            ->paginate(10)
            ->withQueryString();

        $counts = [
            'pending' => QaThread::where('status', QaThread::STATUS_PENDING)->count(),
            'approved' => QaThread::where('status', QaThread::STATUS_APPROVED)->count(),
            'rejected' => QaThread::where('status', QaThread::STATUS_REJECTED)->count(),
        ];

        return view('admin.qa.index', compact('threads', 'status', 'counts'));
    }

    public function approve(Request $request, QaThread $thread): RedirectResponse
    {
        $thread->update([
            'status' => QaThread::STATUS_APPROVED,
            'moderated_by' => $request->user()->id,
        ]);

        AuditLog::record('qa.approve', $thread);

        return back()->with('status', 'تم نشر السؤال.');
    }

    public function reject(Request $request, QaThread $thread): RedirectResponse
    {
        $thread->update([
            'status' => QaThread::STATUS_REJECTED,
            'moderated_by' => $request->user()->id,
        ]);

        AuditLog::record('qa.reject', $thread);

        return back()->with('status', 'تم رفض السؤال.');
    }

    /** قفل/فتح التعليقات يدوياً — القفل يمنع تعليقات الطلاب عدا صاحب السؤال */
    public function toggleLock(Request $request, QaThread $thread): RedirectResponse
    {
        $locked = ! $thread->is_locked;

        $thread->update(['is_locked' => $locked]);

        AuditLog::record($locked ? 'qa.lock' : 'qa.unlock', $thread);

        return back()->with('status', $locked ? 'تم قفل الموضوع — تعليقات الطلاب متوقفة.' : 'تم فتح الموضوع للتعليقات من جديد.');
    }

    /** إجابة رسمية => نشر السؤال وقفل التعليقات تلقائياً — والرد ممكن حتى على المقفول */
    public function answer(Request $request, QaThread $thread): RedirectResponse
    {
        $data = $request->validate(
            [
                'body' => ['nullable', 'required_without:audio', 'string', 'max:5000'],
                'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
                'audio' => ['nullable', 'file', 'mimetypes:' . implode(',', QaThreadRequest::AUDIO_MIMETYPES), 'max:10240'],
            ],
            ['body.required_without' => 'اكتب نص الإجابة أو سجّل رسالة صوتية.'],
            ['body' => 'نص الإجابة', 'image' => 'الصورة المرفقة', 'audio' => 'الرسالة الصوتية'],
        );

        $thread->replies()->create([
            'user_id' => $request->user()->id,
            'body' => $data['body'] ?? null,
            'image_path' => $this->uploadAttachment($request, 'image', 'qa-images'),
            'audio_path' => $this->uploadAttachment($request, 'audio', 'qa-audio'),
            'is_official_answer' => true,
        ]);

        // أول إجابة تسجل صاحبها وتاريخها — والإجابات الإضافية لا تمحو ذلك
        $thread->update([
            'status' => QaThread::STATUS_APPROVED,
            'is_locked' => true,
            'moderated_by' => $thread->moderated_by ?? $request->user()->id,
            'answered_by' => $thread->answered_by ?? $request->user()->id,
            'answered_at' => $thread->answered_at ?? now(),
        ]);

        AuditLog::record('qa.answer', $thread);

        return back()->with('status', 'تم نشر الإجابة وقفل الموضوع كمرجع معتمد.');
    }

    /** رفع مرفق الإجابة (صورة/صوت) لنفس مسارات مرفقات أسئلة الطلاب على الباكت العام */
    private function uploadAttachment(Request $request, string $field, string $folder): ?string
    {
        if (! $request->hasFile($field)) {
            return null;
        }

        $path = Storage::disk('supabase_public')->putFile($folder, $request->file($field));

        return Storage::disk('supabase_public')->url($path);
    }
}
