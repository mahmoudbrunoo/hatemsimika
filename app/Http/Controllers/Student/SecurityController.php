<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SecurityController extends Controller
{
    /** الآمان و تاريخ تسجيل الدخول */
    public function index(Request $request): View
    {
        $user = $request->user();

        return view('student.security', [
            'activities' => $user->loginActivities()->paginate(10),
            'logoutsToday' => $user->loginActivities()
                ->whereNotNull('logged_out_at')
                ->whereDate('logged_out_at', now()->toDateString())
                ->count(),
            'logoutsWeek' => $user->loginActivities()
                ->whereNotNull('logged_out_at')
                ->where('logged_out_at', '>=', now()->startOfWeek())
                ->count(),
        ]);
    }
}
