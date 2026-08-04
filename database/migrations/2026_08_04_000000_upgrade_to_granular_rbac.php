<?php

use App\Support\Rbac;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

/**
 * الترقية لنظام الصلاحيات الحبيبية:
 * 1) إعادة تسمية دور assistant إلى admin (تعيينات المستخدمين تبقى كما هي)
 * 2) إنشاء دور teacher وكل صلاحيات كتالوج Rbac
 * 3) نسخ القالب الافتراضي لكل أدمن حالي كصلاحيات مباشرة قابلة للتخصيص فردياً
 * 4) حذف عمود users.role القديم — أُضيف في 2026_08_01 ولم يقرأه أي كود قط،
 *    والمرجع الوحيد للأدوار هو جداول spatie
 */
return new class extends Migration
{
    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // ------------------------------------------------------------ assistant => admin
        $assistant = DB::table('roles')->where('name', 'assistant')->where('guard_name', 'web')->first();
        $admin = DB::table('roles')->where('name', 'admin')->where('guard_name', 'web')->first();

        if ($assistant !== null && $admin === null) {
            DB::table('roles')->where('id', $assistant->id)->update(['name' => Rbac::ADMIN]);
        } elseif ($assistant !== null && $admin !== null) {
            // الدوران موجودان معاً: انقل التعيينات للدور الجديد ثم احذف القديم
            $alreadyAssigned = DB::table('model_has_roles')->where('role_id', $admin->id)->pluck('model_id');

            DB::table('model_has_roles')
                ->where('role_id', $assistant->id)
                ->whereNotIn('model_id', $alreadyAssigned)
                ->update(['role_id' => $admin->id]);

            DB::table('model_has_roles')->where('role_id', $assistant->id)->delete();
            DB::table('roles')->where('id', $assistant->id)->delete();
        }

        // ------------------------------------------------------------ الأدوار والصلاحيات
        $now = now();

        DB::table('roles')->insertOrIgnore(
            collect(array_keys(Rbac::ROLE_LABELS))
                ->map(fn (string $role) => ['name' => $role, 'guard_name' => 'web', 'created_at' => $now, 'updated_at' => $now])
                ->all()
        );

        DB::table('permissions')->insertOrIgnore(
            collect(Rbac::all())
                ->map(fn (string $permission) => ['name' => $permission, 'guard_name' => 'web', 'created_at' => $now, 'updated_at' => $now])
                ->all()
        );

        // ------------------------------------------------------------ صلاحيات الأدمنز الحاليين
        // كل من كان أدمن (assistant سابقاً) يأخذ القالب الافتراضي كصلاحيات مباشرة —
        // السوبر أدمن لا يحتاج أي صلاحيات (يتجاوز كل الفحوص عبر Gate::before)
        $adminRoleId = DB::table('roles')->where('name', Rbac::ADMIN)->where('guard_name', 'web')->value('id');

        $permissionIds = DB::table('permissions')
            ->where('guard_name', 'web')
            ->whereIn('name', Rbac::defaultsFor(Rbac::ADMIN))
            ->pluck('id');

        $adminUserIds = DB::table('model_has_roles')
            ->where('role_id', $adminRoleId)
            ->where('model_type', 'App\\Models\\User')
            ->pluck('model_id');

        $rows = [];

        foreach ($adminUserIds as $userId) {
            foreach ($permissionIds as $permissionId) {
                $rows[] = [
                    'permission_id' => $permissionId,
                    'model_type' => 'App\\Models\\User',
                    'model_id' => $userId,
                ];
            }
        }

        if ($rows !== []) {
            DB::table('model_has_permissions')->insertOrIgnore($rows);
        }

        // ------------------------------------------------------------ حذف العمود الميت
        if (Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('role');
            });
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        if (! Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->enum('role', ['student', 'teacher', 'admin'])->default('student')->after('status');
            });
        }

        // حذف صلاحيات الكتالوج يمسح تعييناتها المباشرة تلقائياً (cascade)
        DB::table('permissions')->where('guard_name', 'web')->whereIn('name', Rbac::all())->delete();

        DB::table('roles')->where('name', Rbac::ADMIN)->where('guard_name', 'web')->update(['name' => 'assistant']);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
