<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

/**
 * دخول الأدمن على حساب طالب لحل المشاكل التقنية —
 * كل عملية انتحال تسجل في audit_logs ببداية ونهاية الجلسة.
 */
class ImpersonationController extends Controller
{
    public function start(User $user): RedirectResponse
    {
        $admin = Auth::user();

        abort_unless($admin->hasRole('super_admin'), 403);
        abort_if($user->isStaff(), 403, 'لا يمكن انتحال حساب مسؤول آخر');

        AuditLog::record('impersonate.start', $user, [
            'admin' => $admin->name,
            'student' => $user->name,
        ]);

        session()->put('impersonated_by', $admin->id);
        Auth::login($user);

        return redirect()->route('student.dashboard')
            ->with('status', 'أنت الآن داخل حساب الطالب: ' . $user->name);
    }

    public function stop(): RedirectResponse
    {
        $adminId = session()->pull('impersonated_by');

        abort_if($adminId === null, 403);

        $student = Auth::user();
        $admin = User::findOrFail($adminId);

        AuditLog::record('impersonate.stop', $student, [
            'admin' => $admin->name,
        ], $admin->id);

        Auth::login($admin);
        session()->regenerate();

        return redirect()->route('admin.users.show', $student)
            ->with('status', 'تم الرجوع لحساب الأدمن.');
    }
}
