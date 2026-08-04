<?php

namespace Database\Seeders;

use App\Support\Rbac;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * الأدوار الأربعة + كتالوج الصلاحيات الحبيبية — آمن لإعادة التشغيل.
 *
 * ملحوظة: لا نربط صلاحيات بالأدوار في قاعدة البيانات عمداً؛ الدور قالب افتراضي
 * فقط (Rbac::defaults) يُنسخ للمستخدم كصلاحيات مباشرة عند تعيين الدور، ثم تُخصص
 * الصلاحيات لكل حساب على حدة من ملف المستخدم — منحاً أو سحباً.
 */
class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (array_keys(Rbac::ROLE_LABELS) as $role) {
            Role::findOrCreate($role, 'web');
        }

        foreach (Rbac::all() as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
