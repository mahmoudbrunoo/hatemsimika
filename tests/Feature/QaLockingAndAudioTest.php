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
 * قفل مواضيع أسئلة الدروس + الرسائل الصوتية:
 * القفل اليدوي بيد أصحاب qa.moderate — ويمنع تعليقات الطلاب مع استثناء
 * الأدمن والمدرسين وصاحب السؤال. والسؤال/الرد ممكن يكون رسالة صوتية بلا نص.
 */
class QaLockingAndAudioTest extends TestCase
{
    use RefreshDatabase;

    protected User $teacher;

    protected User $owner;

    protected User $classmate;

    protected Lecture $lecture;

    protected QaThread $thread;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->teacher = $this->makeTeacher();

        $course = Course::create([
            'title' => 'كورس الكيمياء',
            'slug' => 'chemistry',
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

        $this->owner = $this->makeStudent($course);
        $this->classmate = $this->makeStudent($course);

        $this->thread = QaThread::create([
            'lecture_id' => $this->lecture->id,
            'user_id' => $this->owner->id,
            'body' => 'مش فاهم الرابطة التساهمية — ممكن توضيح؟',
            'status' => QaThread::STATUS_APPROVED,
        ]);
    }

    protected function makeTeacher(?array $permissions = null): User
    {
        $teacher = User::factory()->create(['academic_year' => null]);
        $teacher->assignRole(Rbac::TEACHER);
        $teacher->syncPermissions($permissions ?? Rbac::defaultsFor(Rbac::TEACHER));

        return $teacher;
    }

    protected function makeStudent(?Course $course = null): User
    {
        $student = User::factory()->create(['academic_year' => 3]);
        $student->assignRole(Rbac::STUDENT);

        if ($course) {
            Enrollment::create([
                'user_id' => $student->id,
                'course_id' => $course->id,
                'status' => 'active',
            ]);
        }

        return $student;
    }

    // ------------------------------------------------------------ القفل والفتح اليدوي

    public function test_moderator_can_lock_and_unlock_thread(): void
    {
        $this->actingAs($this->teacher)
            ->post("/admin/qa/{$this->thread->id}/toggle-lock")
            ->assertSessionHas('status');

        $this->assertTrue($this->thread->fresh()->is_locked);
        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $this->teacher->id,
            'action' => 'qa.lock',
            'target_id' => $this->thread->id,
        ]);

        $this->actingAs($this->teacher)
            ->post("/admin/qa/{$this->thread->id}/toggle-lock")
            ->assertSessionHas('status');

        $this->assertFalse($this->thread->fresh()->is_locked);
        $this->assertDatabaseHas('audit_logs', [
            'actor_id' => $this->teacher->id,
            'action' => 'qa.unlock',
            'target_id' => $this->thread->id,
        ]);
    }

    public function test_toggle_lock_requires_moderate_permission(): void
    {
        $answerOnly = $this->makeTeacher(['admin.access', 'qa.view', 'qa.answer']);

        $this->actingAs($answerOnly)->post("/admin/qa/{$this->thread->id}/toggle-lock")->assertForbidden();
        $this->actingAs($this->owner)->post("/admin/qa/{$this->thread->id}/toggle-lock")->assertForbidden();

        $this->assertFalse($this->thread->fresh()->is_locked);
    }

    // ------------------------------------------------------------ صلاحيات التعليق

    public function test_enrolled_student_can_reply_to_open_thread(): void
    {
        $this->actingAs($this->classmate)
            ->post(route('student.qa.reply', $this->thread), ['body' => 'أنا كمان عندي نفس السؤال بالظبط.'])
            ->assertSessionHas('status');

        $this->assertDatabaseHas('qa_replies', [
            'qa_thread_id' => $this->thread->id,
            'user_id' => $this->classmate->id,
            'is_official_answer' => false,
        ]);
    }

    public function test_student_cannot_reply_to_locked_thread(): void
    {
        $this->thread->update(['is_locked' => true]);

        $this->actingAs($this->classmate)
            ->post(route('student.qa.reply', $this->thread), ['body' => 'محاولة تعليق على موضوع مقفول.'])
            ->assertForbidden();

        $this->assertSame(0, $this->thread->replies()->count());
    }

    public function test_thread_owner_can_reply_to_locked_thread(): void
    {
        $this->thread->update(['is_locked' => true]);

        $this->actingAs($this->owner)
            ->post(route('student.qa.reply', $this->thread), ['body' => 'متابعة من صاحب السؤال بعد القفل.'])
            ->assertSessionHas('status');

        $this->assertDatabaseHas('qa_replies', [
            'qa_thread_id' => $this->thread->id,
            'user_id' => $this->owner->id,
        ]);
    }

    public function test_teacher_can_answer_locked_thread(): void
    {
        $this->thread->update(['is_locked' => true]);

        $this->actingAs($this->teacher)
            ->post("/admin/qa/{$this->thread->id}/answer", ['body' => 'رد المدرس متاح حتى مع القفل.'])
            ->assertSessionHas('status');

        $this->assertDatabaseHas('qa_replies', [
            'qa_thread_id' => $this->thread->id,
            'user_id' => $this->teacher->id,
            'is_official_answer' => true,
        ]);
    }

    public function test_additional_answer_keeps_original_answerer(): void
    {
        $this->actingAs($this->teacher)
            ->post("/admin/qa/{$this->thread->id}/answer", ['body' => 'الإجابة الأولى المعتمدة.']);

        $second = $this->makeTeacher();

        $this->actingAs($second)
            ->post("/admin/qa/{$this->thread->id}/answer", ['body' => 'رد إضافي من مدرس آخر.']);

        $thread = $this->thread->fresh();

        $this->assertSame($this->teacher->id, $thread->answered_by);
        $this->assertTrue($thread->is_locked);
        $this->assertSame(2, $thread->replies()->count());
    }

    public function test_unenrolled_student_cannot_reply(): void
    {
        $outsider = $this->makeStudent();

        $this->actingAs($outsider)
            ->post(route('student.qa.reply', $this->thread), ['body' => 'طالب من برة الكورس بيحاول يعلق.'])
            ->assertForbidden();
    }

    public function test_no_replies_allowed_on_rejected_thread(): void
    {
        $this->thread->update(['status' => QaThread::STATUS_REJECTED]);

        $this->actingAs($this->owner)
            ->post(route('student.qa.reply', $this->thread), ['body' => 'محاولة رد على سؤال مرفوض.'])
            ->assertForbidden();
    }

    // ------------------------------------------------------------ الرسائل الصوتية

    public function test_student_can_ask_question_with_voice_note_only(): void
    {
        Storage::fake('supabase_public');

        $this->actingAs($this->owner)
            ->post(route('student.qa.store', $this->lecture), [
                'audio' => UploadedFile::fake()->create('voice-note.webm', 200, 'audio/webm'),
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status');

        $thread = QaThread::latest('id')->first();

        $this->assertNull($thread->body);
        $this->assertStringContainsString('qa-audio', $thread->audio_path);
        $this->assertNotEmpty(Storage::disk('supabase_public')->files('qa-audio'));
    }

    public function test_question_requires_body_or_audio(): void
    {
        $this->actingAs($this->owner)
            ->post(route('student.qa.store', $this->lecture), [])
            ->assertSessionHasErrors(['body']);
    }

    public function test_reply_can_include_voice_note(): void
    {
        Storage::fake('supabase_public');

        $this->actingAs($this->classmate)
            ->post(route('student.qa.reply', $this->thread), [
                'audio' => UploadedFile::fake()->create('voice-note.m4a', 200, 'audio/mp4'),
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status');

        $reply = $this->thread->replies()->firstOrFail();

        $this->assertNull($reply->body);
        $this->assertStringContainsString('qa-audio', $reply->audio_path);
    }

    public function test_audio_must_be_an_audio_file(): void
    {
        Storage::fake('supabase_public');

        $this->actingAs($this->owner)
            ->post(route('student.qa.store', $this->lecture), [
                'audio' => UploadedFile::fake()->create('not-audio.pdf', 100, 'application/pdf'),
            ])
            ->assertSessionHasErrors(['audio']);

        $this->assertEmpty(Storage::disk('supabase_public')->allFiles());
    }

    public function test_official_answer_can_be_voice_note_only(): void
    {
        Storage::fake('supabase_public');

        $this->actingAs($this->teacher)
            ->post("/admin/qa/{$this->thread->id}/answer", [
                'audio' => UploadedFile::fake()->create('answer.mp3', 300, 'audio/mpeg'),
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status');

        $reply = $this->thread->replies()->where('is_official_answer', true)->firstOrFail();

        $this->assertNull($reply->body);
        $this->assertStringContainsString('qa-audio', $reply->audio_path);
        $this->assertTrue($this->thread->fresh()->is_locked);
    }
}
