<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Support\Rbac;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * إدارة دور وصلاحيات مستخدم بعينه (يتطلب صلاحية roles.manage):
 * الدور قالب افتراضي، ومربعات الصلاحيات المباشرة هي المرجع النهائي لكل حساب
 * على حدة — منحاً لصلاحيات إضافية أو سحباً لصلاحيات افتراضية.
 */
class UserRoleController extends Controller
{
    public function update(Request $request, User $user): RedirectResponse
    {
        $editor = $request->user();

        // حساب السوبر أدمن (المالك) محمي نهائياً — لا يعدل دوره أو صلاحياته أي شخص
        abort_if($user->isSuperAdmin(), 403, 'حساب المالك محمي — لا يمكن تعديل دوره أو صلاحياته.');

        // لا أحد يعدل دوره أو صلاحياته بنفسه
        abort_if($user->is($editor), 403, 'لا يمكنك تعديل دورك أو صلاحياتك بنفسك.');

        // حسابات الأدمن لا يعدلها إلا السوبر أدمن
        abort_if($user->hasRole(Rbac::ADMIN) && ! $editor->isSuperAdmin(), 403, 'تعديل حسابات الأدمن مقصور على السوبر أدمن.');

        $data = $request->validateWithBag('editRoles', [
            'role' => ['required', Rule::in(array_keys(Rbac::ASSIGNABLE_ROLES))],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in(Rbac::all())],
        ], [], ['role' => 'الدور', 'permissions' => 'الصلاحيات']);

        // منح دور الأدمن مقصور على السوبر أدمن
        abort_if($data['role'] === Rbac::ADMIN && ! $editor->isSuperAdmin(), 403, 'منح دور الأدمن مقصور على السوبر أدمن.');

        $previousRole = $user->roleName();
        $previousPermissions = $user->permissions->pluck('name')->sort()->values()->all();
        $permissions = array_values(array_unique($data['permissions'] ?? []));

        $user->syncRoles([$data['role']]);
        $user->syncPermissions($permissions);

        AuditLog::record('user.roles_update', $user, [
            'role_from' => $previousRole,
            'role_to' => $data['role'],
            'permissions_from' => $previousPermissions,
            'permissions_to' => $permissions,
        ]);

        return back()->with('status', 'تم حفظ الدور والصلاحيات بنجاح.');
    }
}
