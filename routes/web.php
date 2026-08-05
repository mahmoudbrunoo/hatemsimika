<?php

use App\Http\Controllers\Admin\AuditLogsController;
use App\Http\Controllers\Admin\BooksController;
use App\Http\Controllers\Admin\CenterCodesController;
use App\Http\Controllers\Admin\ChatbotOptionsController;
use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\Admin\CouponsController;
use App\Http\Controllers\Admin\CoursesController;
use App\Http\Controllers\Admin\ExamsController;
use App\Http\Controllers\Admin\FaqsController;
use App\Http\Controllers\Admin\GradingController;
use App\Http\Controllers\Admin\ImpersonationController;
use App\Http\Controllers\Admin\LecturesController;
use App\Http\Controllers\Admin\OrdersController;
use App\Http\Controllers\Admin\QaModerationController;
use App\Http\Controllers\Admin\QuestionsController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\UserRoleController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Student\CheckoutController;
use App\Http\Controllers\Student\CourseController;
use App\Http\Controllers\Student\DashboardController;
use App\Http\Controllers\Student\ExamController;
use App\Http\Controllers\Student\HomeworkController;
use App\Http\Controllers\Student\InvoiceController;
use App\Http\Controllers\Student\LearnController;
use App\Http\Controllers\Student\PersonalExamController;
use App\Http\Controllers\Student\ProfileController;
use App\Http\Controllers\Student\QaController;
use App\Http\Controllers\Student\ResultsController;
use App\Http\Controllers\Student\SecurityController;
use App\Http\Controllers\Student\WalletController;
use Illuminate\Support\Facades\Route;

// ------------------------------------------------------------------ عام (زوار)
// الصفحة الرئيسية للزوار فقط — المستخدم المسجل يتحول تلقائياً للوحته (طالب/أدمن/قيد المراجعة)
Route::get('/', [HomeController::class, 'index'])->middleware('guest')->name('home');
Route::get('/courses', [HomeController::class, 'courses'])->name('courses.index');
Route::get('/courses/{course:slug}', [HomeController::class, 'course'])->name('courses.show');
Route::get('/books', [HomeController::class, 'books'])->name('books.index');
Route::get('/books/{book:slug}', [HomeController::class, 'book'])->name('books.show');
Route::get('/help', [HomeController::class, 'help'])->name('help');
Route::post('/chatbot', [HomeController::class, 'chatbot'])->name('chatbot');

// ------------------------------------------------------------------ المصادقة
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    Route::get('/account/pending', [ProfileController::class, 'pending'])->name('account.pending');
    Route::post('/account/theme', [ProfileController::class, 'theme'])->name('account.theme');
    Route::post('/impersonate/stop', [ImpersonationController::class, 'stop'])->name('impersonate.stop');
});

// ------------------------------------------------------------------ الطالب (بعد التفعيل)
Route::middleware(['auth', 'approved'])->prefix('me')->name('student.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    // الملف الشخصي للعرض فقط — تعديل البيانات وكلمة المرور من لوحة التحكم حصرياً
    Route::get('/user', [ProfileController::class, 'show'])->name('profile');

    // المحفظة وكود السنتر
    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet');
    Route::get('/wallet/export', [WalletController::class, 'export'])->name('wallet.export');
    Route::get('/charge-code', [WalletController::class, 'chargeForm'])->name('charge');
    Route::post('/charge-code', [WalletController::class, 'chargeCode'])->name('charge.submit');
    Route::get('/link-center', [WalletController::class, 'linkForm'])->name('link');
    Route::post('/link-center', [WalletController::class, 'linkCenter'])->name('link.submit');

    // كورساتي والاشتراكات والفواتير
    Route::get('/courses', [CourseController::class, 'index'])->name('courses');
    Route::get('/subscriptions', [CourseController::class, 'subscriptions'])->name('subscriptions');
    Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices');

    // الأمان والمشاهدات
    Route::get('/login-data', [SecurityController::class, 'index'])->name('security');
    Route::get('/watch-details', [ResultsController::class, 'watchDetails'])->name('watch');

    // النتائج
    Route::get('/exam-results', [ResultsController::class, 'exams'])->name('results.exams');
    Route::get('/evaluation-results', [ResultsController::class, 'evaluations'])->name('results.evaluations');
    Route::get('/hm-results', [ResultsController::class, 'homework'])->name('results.homework');

    // امتحان خاص بيك وبنك الأسئلة
    Route::get('/personal-exam', [PersonalExamController::class, 'index'])->name('personal');
    Route::post('/personal-exam', [PersonalExamController::class, 'start'])->name('personal.start');
    Route::get('/question-bank', [PersonalExamController::class, 'bank'])->name('bank');

    // التعلم: كورس -> محاضرة -> فيديو/واجب/امتحان
    Route::get('/learn/{course:slug}', [LearnController::class, 'course'])->name('learn.course');
    Route::get('/learn/{course:slug}/lectures/{lecture}', [LearnController::class, 'lecture'])->name('learn.lecture');
    Route::get('/learn/{course:slug}/videos/{video}', [LearnController::class, 'video'])->name('learn.video');
    Route::post('/learn/videos/{video}/progress', [LearnController::class, 'progress'])->name('learn.progress');

    Route::get('/homework/{assignment}', [HomeworkController::class, 'show'])->name('homework.show');
    Route::post('/homework/{assignment}', [HomeworkController::class, 'submit'])->name('homework.submit');

    Route::get('/exams/{exam}', [ExamController::class, 'show'])->name('exams.show');
    Route::post('/exams/{exam}/start', [ExamController::class, 'start'])->name('exams.start');
    Route::get('/attempts/{attempt}', [ExamController::class, 'take'])->name('exams.take');
    Route::post('/attempts/{attempt}/submit', [ExamController::class, 'submit'])->name('exams.submit');
    Route::post('/attempts/{attempt}/draft', [ExamController::class, 'saveDraft'])->name('exams.draft');
    Route::post('/attempts/{attempt}/finalize', [ExamController::class, 'finalize'])->name('exams.finalize');
    Route::get('/attempts/{attempt}/result', [ExamController::class, 'result'])->name('exams.result');

    // الشراء
    Route::post('/enroll-free/{course:slug}', [CheckoutController::class, 'enrollFree'])->name('checkout.free');
    Route::get('/checkout/{course:slug}', [CheckoutController::class, 'course'])->name('checkout.course');
    Route::get('/checkout/book/{book:slug}', [CheckoutController::class, 'book'])->name('checkout.book');
    Route::post('/checkout/{type}/{id}', [CheckoutController::class, 'store'])->name('checkout.store');

    // أسئلة الدروس — التعليق محكوم بسياسة القفل (QaThreadPolicy@reply)
    Route::post('/lectures/{lecture}/questions', [QaController::class, 'store'])->name('qa.store');
    Route::post('/questions/{thread}/replies', [QaController::class, 'reply'])->name('qa.reply');
});

// ------------------------------------------------------------------ لوحة التحكم
// الدخول للوحة بصلاحية admin.access — وكل قسم مقيد بصلاحيته الحبيبية،
// والسوبر أدمن يتجاوز كل الفحوص تلقائياً (Gate::before في AppServiceProvider)
Route::middleware(['auth', 'permission:admin.access'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [App\Http\Controllers\Admin\DashboardController::class, 'index'])
        ->middleware('permission:dashboard.view')->name('dashboard');

    // المستخدمون والموافقات
    Route::middleware('permission:users.view')->group(function () {
        Route::get('/users', [UsersController::class, 'index'])->name('users.index');
        Route::get('/users/{user}', [UsersController::class, 'show'])->name('users.show');
        Route::get('/users/{user}/id-photo', [UsersController::class, 'idPhoto'])->name('users.idphoto');
    });
    Route::put('/users/{user}', [UsersController::class, 'update'])->middleware('permission:users.update')->name('users.update');
    Route::post('/users/{user}/password', [UsersController::class, 'resetPassword'])->middleware('permission:users.password')->name('users.password');
    Route::post('/users/{user}/approve', [UsersController::class, 'approve'])->middleware('permission:users.approve')->name('users.approve');
    Route::post('/users/{user}/reject', [UsersController::class, 'reject'])->middleware('permission:users.approve')->name('users.reject');
    Route::post('/users/{user}/ban', [UsersController::class, 'ban'])->middleware('permission:users.ban')->name('users.ban');
    Route::post('/users/{user}/wallet', [UsersController::class, 'adjustWallet'])->middleware('permission:wallet.adjust')->name('users.wallet');
    Route::post('/users/{user}/enroll', [UsersController::class, 'enroll'])->middleware('permission:enrollments.manage')->name('users.enroll');
    Route::post('/users/{user}/impersonate', [ImpersonationController::class, 'start'])->middleware('permission:users.impersonate')->name('users.impersonate');

    // الدور والصلاحيات الفردية لكل مستخدم
    Route::put('/users/{user}/roles', [UserRoleController::class, 'update'])->middleware('permission:roles.manage')->name('users.roles');

    // الكورسات والمحاضرات والمحتوى
    Route::middleware('permission:courses.manage')->group(function () {
        Route::resource('courses', CoursesController::class)->except(['show']);
        Route::get('/courses/{course}/lectures', [LecturesController::class, 'index'])->name('lectures.index');
        Route::post('/courses/{course}/lectures', [LecturesController::class, 'store'])->name('lectures.store');
        Route::get('/lectures/{lecture}', [LecturesController::class, 'edit'])->name('lectures.edit');
        Route::put('/lectures/{lecture}', [LecturesController::class, 'update'])->name('lectures.update');
        Route::delete('/lectures/{lecture}', [LecturesController::class, 'destroy'])->name('lectures.destroy');

        Route::post('/lectures/{lecture}/videos', [ContentController::class, 'storeVideo'])->name('videos.store');
        Route::put('/videos/{video}', [ContentController::class, 'updateVideo'])->name('videos.update');
        Route::delete('/videos/{video}', [ContentController::class, 'destroyVideo'])->name('videos.destroy');
        Route::post('/lectures/{lecture}/attachments', [ContentController::class, 'storeAttachment'])->name('attachments.store');
        Route::delete('/attachments/{attachment}', [ContentController::class, 'destroyAttachment'])->name('attachments.destroy');
        Route::post('/lectures/{lecture}/assignment', [ContentController::class, 'saveAssignment'])->name('assignments.save');
    });

    // الامتحانات وبنك الأسئلة
    Route::resource('exams', ExamsController::class)->except(['show'])->middleware('permission:exams.manage');
    Route::middleware('permission:exams.import')->group(function () {
        Route::post('/exams/{exam}/questions/import', [QuestionsController::class, 'import'])->name('questions.import');
        Route::get('/questions/import-template', [QuestionsController::class, 'template'])->name('questions.template');
    });
    Route::middleware('permission:questions.manage')->group(function () {
        Route::get('/exams/{exam}/questions', [QuestionsController::class, 'index'])->name('questions.index');
        Route::post('/exams/{exam}/questions', [QuestionsController::class, 'store'])->name('questions.store');
        Route::get('/questions/{question}', [QuestionsController::class, 'edit'])->name('questions.edit');
        Route::put('/questions/{question}', [QuestionsController::class, 'update'])->name('questions.update');
        Route::delete('/questions/{question}', [QuestionsController::class, 'destroy'])->name('questions.destroy');
    });

    // التصحيح
    Route::middleware('permission:grading.manage')->group(function () {
        Route::get('/grading', [GradingController::class, 'index'])->name('grading.index');
        Route::post('/grading/homework/{submission}', [GradingController::class, 'gradeHomework'])->name('grading.homework');
        Route::post('/grading/essay/{answer}', [GradingController::class, 'gradeEssay'])->name('grading.essay');
    });

    // الطلبات والإيصالات
    Route::middleware('permission:orders.manage')->group(function () {
        Route::get('/orders', [OrdersController::class, 'index'])->name('orders.index');
        Route::post('/orders/{order}/approve', [OrdersController::class, 'approve'])->name('orders.approve');
        Route::post('/orders/{order}/reject', [OrdersController::class, 'reject'])->name('orders.reject');
    });

    // الكوبونات والكتب وأكواد السنتر
    Route::resource('coupons', CouponsController::class)->only(['index', 'store', 'update', 'destroy'])->middleware('permission:coupons.manage');
    Route::resource('books', BooksController::class)->except(['show'])->middleware('permission:books.manage');
    Route::middleware('permission:codes.manage')->group(function () {
        Route::get('/center-codes', [CenterCodesController::class, 'index'])->name('codes.index');
        Route::post('/center-codes', [CenterCodesController::class, 'generate'])->name('codes.generate');
    });

    // مراجعة أسئلة الطلاب — عرض القائمة / الموافقة والرفض / الرد كل منها بصلاحيته
    Route::get('/qa', [QaModerationController::class, 'index'])->middleware('permission:qa.view')->name('qa.index');
    Route::post('/qa/{thread}/approve', [QaModerationController::class, 'approve'])->middleware('permission:qa.moderate')->name('qa.approve');
    Route::post('/qa/{thread}/reject', [QaModerationController::class, 'reject'])->middleware('permission:qa.moderate')->name('qa.reject');
    Route::post('/qa/{thread}/toggle-lock', [QaModerationController::class, 'toggleLock'])->middleware('permission:qa.moderate')->name('qa.toggle-lock');
    Route::post('/qa/{thread}/answer', [QaModerationController::class, 'answer'])->middleware('permission:qa.answer')->name('qa.answer');

    // إدارة محتوى الموقع (نصوص وصور كل الصفحات) + الأسئلة الشائعة
    Route::middleware('permission:settings.manage')->group(function () {
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
    });
    Route::resource('faqs', FaqsController::class)->only(['index', 'store', 'update', 'destroy'])->middleware('permission:faqs.manage');

    // شجرة الشات بوت التفاعلي (أسئلة رئيسية وفرعية بلا حدود)
    Route::resource('chatbot', ChatbotOptionsController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->parameters(['chatbot' => 'option'])
        ->middleware('permission:chatbot.manage');

    // سجل التدقيق
    Route::get('/audit', [AuditLogsController::class, 'index'])->middleware('permission:audit.view')->name('audit.index');
});
