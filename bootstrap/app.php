<?php

use App\Http\Middleware\EnsureAccountIsApproved;
use App\Http\Middleware\EnsureSingleSession;
use App\Http\Middleware\TrackSessionActivity;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // إنفاذ الجلسة الواحدة + تتبع نشاط الجهاز على كل طلبات الويب
        $middleware->web(append: [
            EnsureSingleSession::class,
            TrackSessionActivity::class,
        ]);

        $middleware->alias([
            'approved' => EnsureAccountIsApproved::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);

        // زائر غير مسجل حاول يدخل صفحة محمية => صفحة الدخول برسالة واضحة
        $middleware->redirectGuestsTo(function (Request $request) {
            if (! $request->expectsJson()) {
                session()->flash('error', 'سجل دخولك الأول علشان توصل للصفحة دي.');
            }

            return route('login');
        });

        // مستخدم مسجل حاول يفتح صفحة للزوار (الرئيسية/الدخول/التسجيل) => وجهته الصحيحة
        $middleware->redirectUsersTo(function (Request $request) {
            $user = $request->user();

            if ($user->isStaff()) {
                return route('admin.dashboard');
            }

            return $user->isApproved()
                ? route('student.dashboard')
                : route('account.pending');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
