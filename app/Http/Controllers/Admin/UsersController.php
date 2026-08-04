<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminUpdateStudentRequest;
use App\Models\AuditLog;
use App\Models\Course;
use App\Models\User;
use App\Services\EnrollmentService;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class UsersController extends Controller
{
    public function __construct(
        protected WalletService $wallet,
        protected EnrollmentService $enrollment,
    ) {}

    public function index(Request $request): View
    {
        $status = $request->string('status')->toString() ?: null;
        $search = $request->string('q')->toString() ?: null;
        $role = $request->string('role')->toString() ?: null;

        $users = User::query()
            ->with('roles')
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($role && array_key_exists($role, \App\Support\Rbac::ROLE_LABELS), fn ($q) => $q->role($role))
            ->when($search, fn ($q) => $q->where(fn ($qq) => $qq
                ->where('name', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('national_id', 'like', "%{$search}%")))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'status', 'search', 'role'));
    }

    public function show(Request $request, User $user): View
    {
        $user->load(['roles', 'permissions', 'enrollments.course', 'orders' => fn ($q) => $q->latest()->take(10)]);

        return view('admin.users.show', [
            'user' => $user,
            'courses' => Course::orderBy('academic_year')->orderBy('position')->get(),
            'idPhotoUrl' => $this->idPhotoUrl($request, $user),
        ]);
    }

    /**
     * رابط موقّع مؤقت (10 دقائق) لصورة البطاقة من باكت Supabase الخاص — للإدارة فقط.
     * الملفات القديمة غير المرحّلة تُعرض عبر المسار المحمي القديم.
     */
    protected function idPhotoUrl(Request $request, User $user): ?string
    {
        if (! $user->id_photo_path || ! $request->user()?->can('users.view')) {
            return null;
        }

        return str_starts_with($user->id_photo_path, 'student-ids/')
            ? Storage::disk('supabase_private')->temporaryUrl($user->id_photo_path, now()->addMinutes(10))
            : route('admin.users.idphoto', $user);
    }

    /**
     * عرض صورة البطاقة للإدارة فقط — الملف في باكت Supabase الخاص بلا أي رابط عام،
     * ويُعاد التوجيه لرابط موقّع تنتهي صلاحيته بعد 10 دقائق.
     * يدعم الملفات القديمة على الأقراص المحلية لحين نقلها.
     */
    public function idPhoto(Request $request, User $user): Response
    {
        abort_unless($request->user()?->can('users.view'), 403);
        abort_unless($user->id_photo_path, 404);

        if (str_starts_with($user->id_photo_path, 'student-ids/')) {
            return redirect()->away(
                Storage::disk('supabase_private')->temporaryUrl($user->id_photo_path, now()->addMinutes(10))
            );
        }

        foreach (['local', 'public'] as $disk) {
            if (Storage::disk($disk)->exists($user->id_photo_path)) {
                return Storage::disk($disk)->response($user->id_photo_path);
            }
        }

        abort(404);
    }

    /** تعديل بيانات الطالب الأساسية من لوحة التحكم */
    public function update(AdminUpdateStudentRequest $request, User $user): RedirectResponse
    {
        // تعديل حسابات الأدمن مقصور على السوبر أدمن (المدرس يعدله من يملك users.update)
        abort_if($user->isAdminLevel() && ! $request->user()->isSuperAdmin(), 403);

        $data = $request->validated();

        // تغيير الحالة من هنا يطبق نفس سلوك أزرار الموافقة/الحظر
        if ($data['status'] === User::STATUS_APPROVED && ! $user->isApproved()) {
            $data['approved_at'] = now();
            $data['approved_by'] = $request->user()->id;
            $data['rejection_reason'] = null;
        }

        if ($data['status'] === User::STATUS_BANNED && $user->status !== User::STATUS_BANNED) {
            $data['current_session_id'] = null;
        }

        $user->fill($data);
        $changed = array_keys($user->getDirty());
        $user->save();

        AuditLog::record('user.update', $user, ['fields' => $changed]);

        return back()->with('status', 'تم حفظ بيانات الطالب بنجاح.');
    }

    /** تعيين كلمة مرور جديدة للطالب مباشرة — بدون الحاجة لكلمة المرور القديمة */
    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        // تغيير كلمة مرور حسابات الأدمن مقصور على السوبر أدمن
        abort_if($user->isAdminLevel() && ! $request->user()->isSuperAdmin(), 403);

        $data = $request->validateWithBag('resetPassword', [
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [], ['password' => 'كلمة المرور الجديدة']);

        $user->update([
            'password' => Hash::make($data['password']),
            // إنهاء الجلسة القديمة حتى يدخل الطالب فوراً بكلمة المرور الجديدة
            'current_session_id' => null,
        ]);

        AuditLog::record('user.password_reset', $user);

        return back()->with('status', 'تم تغيير كلمة مرور الطالب — يمكنه الدخول الآن بكلمة المرور الجديدة.');
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

        return back()->with('status', 'تم تفعيل حساب: '.$user->name);
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
