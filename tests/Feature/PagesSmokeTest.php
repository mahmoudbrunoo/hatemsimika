<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Attachment;
use App\Models\AttemptAnswer;
use App\Models\AuditLog;
use App\Models\Book;
use App\Models\CenterCode;
use App\Models\Coupon;
use App\Models\Course;
use App\Models\DailyActivity;
use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Faq;
use App\Models\Lecture;
use App\Models\LectureProgress;
use App\Models\LoginActivity;
use App\Models\Mistake;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\QaReply;
use App\Models\QaThread;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\User;
use App\Models\Video;
use App\Models\VideoView;
use App\Models\WalletTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * اختبار دخاني: كل صفحات GET (زائر/طالب/أدمن) لازم ترجع 200 بدون أخطاء عرض.
 */
class PagesSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $student;

    protected Course $course;

    protected Lecture $lecture;

    protected Video $video;

    protected Assignment $assignment;

    protected Exam $exam;

    protected Question $question;

    protected Book $book;

    protected Course $otherCourse;

    protected ExamAttempt $submittedAttempt;

    protected ExamAttempt $openAttempt;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['super_admin', 'assistant', 'student'] as $role) {
            Role::findOrCreate($role, 'web');
        }

        $this->admin = User::factory()->create(['academic_year' => null]);
        $this->admin->assignRole('super_admin');

        $this->student = User::factory()->create(['academic_year' => 3, 'balance' => 150]);
        $this->student->assignRole('student');

        $this->course = Course::create([
            'title' => 'كورس الفيزياء — شهر يوليو',
            'slug' => 'physics-july',
            'description' => 'شرح شامل',
            'academic_year' => 3,
            'category' => 'monthly',
            'price' => 300,
            'discount_price' => 200,
            'is_featured' => true,
            'is_published' => true,
        ]);

        $this->otherCourse = Course::create([
            'title' => 'كورس الكيمياء — باقة 3 شهور',
            'slug' => 'chemistry-quarter',
            'academic_year' => 3,
            'category' => 'three_months',
            'price' => 450,
            'discount_price' => 300,
            'is_published' => true,
        ]);

        $this->lecture = Lecture::create([
            'course_id' => $this->course->id,
            'title' => 'المحاضرة الأولى',
            'description' => 'مقدمة',
            'position' => 1,
            'is_published' => true,
        ]);

        $this->video = Video::create([
            'lecture_id' => $this->lecture->id,
            'title' => 'فيديو الشرح',
            'description' => 'شرح الدرس الأول',
            'takeaways' => 'أهم النقاط',
            'source' => 'youtube',
            'url' => 'dQw4w9WgXcQ',
            'duration_seconds' => 600,
            'position' => 1,
        ]);

        Attachment::create([
            'lecture_id' => $this->lecture->id,
            'title' => 'مذكرة المحاضرة',
            'file_path' => 'attachments/demo.pdf',
        ]);

        $this->assignment = Assignment::create([
            'lecture_id' => $this->lecture->id,
            'title' => 'واجب المحاضرة الأولى',
            'description' => 'حل المسائل',
            'max_score' => 10,
        ]);

        $this->exam = Exam::create([
            'lecture_id' => $this->lecture->id,
            'title' => 'كويز المحاضرة الأولى',
            'type' => 'quiz',
            'duration_minutes' => 30,
            'passing_percent' => 60,
            'hints_enabled' => true,
            'max_attempts' => 5,
            'is_published' => true,
        ]);

        $this->question = Question::create([
            'exam_id' => $this->exam->id,
            'type' => 'mcq',
            'body' => 'ما هي وحدة قياس القوة؟',
            'hint' => 'فكر في اسم عالم',
            'explanation' => 'القوة تقاس بالنيوتن',
            'points' => 1,
            'position' => 1,
        ]);

        $correct = QuestionOption::create([
            'question_id' => $this->question->id,
            'body' => 'نيوتن',
            'is_correct' => true,
            'position' => 1,
        ]);

        QuestionOption::create([
            'question_id' => $this->question->id,
            'body' => 'جول',
            'is_correct' => false,
            'position' => 2,
        ]);

        $essay = Question::create([
            'exam_id' => $this->exam->id,
            'type' => 'essay',
            'body' => 'اشرح القانون الأول لنيوتن.',
            'points' => 5,
            'position' => 2,
        ]);

        Enrollment::create([
            'user_id' => $this->student->id,
            'course_id' => $this->course->id,
            'status' => 'active',
        ]);

        AssignmentSubmission::create([
            'assignment_id' => $this->assignment->id,
            'user_id' => $this->student->id,
            'answer_text' => 'الحل بتاعي',
            'status' => 'submitted',
        ]);

        LectureProgress::create([
            'user_id' => $this->student->id,
            'lecture_id' => $this->lecture->id,
            'homework_submitted' => true,
        ]);

        $this->submittedAttempt = ExamAttempt::create([
            'exam_id' => $this->exam->id,
            'user_id' => $this->student->id,
            'started_at' => now()->subMinutes(20),
            'submitted_at' => now()->subMinutes(5),
            'score' => 1,
            'total' => 6,
            'percent' => 16.67,
            'passed' => false,
        ]);

        AttemptAnswer::create([
            'exam_attempt_id' => $this->submittedAttempt->id,
            'question_id' => $this->question->id,
            'question_option_id' => $correct->id,
            'is_correct' => true,
            'points_awarded' => 1,
        ]);

        AttemptAnswer::create([
            'exam_attempt_id' => $this->submittedAttempt->id,
            'question_id' => $essay->id,
            'essay_text' => 'إجابة مقالية للتصحيح',
            'is_correct' => null,
            'points_awarded' => 0,
        ]);

        $this->openAttempt = ExamAttempt::create([
            'exam_id' => $this->exam->id,
            'user_id' => $this->student->id,
            'started_at' => now(),
        ]);

        Mistake::create([
            'user_id' => $this->student->id,
            'question_id' => $this->question->id,
            'times_wrong' => 2,
            'last_wrong_at' => now(),
        ]);

        VideoView::create([
            'user_id' => $this->student->id,
            'video_id' => $this->video->id,
            'seconds_watched' => 300,
            'last_position' => 300,
        ]);

        DailyActivity::create([
            'user_id' => $this->student->id,
            'activity_date' => now()->toDateString(),
            'videos_watched' => 2,
            'quizzes_completed' => 1,
            'seconds_spent' => 3600,
        ]);

        LoginActivity::create([
            'user_id' => $this->student->id,
            'session_id' => 'test-session',
            'device_type' => 'Desktop',
            'browser' => 'Chrome',
            'ip' => '127.0.0.1',
            'logged_in_at' => now(),
            'last_activity_at' => now(),
        ]);

        WalletTransaction::create([
            'user_id' => $this->student->id,
            'amount' => 150,
            'balance_before' => 0,
            'balance_after' => 150,
            'type' => 'charge',
            'note' => 'شحن كود سنتر',
        ]);

        $this->book = Book::create([
            'title' => 'كتاب المراجعة النهائية',
            'slug' => 'final-revision',
            'description' => 'ملخص المنهج',
            'price' => 120,
            'discount_price' => 90,
            'academic_year' => 3,
            'is_published' => true,
        ]);

        $order = Order::create([
            'number' => 'ORD-1001',
            'user_id' => $this->student->id,
            'subtotal' => 200,
            'total' => 200,
            'payment_method' => 'manual_vodafone',
            'status' => 'PENDING',
            'sender_phone' => '01012345678',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'purchasable_type' => Course::class,
            'purchasable_id' => $this->course->id,
            'title' => $this->course->title,
            'price' => 200,
        ]);

        $thread = QaThread::create([
            'lecture_id' => $this->lecture->id,
            'user_id' => $this->student->id,
            'body' => 'مش فاهم الجزء الأخير؟',
            'status' => 'APPROVED',
            'is_locked' => true,
            'answered_by' => $this->admin->id,
            'answered_at' => now(),
        ]);

        QaReply::create([
            'qa_thread_id' => $thread->id,
            'user_id' => $this->admin->id,
            'body' => 'اتفرج على الفيديو من الدقيقة 5.',
            'is_official_answer' => true,
        ]);

        Coupon::create(['code' => 'SAVE20', 'type' => 'percent', 'value' => 20]);

        CenterCode::create(['code' => 'CTR-0001', 'value' => 100]);

        Faq::create(['question' => 'إزاي أشترك؟', 'answer' => 'من صفحة الكورس.', 'is_active' => true]);

        AuditLog::create([
            'actor_id' => $this->admin->id,
            'action' => 'user.approve',
            'target_type' => User::class,
            'target_id' => $this->student->id,
            'ip' => '127.0.0.1',
        ]);
    }

    public function test_guest_pages_render(): void
    {
        foreach ([
            '/',
            '/courses',
            '/courses/' . $this->course->slug,
            '/books',
            '/books/' . $this->book->slug,
            '/help',
            '/login',
            '/register',
        ] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_student_pages_render(): void
    {
        $urls = [
            '/me',
            '/me?all=1',
            '/me/user',
            '/me/wallet',
            '/me/charge-code',
            '/me/link-center',
            '/me/courses',
            '/me/subscriptions',
            '/me/invoices',
            '/me/login-data',
            '/me/watch-details',
            '/me/exam-results',
            '/me/evaluation-results',
            '/me/hm-results',
            '/me/personal-exam',
            '/me/question-bank',
            '/me/learn/' . $this->course->slug,
            '/me/learn/' . $this->course->slug . '/lectures/' . $this->lecture->id,
            '/me/learn/' . $this->course->slug . '/videos/' . $this->video->id,
            '/me/homework/' . $this->assignment->id,
            '/me/exams/' . $this->exam->id,
            '/me/attempts/' . $this->openAttempt->id,
            '/me/attempts/' . $this->submittedAttempt->id . '/result',
            '/me/checkout/' . $this->otherCourse->slug,
            '/me/checkout/book/' . $this->book->slug,
        ];

        foreach ($urls as $url) {
            $response = $this->actingAs($this->student)->get($url);
            $this->assertSame(200, $response->status(), "فشل عرض الصفحة: {$url} (status {$response->status()})");
        }
    }

    public function test_admin_pages_render(): void
    {
        $urls = [
            '/admin',
            '/admin/users',
            '/admin/users/' . $this->student->id,
            '/admin/courses',
            '/admin/courses/create',
            '/admin/courses/' . $this->course->id . '/edit',
            '/admin/courses/' . $this->course->id . '/lectures',
            '/admin/lectures/' . $this->lecture->id,
            '/admin/exams',
            '/admin/exams/create',
            '/admin/exams/' . $this->exam->id . '/edit',
            '/admin/exams/' . $this->exam->id . '/questions',
            '/admin/questions/' . $this->question->id,
            '/admin/grading',
            '/admin/orders',
            '/admin/coupons',
            '/admin/books',
            '/admin/books/create',
            '/admin/books/' . $this->book->id . '/edit',
            '/admin/center-codes',
            '/admin/qa',
            '/admin/settings',
            '/admin/faqs',
            '/admin/audit',
        ];

        foreach ($urls as $url) {
            $response = $this->actingAs($this->admin)->get($url);
            $this->assertSame(200, $response->status(), "فشل عرض الصفحة: {$url} (status {$response->status()})");
        }
    }

    public function test_pending_student_is_blocked_from_dashboard(): void
    {
        $pending = User::factory()->pending()->create();
        $pending->assignRole('student');

        $this->actingAs($pending)->get('/me')->assertRedirect('/account/pending');
        $this->actingAs($pending)->get('/account/pending')->assertOk();
    }
}
