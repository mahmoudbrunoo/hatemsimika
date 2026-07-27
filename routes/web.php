<?php

use Illuminate\Support\Facades\Route;

// ------------------------------------------------------------------ عام (زوار)
Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/courses', [App\Http\Controllers\HomeController::class, 'courses'])->name('courses.index');
Route::get('/courses/{course:slug}', [App\Http\Controllers\HomeController::class, 'course'])->name('courses.show');
Route::get('/books', [App\Http\Controllers\HomeController::class, 'books'])->name('books.index');
Route::get('/books/{book:slug}', [App\Http\Controllers\HomeController::class, 'book'])->name('books.show');
Route::get('/help', [App\Http\Controllers\HomeController::class, 'help'])->name('help');
Route::post('/chatbot', [App\Http\Controllers\HomeController::class, 'chatbot'])->name('chatbot');

// ------------------------------------------------------------------ المصادقة
Route::middleware('guest')->group(function () {
    Route::get('/register', [App\Http\Controllers\Auth\RegisterController::class, 'create'])->name('register');
    Route::post('/register', [App\Http\Controllers\Auth\RegisterController::class, 'store']);
    Route::get('/login', [App\Http\Controllers\Auth\LoginController::class, 'create'])->name('login');
    Route::post('/login', [App\Http\Controllers\Auth\LoginController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'destroy'])->name('logout');
    Route::get('/account/pending', [App\Http\Controllers\Student\ProfileController::class, 'pending'])->name('account.pending');
    Route::post('/account/theme', [App\Http\Controllers\Student\ProfileController::class, 'theme'])->name('account.theme');
    Route::post('/impersonate/stop', [App\Http\Controllers\Admin\ImpersonationController::class, 'stop'])->name('impersonate.stop');
});

// ------------------------------------------------------------------ الطالب (بعد التفعيل)
Route::middleware(['auth', 'approved'])->prefix('me')->name('student.')->group(function () {
    Route::get('/', [App\Http\Controllers\Student\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/user', [App\Http\Controllers\Student\ProfileController::class, 'show'])->name('profile');
    Route::put('/user', [App\Http\Controllers\Student\ProfileController::class, 'update'])->name('profile.update');
    Route::put('/user/password', [App\Http\Controllers\Student\ProfileController::class, 'password'])->name('profile.password');

    // المحفظة وكود السنتر
    Route::get('/wallet', [App\Http\Controllers\Student\WalletController::class, 'index'])->name('wallet');
    Route::get('/wallet/export', [App\Http\Controllers\Student\WalletController::class, 'export'])->name('wallet.export');
    Route::get('/charge-code', [App\Http\Controllers\Student\WalletController::class, 'chargeForm'])->name('charge');
    Route::post('/charge-code', [App\Http\Controllers\Student\WalletController::class, 'chargeCode'])->name('charge.submit');
    Route::get('/link-center', [App\Http\Controllers\Student\WalletController::class, 'linkForm'])->name('link');
    Route::post('/link-center', [App\Http\Controllers\Student\WalletController::class, 'linkCenter'])->name('link.submit');

    // كورساتي والاشتراكات والفواتير
    Route::get('/courses', [App\Http\Controllers\Student\CourseController::class, 'index'])->name('courses');
    Route::get('/subscriptions', [App\Http\Controllers\Student\CourseController::class, 'subscriptions'])->name('subscriptions');
    Route::get('/invoices', [App\Http\Controllers\Student\InvoiceController::class, 'index'])->name('invoices');

    // الأمان والمشاهدات
    Route::get('/login-data', [App\Http\Controllers\Student\SecurityController::class, 'index'])->name('security');
    Route::get('/watch-details', [App\Http\Controllers\Student\ResultsController::class, 'watchDetails'])->name('watch');

    // النتائج
    Route::get('/exam-results', [App\Http\Controllers\Student\ResultsController::class, 'exams'])->name('results.exams');
    Route::get('/evaluation-results', [App\Http\Controllers\Student\ResultsController::class, 'evaluations'])->name('results.evaluations');
    Route::get('/hm-results', [App\Http\Controllers\Student\ResultsController::class, 'homework'])->name('results.homework');

    // امتحان خاص بيك وبنك الأسئلة
    Route::get('/personal-exam', [App\Http\Controllers\Student\PersonalExamController::class, 'index'])->name('personal');
    Route::post('/personal-exam', [App\Http\Controllers\Student\PersonalExamController::class, 'start'])->name('personal.start');
    Route::get('/question-bank', [App\Http\Controllers\Student\PersonalExamController::class, 'bank'])->name('bank');

    // التعلم: كورس -> محاضرة -> فيديو/واجب/امتحان
    Route::get('/learn/{course:slug}', [App\Http\Controllers\Student\LearnController::class, 'course'])->name('learn.course');
    Route::get('/learn/{course:slug}/lectures/{lecture}', [App\Http\Controllers\Student\LearnController::class, 'lecture'])->name('learn.lecture');
    Route::get('/learn/{course:slug}/videos/{video}', [App\Http\Controllers\Student\LearnController::class, 'video'])->name('learn.video');
    Route::post('/learn/videos/{video}/progress', [App\Http\Controllers\Student\LearnController::class, 'progress'])->name('learn.progress');
    Route::get('/learn/{course:slug}/attachments/{attachment}', [App\Http\Controllers\Student\LearnController::class, 'attachment'])->name('learn.attachment');

    Route::get('/homework/{assignment}', [App\Http\Controllers\Student\HomeworkController::class, 'show'])->name('homework.show');
    Route::post('/homework/{assignment}', [App\Http\Controllers\Student\HomeworkController::class, 'submit'])->name('homework.submit');

    Route::get('/exams/{exam}', [App\Http\Controllers\Student\ExamController::class, 'show'])->name('exams.show');
    Route::post('/exams/{exam}/start', [App\Http\Controllers\Student\ExamController::class, 'start'])->name('exams.start');
    Route::get('/attempts/{attempt}', [App\Http\Controllers\Student\ExamController::class, 'take'])->name('exams.take');
    Route::post('/attempts/{attempt}/submit', [App\Http\Controllers\Student\ExamController::class, 'submit'])->name('exams.submit');
    Route::get('/attempts/{attempt}/result', [App\Http\Controllers\Student\ExamController::class, 'result'])->name('exams.result');

    // الشراء
    Route::get('/checkout/{course:slug}', [App\Http\Controllers\Student\CheckoutController::class, 'course'])->name('checkout.course');
    Route::get('/checkout/book/{book:slug}', [App\Http\Controllers\Student\CheckoutController::class, 'book'])->name('checkout.book');
    Route::post('/checkout/{type}/{id}', [App\Http\Controllers\Student\CheckoutController::class, 'store'])->name('checkout.store');

    // أسئلة الدروس
    Route::post('/lectures/{lecture}/questions', [App\Http\Controllers\Student\QaController::class, 'store'])->name('qa.store');
});

// ------------------------------------------------------------------ لوحة التحكم
Route::middleware(['auth', 'role:super_admin|assistant'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

    // المستخدمون والموافقات
    Route::get('/users', [App\Http\Controllers\Admin\UsersController::class, 'index'])->name('users.index');
    Route::get('/users/{user}', [App\Http\Controllers\Admin\UsersController::class, 'show'])->name('users.show');
    Route::post('/users/{user}/approve', [App\Http\Controllers\Admin\UsersController::class, 'approve'])->name('users.approve');
    Route::post('/users/{user}/reject', [App\Http\Controllers\Admin\UsersController::class, 'reject'])->name('users.reject');
    Route::post('/users/{user}/ban', [App\Http\Controllers\Admin\UsersController::class, 'ban'])->name('users.ban');
    Route::post('/users/{user}/wallet', [App\Http\Controllers\Admin\UsersController::class, 'adjustWallet'])->name('users.wallet');
    Route::post('/users/{user}/enroll', [App\Http\Controllers\Admin\UsersController::class, 'enroll'])->name('users.enroll');
    Route::post('/users/{user}/impersonate', [App\Http\Controllers\Admin\ImpersonationController::class, 'start'])->name('users.impersonate');

    // الكورسات والمحاضرات والمحتوى
    Route::resource('courses', App\Http\Controllers\Admin\CoursesController::class)->except(['show']);
    Route::get('/courses/{course}/lectures', [App\Http\Controllers\Admin\LecturesController::class, 'index'])->name('lectures.index');
    Route::post('/courses/{course}/lectures', [App\Http\Controllers\Admin\LecturesController::class, 'store'])->name('lectures.store');
    Route::get('/lectures/{lecture}', [App\Http\Controllers\Admin\LecturesController::class, 'edit'])->name('lectures.edit');
    Route::put('/lectures/{lecture}', [App\Http\Controllers\Admin\LecturesController::class, 'update'])->name('lectures.update');
    Route::delete('/lectures/{lecture}', [App\Http\Controllers\Admin\LecturesController::class, 'destroy'])->name('lectures.destroy');

    Route::post('/lectures/{lecture}/videos', [App\Http\Controllers\Admin\ContentController::class, 'storeVideo'])->name('videos.store');
    Route::put('/videos/{video}', [App\Http\Controllers\Admin\ContentController::class, 'updateVideo'])->name('videos.update');
    Route::delete('/videos/{video}', [App\Http\Controllers\Admin\ContentController::class, 'destroyVideo'])->name('videos.destroy');
    Route::post('/lectures/{lecture}/attachments', [App\Http\Controllers\Admin\ContentController::class, 'storeAttachment'])->name('attachments.store');
    Route::delete('/attachments/{attachment}', [App\Http\Controllers\Admin\ContentController::class, 'destroyAttachment'])->name('attachments.destroy');
    Route::post('/lectures/{lecture}/assignment', [App\Http\Controllers\Admin\ContentController::class, 'saveAssignment'])->name('assignments.save');

    // الامتحانات وبنك الأسئلة
    Route::resource('exams', App\Http\Controllers\Admin\ExamsController::class)->except(['show']);
    Route::get('/exams/{exam}/questions', [App\Http\Controllers\Admin\QuestionsController::class, 'index'])->name('questions.index');
    Route::post('/exams/{exam}/questions', [App\Http\Controllers\Admin\QuestionsController::class, 'store'])->name('questions.store');
    Route::get('/questions/{question}', [App\Http\Controllers\Admin\QuestionsController::class, 'edit'])->name('questions.edit');
    Route::put('/questions/{question}', [App\Http\Controllers\Admin\QuestionsController::class, 'update'])->name('questions.update');
    Route::delete('/questions/{question}', [App\Http\Controllers\Admin\QuestionsController::class, 'destroy'])->name('questions.destroy');

    // التصحيح
    Route::get('/grading', [App\Http\Controllers\Admin\GradingController::class, 'index'])->name('grading.index');
    Route::post('/grading/homework/{submission}', [App\Http\Controllers\Admin\GradingController::class, 'gradeHomework'])->name('grading.homework');
    Route::post('/grading/essay/{answer}', [App\Http\Controllers\Admin\GradingController::class, 'gradeEssay'])->name('grading.essay');

    // الطلبات والإيصالات
    Route::get('/orders', [App\Http\Controllers\Admin\OrdersController::class, 'index'])->name('orders.index');
    Route::post('/orders/{order}/approve', [App\Http\Controllers\Admin\OrdersController::class, 'approve'])->name('orders.approve');
    Route::post('/orders/{order}/reject', [App\Http\Controllers\Admin\OrdersController::class, 'reject'])->name('orders.reject');

    // الكوبونات والكتب وأكواد السنتر
    Route::resource('coupons', App\Http\Controllers\Admin\CouponsController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('books', App\Http\Controllers\Admin\BooksController::class)->except(['show']);
    Route::get('/center-codes', [App\Http\Controllers\Admin\CenterCodesController::class, 'index'])->name('codes.index');
    Route::post('/center-codes', [App\Http\Controllers\Admin\CenterCodesController::class, 'generate'])->name('codes.generate');

    // مراجعة أسئلة الطلاب
    Route::get('/qa', [App\Http\Controllers\Admin\QaModerationController::class, 'index'])->name('qa.index');
    Route::post('/qa/{thread}/approve', [App\Http\Controllers\Admin\QaModerationController::class, 'approve'])->name('qa.approve');
    Route::post('/qa/{thread}/reject', [App\Http\Controllers\Admin\QaModerationController::class, 'reject'])->name('qa.reject');
    Route::post('/qa/{thread}/answer', [App\Http\Controllers\Admin\QaModerationController::class, 'answer'])->name('qa.answer');

    // إدارة محتوى الموقع (نصوص وصور كل الصفحات) + الأسئلة الشائعة
    Route::get('/settings', [App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [App\Http\Controllers\Admin\SettingsController::class, 'update'])->name('settings.update');
    Route::resource('faqs', App\Http\Controllers\Admin\FaqsController::class)->only(['index', 'store', 'update', 'destroy']);

    // سجل التدقيق
    Route::get('/audit', [App\Http\Controllers\Admin\AuditLogsController::class, 'index'])->name('audit.index');
});
