<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lecture;
use App\Models\QaThread;
use App\Models\User;
use App\Support\Rbac;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * سير عمل المدرس في أسئلة الطلاب:
 * سؤال الطالب يدخل قائمة المراجعة ← المدرس يوافق/يرفض ← الإجابة الرسمية (نص + صورة
 * اختيارية) تنشر السؤال وتقفله كمرجع معتمد — وكل خطوة خلف صلاحيتها الحبيبية.
 */
class TeacherQaWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $teacher;

    protected User $student;

    protected Lecture $lecture;

    protected QaThread $thread;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->teacher = $this->makeTeacher();

        $this->student = User::factory()->create(['academic_year' => 3]);
        $this->student->assignRole(Rbac::STUDENT);

        $course = Course::create([
            'title' => 'كورس الفيزياء',
            'slug' => 'physics',
            'academic_year' => 3,
            'category' => 'monthly',
            'price' => 100,
            'is_published' => true,
        ]);

        $this->lecture = Lecture::create([
            'course_id' => $course->id,
            'title' => 'المحاضرة الأولى',
            'position' => 1,
            'is_published' => true,
        ]);

        Enrollment::create([
            'user_id' => $this->student->id,
            'course_id' => $course->id,
            'status' => 'active',
        ]);

        $this->thread = QaThread::create([
            'lecture_id' => $this->lecture->id,
            'user_id' => $this->student->id,
            'body' => 'مش فاهم القانون الأول — ممكن توضيح؟',
            'status' => QaThread::STATUS_PENDING,
        ]);
    }

    /** مدرس بقالبه الافتراضي — أو بصلاحيات مخصصة فردياً */
    protected function makeTeacher(?array $permissions = null): User
    {
        $teacher = User::factory()->create(['academic_year' => null]);
        $teacher->assignRole(Rbac::TEACHER);
        $teacher->syncPermissions($permissions ?? Rbac::defaultsFor(Rbac::TEACHER));

        return $teacher;
    }

    // ------------------------------------------------------------ سير العمل

    public function test_student_question_enters_moderation_queue_as_pending(): void
    {
        $this->actingAs($this->student)
            ->post(route('student.qa.store', $this->lecture), ['body' => 'سؤال جديد عن الدرس؟'])
            ->assertSessionHas('status');

        $this->assertDatabaseHas('qa_threads', [
            'lecture_id' => $this->lecture->id,
            'user_id' => $this->student->id,
            'body' => 'سؤال جديد عن الدرس؟',
            'status' => QaThread::STATUS_PENDING,
        ]);
    }

    public function test_teacher_sees_pending_questions_in_queue(): void
    {
        $this->actingAs($this->teacher)
            ->get('/admin/qa')
            ->assertOk()
            ->assertSee($this->thread->body);
    }

    public function test_teacher_approves_question(): void
    {
        $this->actingAs($this->teacher)
            ->post("/admin/qa/{$this->thread->id}/approve")
            ->assertSessionHas('status');

        $thread = $this->thread->fresh();

        $this->assertSame(QaThread::STATUS_APPROVED, $thread->status);
        $this->assertSame($this->teacher->id, $thread->moderated_by);
        $this->assertFalse($thread->is_locked);

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $this->teacher->id,
            'action' => 'qa.approve',
            'target_id' => $this->thread->id,
        ]);
    }

    public function test_teacher_rejects_question(): void
    {
        $this->actingAs($this->teacher)
            ->post("/admin/qa/{$this->thread->id}/reject")
            ->assertSessionHas('status');

        $thread = $this->thread->fresh();

        $this->assertSame(QaThread::STATUS_REJECTED, $thread->status);
        $this->assertSame($this->teacher->id, $thread->moderated_by);

        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $this->teacher->id,
            'action' => 'qa.reject',
            'target_id' => $this->thread->id,
        ]);
    }

    public function test_official_answer_publishes_and_locks_thread(): void
    {
        $this->actingAs($this->teacher)
            ->post("/admin/qa/{$this->thread->id}/answer", ['body' => 'القانون الأول معناه القصور الذاتي.'])
            ->assertSessionHas('status');

        $thread = $this->thread->fresh();

        $this->assertSame(QaThread::STATUS_APPROVED, $thread->status);
        $this->assertTrue($thread->is_locked);
        $this->assertSame($this->teacher->id, $thread->answered_by);
        $this->assertNotNull($thread->answered_at);

        $this->assertDatabaseHas('qa_replies', [
            'qa_thread_id' => $thread->id,
            'user_id' => $this->teacher->id,
            'body' => 'القانون الأول معناه القصور الذاتي.',
            'is_official_answer' => true,
        ]);
    }

    public function test_official_answer_can_include_image_attachment(): void
    {
        Storage::fake('supabase_public');

        $this->actingAs($this->teacher)
            ->post("/admin/qa/{$this->thread->id}/answer", [
                'body' => 'الشرح في الصورة المرفقة.',
                'image' => UploadedFile::fake()->image('answer.png'),
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status');

        $reply = $this->thread->fresh()->replies()->where('is_official_answer', true)->firstOrFail();

        $this->assertNotNull($reply->image_path);
        $this->assertStringContainsString('qa-images', $reply->image_path);
        $this->assertNotEmpty(Storage::disk('supabase_public')->files('qa-images'));
    }

    public function test_answer_image_must_be_a_valid_image_type(): void
    {
        Storage::fake('supabase_public');

        $this->actingAs($this->teacher)
            ->post("/admin/qa/{$this->thread->id}/answer", [
                'body' => 'إجابة بمرفق غير صالح.',
                'image' => UploadedFile::fake()->create('malware.pdf', 100, 'application/pdf'),
            ])
            ->assertSessionHasErrors(['image']);

        $this->assertSame(0, $this->thread->replies()->count());
        $this->assertEmpty(Storage::disk('supabase_public')->allFiles());
    }

    // ------------------------------------------------------------ حواجز الصلاحيات

    public function test_qa_view_permission_required_for_queue(): void
    {
        // موظف يدخل اللوحة لكن بلا qa.view
        $limited = $this->makeTeacher(['admin.access']);

        $this->actingAs($limited)->get('/admin/qa')->assertForbidden();
    }

    public function test_qa_moderate_permission_required_to_approve_or_reject(): void
    {
        // مدرس سُحبت منه صلاحية المراجعة فردياً — يرى القائمة ويجيب لكن لا يوافق/يرفض
        $answerOnly = $this->makeTeacher(['admin.access', 'qa.view', 'qa.answer']);

        $this->actingAs($answerOnly)->post("/admin/qa/{$this->thread->id}/approve")->assertForbidden();
        $this->actingAs($answerOnly)->post("/admin/qa/{$this->thread->id}/reject")->assertForbidden();

        $this->assertSame(QaThread::STATUS_PENDING, $this->thread->fresh()->status);
    }

    public function test_qa_answer_permission_required_to_answer(): void
    {
        $moderateOnly = $this->makeTeacher(['admin.access', 'qa.view', 'qa.moderate']);

        $this->actingAs($moderateOnly)
            ->post("/admin/qa/{$this->thread->id}/answer", ['body' => 'محاولة إجابة بلا صلاحية.'])
            ->assertForbidden();

        $this->assertSame(0, $this->thread->replies()->count());
    }

    public function test_student_cannot_access_moderation_queue_or_actions(): void
    {
        $this->actingAs($this->student)->get('/admin/qa')->assertForbidden();
        $this->actingAs($this->student)->post("/admin/qa/{$this->thread->id}/approve")->assertForbidden();

        $this->assertSame(QaThread::STATUS_PENDING, $this->thread->fresh()->status);
    }
}
