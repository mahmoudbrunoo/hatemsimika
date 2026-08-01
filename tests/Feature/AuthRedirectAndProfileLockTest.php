<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * قواعد التوجيه الذكي حسب حالة الدخول + قفل تعديل الملف الشخصي وإخفاء المستندات الحساسة.
 */
class AuthRedirectAndProfileLockTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['super_admin', 'assistant', 'student'] as $role) {
            Role::findOrCreate($role, 'web');
        }
    }

    protected function student(array $attributes = []): User
    {
        $student = User::factory()->create($attributes);
        $student->assignRole('student');

        return $student;
    }

    // ------------------------------------------------------------ التوجيه الذكي

    public function test_guest_sees_landing_page(): void
    {
        $this->get('/')->assertOk();
    }

    public function test_guest_is_redirected_from_protected_routes_to_login_with_message(): void
    {
        $this->get('/me')
            ->assertRedirect(route('login'))
            ->assertSessionHas('error');

        $this->get('/me/user')->assertRedirect(route('login'));
        $this->get('/me/wallet')->assertRedirect(route('login'));
    }

    public function test_approved_student_is_redirected_from_landing_to_dashboard(): void
    {
        $this->actingAs($this->student())
            ->get('/')
            ->assertRedirect(route('student.dashboard'));
    }

    public function test_pending_student_is_redirected_from_landing_to_pending_page(): void
    {
        $pending = User::factory()->pending()->create();
        $pending->assignRole('student');

        $this->actingAs($pending)->get('/')->assertRedirect(route('account.pending'));
    }

    public function test_staff_is_redirected_from_landing_to_admin_dashboard(): void
    {
        $admin = User::factory()->create(['academic_year' => null]);
        $admin->assignRole('super_admin');

        $this->actingAs($admin)->get('/')->assertRedirect(route('admin.dashboard'));
    }

    public function test_logged_in_student_is_redirected_away_from_login_and_register(): void
    {
        $student = $this->student();

        $this->actingAs($student)->get('/login')->assertRedirect(route('student.dashboard'));
        $this->actingAs($student)->get('/register')->assertRedirect(route('student.dashboard'));
    }

    // ------------------------------------------------------------ قفل الملف الشخصي

    public function test_profile_update_endpoints_are_removed(): void
    {
        $student = $this->student(['email' => 'locked@example.com']);

        // PUT /me/user: المسار GET فقط => 405 / PUT /me/user/password: محذوف تماماً => 404
        $this->actingAs($student)
            ->put('/me/user', ['email' => 'hacker@example.com'])
            ->assertStatus(405);

        $this->actingAs($student)
            ->put('/me/user/password', ['password' => 'new-password'])
            ->assertNotFound();

        $this->assertSame('locked@example.com', $student->fresh()->email);
    }

    public function test_profile_page_is_read_only_and_hides_sensitive_data(): void
    {
        $student = $this->student([
            'national_id' => '31234567890123',
            'id_photo_path' => 'id-photos/secret-card.jpg',
        ]);

        $response = $this->actingAs($student)->get('/me/user');

        $response->assertOk()
            ->assertSee($student->email)
            ->assertSee($student->phone)
            ->assertDontSee('31234567890123')          // الرقم القومي لا يُعرض
            ->assertDontSee('id-photos/secret-card.jpg') // مسار البطاقة لا يظهر في الـ DOM
            ->assertDontSee('type="password"', false);   // لا نماذج تغيير كلمة مرور
    }

    public function test_sensitive_fields_never_serialized(): void
    {
        $user = $this->student([
            'national_id' => '31111111111111',
            'id_photo_path' => 'id-photos/x.jpg',
        ]);

        $serialized = $user->toArray();

        $this->assertArrayNotHasKey('national_id', $serialized);
        $this->assertArrayNotHasKey('id_photo_path', $serialized);
        $this->assertArrayNotHasKey('current_session_id', $serialized);
        $this->assertArrayNotHasKey('password', $serialized);
    }

    public function test_id_photo_route_is_admin_only(): void
    {
        $student = $this->student(['id_photo_path' => 'id-photos/x.jpg']);

        // طالب => ممنوع | زائر => صفحة الدخول
        $this->actingAs($student)
            ->get("/admin/users/{$student->id}/id-photo")
            ->assertForbidden();

        $this->post('/logout'); // تنظيف

        $this->get("/admin/users/{$student->id}/id-photo")->assertRedirect(route('login'));
    }
}
