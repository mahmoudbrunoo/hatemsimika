<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\LoginActivity;
use App\Models\User;
use App\Services\DeviceParser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $throttleKey = 'login:' . mb_strtolower($request->input('login')) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            throw ValidationException::withMessages([
                'login' => 'محاولات كثيرة جداً — حاول مرة أخرى بعد ' . RateLimiter::availableIn($throttleKey) . ' ثانية.',
            ]);
        }

        $login = preg_replace('/[\s\-]/', '', (string) $request->input('login'));
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        $user = User::where($field, $field === 'email' ? mb_strtolower($login) : $login)->first();

        if ($user === null || ! Hash::check($request->input('password'), $user->password)) {
            RateLimiter::hit($throttleKey, 60);

            throw ValidationException::withMessages([
                'login' => 'بيانات الدخول غير صحيحة.',
            ]);
        }

        if ($user->status === User::STATUS_BANNED) {
            throw ValidationException::withMessages([
                'login' => 'هذا الحساب محظور — تواصل مع الدعم الفني.',
            ]);
        }

        RateLimiter::clear($throttleKey);

        // إنهاء الجلسات القديمة فوراً (جهاز واحد فقط)
        LoginActivity::where('user_id', $user->id)
            ->whereNull('logged_out_at')
            ->update(['logged_out_at' => now(), 'logout_reason' => 'new_device']);

        Auth::login($user, (bool) $request->boolean('remember'));
        $request->session()->regenerate();

        $sessionId = $request->session()->getId();
        $user->update(['current_session_id' => $sessionId]);

        LoginActivity::create([
            'user_id' => $user->id,
            'session_id' => $sessionId,
            'ip' => $request->ip(),
            'logged_in_at' => now(),
            'last_activity_at' => now(),
        ] + DeviceParser::parse($request->userAgent()));

        // الموظف يذهب لأول صفحة يملك صلاحيتها في لوحة التحكم
        if ($adminHome = $user->adminHomeRoute()) {
            return redirect()->intended($adminHome);
        }

        return $user->isApproved()
            ? redirect()->intended(route('student.dashboard'))
            : redirect()->route('account.pending');
    }

    public function destroy(): RedirectResponse
    {
        $user = Auth::user();

        if ($user !== null) {
            LoginActivity::where('user_id', $user->id)
                ->where('session_id', session()->getId())
                ->whereNull('logged_out_at')
                ->update(['logged_out_at' => now(), 'logout_reason' => 'manual']);

            if ($user->current_session_id === session()->getId()) {
                $user->update(['current_session_id' => null]);
            }
        }

        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        return redirect()->route('home');
    }
}
