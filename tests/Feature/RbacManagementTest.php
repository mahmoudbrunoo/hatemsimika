<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Rbac;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * نظام الأدوار والصلاحيات الحبيبية:
 * - السوبر أدمن يتجاوز كل الفحوص ولا يمكن تعديل حسابه
 * - الدور قالب افتراضي والصلاحيات المباشرة قابلة للتخصيص لكل حساب (منحاً وسحباً)
 * - إدارة الأدوار تتطلب roles.manage وتخضع لقواعد حماية صارمة
 */
class RbacManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    /** موظف بدور مع نسخ قالبه الافتراضي كصلاحيات مباشرة — نفس سلوك لوحة التحكم */
    protected function staff(string $role, ?array $permissions = null): User
    {
        $user = User::factory()->create(['academic_year' => null]);
        $user->assignRole($role);
        $user->syncPermissions($permissions ?? Rbac::defaultsFor($role));

        return $user;
    }

    protected function superAdmin(): User
    {
        // بلا أي صلاحيات مباشرة عمداً — يعتمد كلياً على تجاوز Gate::before
        $user = User::factory()->create(['academic_year' => null]);
        $user->assignRole(Rbac::SUPER_ADMIN);

        return $user;
    }

    protected function student(): User
    {
        $user = User::factory()->create(['academic_year' => 3]);
        $user->assignRole(Rbac::STUDENT);

        return $user;
    }

    // ------------------------------------------------------------ مصفوفة الوصول

    public function test_super_admin_bypasses_all_permission_checks_without_direct_permissions(): void
    {
        $owner = $this->superAdmin();

        $this->assertSame(0, $owner->permissions()->count());

        foreach (['/admin', '/admin/users', '/admin/qa', '/admin/settings', '/admin/audit'] as $url) {
            $this->actingAs($owner)->get($url)->assertOk();
        }
    }

    public function test_admin_default_template_grants_modules_except_impersonation(): void
    {
        $admin = $this->staff(Rbac::ADMIN);
        $student = $this->student();

        $this->actingAs($admin)->get('/admin/users')->assertOk();
        $this->actingAs($admin)->get('/admin/settings')->assertOk();

        // الانتحال خارج القالب الافتراضي للأدمن — يُمنح فردياً عند الحاجة فقط
        $this->actingAs($admin)
            ->post("/admin/users/{$student->id}/impersonate")
            ->assertForbidden();
    }

    public function test_teacher_default_template_limits_access_to_qa_module(): void
    {
        $teacher = $this->staff(Rbac::TEACHER);

        $this->actingAs($teacher)->get('/admin/qa')->assertOk();

        foreach (['/admin', '/admin/users', '/admin/courses', '/admin/exams', '/admin/settings'] as $url) {
            $this->actingAs($teacher)->get($url)->assertForbidden();
        }
    }

    public function test_student_cannot_access_admin_panel(): void
    {
        $student = $this->student();

        $this->actingAs($student)->get('/admin')->assertForbidden();
        $this->actingAs($student)->get('/admin/qa')->assertForbidden();
    }

    // ------------------------------------------------------------ التخصيص الفردي

    public function test_per_user_override_grants_extra_module_to_teacher(): void
    {
        $teacher = $this->staff(Rbac::TEACHER, [...Rbac::defaultsFor(Rbac::TEACHER), 'exams.manage']);

        $this->actingAs($teacher)->get('/admin/exams')->assertOk();
    }

    public function test_per_user_override_revokes_default_permission_from_admin(): void
    {
        $admin = $this->staff(
            Rbac::ADMIN,
            array_values(array_diff(Rbac::defaultsFor(Rbac::ADMIN), ['settings.manage'])),
        );

        $this->actingAs($admin)->get('/admin/settings')->assertForbidden();
        $this->actingAs($admin)->get('/admin/users')->assertOk();
    }

    // ------------------------------------------------------------ إدارة الأدوار

    public function test_updating_roles_requires_roles_manage_permission(): void
    {
        $teacher = $this->staff(Rbac::TEACHER); // القالب الافتراضي بلا roles.manage
        $student = $this->student();

        $this->actingAs($teacher)
            ->put("/admin/users/{$student->id}/roles", ['role' => Rbac::TEACHER])
            ->assertForbidden();

        $this->assertTrue($student->fresh()->hasRole(Rbac::STUDENT));
    }

    public function test_super_admin_account_cannot_be_modified_by_anyone(): void
    {
        $owner = $this->superAdmin();
        $admin = $this->staff(Rbac::ADMIN);

        $this->actingAs($admin)
            ->put("/admin/users/{$owner->id}/roles", ['role' => Rbac::STUDENT])
            ->assertForbidden();

        $this->assertTrue($owner->fresh()->isSuperAdmin());
    }

    public function test_no_one_can_edit_own_role_or_permissions(): void
    {
        $admin = $this->staff(Rbac::ADMIN);

        $this->actingAs($admin)
            ->put("/admin/users/{$admin->id}/roles", ['role' => Rbac::ADMIN, 'permissions' => Rbac::all()])
            ->assertForbidden();
    }

    public function test_only_super_admin_can_modify_admin_accounts(): void
    {
        $adminA = $this->staff(Rbac::ADMIN);
        $adminB = $this->staff(Rbac::ADMIN);

        $this->actingAs($adminA)
            ->put("/admin/users/{$adminB->id}/roles", ['role' => Rbac::TEACHER])
            ->assertForbidden();

        $this->assertTrue($adminB->fresh()->hasRole(Rbac::ADMIN));

        $this->actingAs($this->superAdmin())
            ->put("/admin/users/{$adminB->id}/roles", [
                'role' => Rbac::TEACHER,
                'permissions' => Rbac::defaultsFor(Rbac::TEACHER),
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertTrue($adminB->fresh()->hasRole(Rbac::TEACHER));
    }

    public function test_only_super_admin_can_grant_admin_role(): void
    {
        $admin = $this->staff(Rbac::ADMIN);
        $student = $this->student();

        $this->actingAs($admin)
            ->put("/admin/users/{$student->id}/roles", ['role' => Rbac::ADMIN])
            ->assertForbidden();

        $this->assertTrue($student->fresh()->hasRole(Rbac::STUDENT));
    }

    public function test_super_admin_role_is_never_assignable(): void
    {
        $student = $this->student();

        $this->actingAs($this->superAdmin())
            ->put("/admin/users/{$student->id}/roles", ['role' => Rbac::SUPER_ADMIN])
            ->assertSessionHasErrorsIn('editRoles', ['role']);

        $this->assertTrue($student->fresh()->hasRole(Rbac::STUDENT));
    }

    public function test_invalid_permission_names_are_rejected(): void
    {
        $student = $this->student();

        $this->actingAs($this->superAdmin())
            ->put("/admin/users/{$student->id}/roles", [
                'role' => Rbac::TEACHER,
                'permissions' => ['qa.view', 'not.a.permission'],
            ])
            ->assertSessionHasErrorsIn('editRoles', ['permissions.1']);

        $this->assertTrue($student->fresh()->hasRole(Rbac::STUDENT));
    }

    public function test_role_update_syncs_role_and_direct_permissions_and_logs_audit(): void
    {
        $owner = $this->superAdmin();
        $student = $this->student();

        // القالب الافتراضي للمدرس + صلاحية إضافية مخصصة فردياً
        $permissions = [...Rbac::defaultsFor(Rbac::TEACHER), 'exams.manage'];

        $this->actingAs($owner)
            ->put("/admin/users/{$student->id}/roles", [
                'role' => Rbac::TEACHER,
                'permissions' => $permissions,
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $student = $student->fresh();

        $this->assertTrue($student->hasRole(Rbac::TEACHER));
        $this->assertFalse($student->hasRole(Rbac::STUDENT));
        $this->assertEqualsCanonicalizing($permissions, $student->permissions->pluck('name')->all());

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $owner->id,
            'action' => 'user.roles_update',
            'target_type' => User::class,
            'target_id' => $student->id,
        ]);
    }
}
