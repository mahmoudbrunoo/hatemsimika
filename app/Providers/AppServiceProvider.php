<?php

namespace App\Providers;

use App\Models\QaThread;
use App\Models\User;
use App\Policies\QaThreadPolicy;
use App\Support\Rbac;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.env') === 'production' || app()->environment('production')) {
            URL::forceScheme('https');
        }

        // السوبر أدمن (المالك) يتجاوز كل فحوص الصلاحيات نهائياً ولا يمكن لأحد تقييده —
        // نعيد null لغيره حتى تكمل بقية الفحوص (صلاحيات spatie المباشرة) طبيعياً
        Gate::before(function ($user, string $ability) {
            return $user instanceof User && $user->hasRole(Rbac::SUPER_ADMIN) ? true : null;
        });

        Gate::policy(QaThread::class, QaThreadPolicy::class);
    }
}