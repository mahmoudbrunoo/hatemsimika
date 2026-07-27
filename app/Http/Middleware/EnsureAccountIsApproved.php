<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * منع الحسابات غير المفعلة من الوصول للمحتوى المدفوع ولوحة الطالب
 * لحين مراجعة الأدمن لصورة البطاقة والبيانات.
 */
class EnsureAccountIsApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null && ! $user->isApproved() && ! $user->isStaff()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'الحساب قيد المراجعة'], 403);
            }

            return redirect()->route('account.pending');
        }

        return $next($request);
    }
}
