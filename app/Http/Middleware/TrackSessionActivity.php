<?php

namespace App\Http\Middleware;

use App\Models\LoginActivity;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/** تحديث آخر نشاط لسجل الجهاز (كل دقيقتين على الأكثر لتخفيف الكتابة) */
class TrackSessionActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null) {
            $key = 'login_activity_touch.' . $request->session()->getId();

            if (Cache::add($key, 1, 120)) {
                LoginActivity::where('session_id', $request->session()->getId())
                    ->whereNull('logged_out_at')
                    ->update(['last_activity_at' => now()]);
            }
        }

        return $next($request);
    }
}
