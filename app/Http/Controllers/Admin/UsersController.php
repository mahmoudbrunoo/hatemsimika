<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Course;
use App\Models\User;
use App\Services\EnrollmentService;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UsersController extends Controller
{
    public function __construct(
        protected WalletService $wallet,
        protected EnrollmentService $enrollment,
    ) {
    }

    public function index(Request $request): View
    {
        $status = $request->string('status')->toString() ?: null;
        $search = $request->string('q')->toString() ?: null;

        $users = User::query()
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($search, fn ($q) => $q->where(fn ($qq) => $qq
                ->where('name', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('national_id', 'like', "%{$search}%")))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'status', 'search'));
    }

    public function show(User $user): View
    {
        $user->load(['enrollments.course', 'orders' => fn ($q) => $q->latest()->take(10)]);

        return view('admin.users.show', [
            'user' => $user,
            'courses' => Course::orderBy('academic_year')->orderBy('position')->get(),
        ]);
    }

    /** الموافقة على الحساب بعد مراجعة صورة البطاقة والبيانات */
    public function approve(Request $request, User $user): RedirectResponse
    {
        $user->update([
            'status' => User::STATUS_APPROVED,
            'approved_at' => now(),
            'approved_by' => $request->user()->id,
            'rejection_reason' => null,
        ]);

        AuditLog::record('user.approve', $user);

        return back()->with('status', 'تم تفعيل حساب: ' . $user->name);
    }

    public function reject(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate(
            ['reason' => ['required', 'string', 'max:500']],
            [],
            ['reason' => 'سبب الرفض'],
        );

        $user->update([
            'status' => User::STATUS_REJECTED,
            'rejection_reason' => $data['reason'],
        ]);

        AuditLog::record('user.reject', $user, ['reason' => $data['reason']]);

        return back()->with('status', 'تم رفض الحساب.');
    }

    public function ban(User $user): RedirectResponse
    {
        abort_if($user->isStaff(), 403);

        $user->update(['status' => User::STATUS_BANNED, 'current_session_id' => null]);

        AuditLog::record('user.ban', $user);

        return back()->with('status', 'تم حظر الحساب وإنهاء جلساته.');
    }

    /** تعديل رصيد المحفظة يدوياً */
    public function adjustWallet(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'not_in:0'],
            'note' => ['nullable', 'string', 'max:255'],
        ], [], ['amount' => 'المبلغ', 'note' => 'ملحوظة']);

        $amount = (float) $data['amount'];

        try {
            $amount > 0
                ? $this->wallet->credit($user, $amount, 'admin_adjust', $data['note'] ?? 'إضافة رصيد إداري')
                : $this->wallet->debit($user, abs($amount), 'admin_adjust', $data['note'] ?? 'خصم رصيد إداري');
        } catch (\RuntimeException $e) {
            return back()->withErrors(['amount' => $e->getMessage()]);
        }

        AuditLog::record('wallet.adjust', $user, ['amount' => $amount]);

        return back()->with('status', 'تم تعديل الرصيد.');
    }

    /** فتح كورس للطالب يدوياً */
    public function enroll(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate(['course_id' => ['required', 'exists:courses,id']]);

        $course = Course::findOrFail($data['course_id']);
        $this->enrollment->enroll($user, $course, 'admin');

        AuditLog::record('enrollment.admin', $user, ['course' => $course->title]);

        return back()->with('status', 'تم فتح الكورس للطالب.');
    }
}
