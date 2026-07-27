<?php

namespace App\Http\Middleware;

use App\Models\LoginActivity;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * إنفاذ الجلسة الواحدة: تسجيل الدخول من جهاز جديد
 * يطرد الجلسة القديمة فوراً (الطلب التالي منها يتحول لصفحة الدخول).
 */
class EnsureSingleSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null && $user->current_session_id !== null
            && $user->current_session_id !== $request->session()->getId()
            && ! $request->session()->get('impersonated_by')) {

            LoginActivity::where('user_id', $user->id)
                ->where('session_id', $request->session()->getId())
                ->whereNull('logged_out_at')
                ->update(['logged_out_at' => now(), 'logout_reason' => 'new_device']);

            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json(['message' => 'تم تسجيل الدخول من جهاز آخر'], 440);
            }

            return redirect()->route('login')
                ->with('status', 'تم تسجيل الدخول إلى حسابك من جهاز آخر، وتم إنهاء هذه الجلسة.');
        }

        return $next($request);
    }
}
