<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * الكورسات المجانية (السعر = 0) تتخطى شاشة الدفع وتفعّل الاشتراك فوراً.
 */
class FreeCourseEnrollmentTest extends TestCase
{
    use RefreshDatabase;

    protected User $student;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('student', 'web');

        $this->student = User::factory()->create(['academic_year' => 3]);
        $this->student->assignRole('student');
    }

    protected function makeCourse(array $attrs = []): Course
    {
        return Course::create(array_merge([
            'title' => 'كورس مجاني تجريبي',
            'slug' => 'free-course',
            'academic_year' => 3,
            'category' => Course::CATEGORY_MONTHLY,
            'price' => 0,
            'is_published' => true,
        ], $attrs));
    }

    public function test_checkout_page_bypasses_payment_for_free_course(): void
    {
        $course = $this->makeCourse();

        $response = $this->actingAs($this->student)
            ->get(route('student.checkout.course', $course));

        $response->assertRedirect(route('student.learn.course', $course));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('enrollments', [
            'user_id' => $this->student->id,
            'course_id' => $course->id,
            'status' => 'active',
            'source' => 'free',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $this->student->id,
            'action' => 'enrollment.free',
            'target_id' => $course->id,
        ]);
    }

    public function test_free_enroll_route_enrolls_and_opens_course(): void
    {
        $course = $this->makeCourse();

        $this->actingAs($this->student)
            ->post(route('student.checkout.free', $course))
            ->assertRedirect(route('student.learn.course', $course));

        $this->assertTrue($this->student->fresh()->isEnrolledIn($course));

        // الاشتراك النشط يسمح بالوصول لمحتوى الكورس فعلاً
        $this->actingAs($this->student)
            ->get(route('student.learn.course', $course))
            ->assertOk();
    }

    public function test_free_course_with_zero_discount_price_is_treated_as_free(): void
    {
        $course = $this->makeCourse(['price' => 300, 'discount_price' => 0]);

        $this->actingAs($this->student)
            ->get(route('student.checkout.course', $course))
            ->assertRedirect(route('student.learn.course', $course));

        $this->assertTrue($this->student->fresh()->isEnrolledIn($course));
    }

    public function test_checkout_store_grants_free_course_without_order(): void
    {
        $course = $this->makeCourse();

        $this->actingAs($this->student)
            ->post(route('student.checkout.store', ['course', $course->id]), [
                'payment_method' => 'wallet',
            ])
            ->assertRedirect(route('student.learn.course', $course));

        $this->assertDatabaseCount('orders', 0);
        $this->assertTrue($this->student->fresh()->isEnrolledIn($course));
    }

    public function test_paid_course_still_goes_through_checkout(): void
    {
        $course = $this->makeCourse(['slug' => 'paid-course', 'price' => 300]);

        $this->actingAs($this->student)
            ->get(route('student.checkout.course', $course))
            ->assertOk()
            ->assertViewIs('student.checkout');

        $this->assertDatabaseMissing('enrollments', [
            'user_id' => $this->student->id,
            'course_id' => $course->id,
        ]);
    }

    public function test_free_enroll_route_redirects_paid_course_to_checkout(): void
    {
        $course = $this->makeCourse(['slug' => 'paid-course', 'price' => 300]);

        $this->actingAs($this->student)
            ->post(route('student.checkout.free', $course))
            ->assertRedirect(route('student.checkout.course', $course));

        $this->assertDatabaseMissing('enrollments', [
            'user_id' => $this->student->id,
            'course_id' => $course->id,
        ]);
    }

    public function test_already_enrolled_student_goes_straight_to_course(): void
    {
        $course = $this->makeCourse();

        $this->actingAs($this->student)->post(route('student.checkout.free', $course));

        $this->actingAs($this->student)
            ->post(route('student.checkout.free', $course))
            ->assertRedirect(route('student.learn.course', $course));

        $this->assertSame(1, $this->student->enrollments()->count());
    }

    public function test_course_page_shows_free_enroll_button_for_free_course(): void
    {
        $course = $this->makeCourse();

        $this->actingAs($this->student)
            ->get(route('courses.show', $course))
            ->assertOk()
            ->assertSee('اشترك مجاناً')
            ->assertSee(route('student.checkout.free', $course));
    }

    public function test_course_page_keeps_paid_flow_for_paid_course(): void
    {
        $course = $this->makeCourse(['slug' => 'paid-course', 'price' => 300]);

        $this->actingAs($this->student)
            ->get(route('courses.show', $course))
            ->assertOk()
            ->assertSee('اشترك الآن')
            ->assertSee(route('student.checkout.course', $course));
    }
}
