<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\QaThread;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    /** إجابة رسمية => نشر السؤال وقفل التعليقات تلقائياً */
    public function answer(Request $request, QaThread $thread): RedirectResponse
    {
        $data = $request->validate(
            ['body' => ['required', 'string', 'max:5000']],
            [],
            ['body' => 'نص الإجابة'],
        );

        $thread->replies()->create([
            'user_id' => $request->user()->id,
            'body' => $data['body'],
            'is_official_answer' => true,
        ]);

        $thread->update([
            'status' => QaThread::STATUS_APPROVED,
            'is_locked' => true,
            'moderated_by' => $thread->moderated_by ?? $request->user()->id,
            'answered_by' => $request->user()->id,
            'answered_at' => now(),
        ]);

        AuditLog::record('qa.answer', $thread);

        return back()->with('status', 'تم نشر الإجابة وقفل الموضوع كمرجع معتمد.');
    }
}
