<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseController extends Controller
{
    /** كورساتي: كل الاشتراكات مع فلتر (كل الإشتراكات / موادي / كورساتي) */
    public function index(Request $request): View
    {
        $filter = $request->string('filter', 'all')->toString();
        $user = $request->user();

        $enrollments = $user->enrollments()
            ->with('course')
            ->where('status', 'active')
            ->get()
            ->filter(fn ($e) => $e->isActive() && $e->course !== null);

        if ($filter === 'materials') {
            $enrollments = $enrollments->filter(
                fn ($e) => in_array($e->course->category, ['semester_1', 'semester_2', 'full_bundle'], true),
            );
        } elseif ($filter === 'courses') {
            $enrollments = $enrollments->filter(
                fn ($e) => in_array($e->course->category, ['monthly', 'three_months'], true),
            );
        }

        return view('student.my-courses', [
            'enrollments' => $enrollments->values(),
            'filter' => $filter,
        ]);
    }

    /** جدول الاشتراكات التفصيلي */
    public function subscriptions(Request $request): View
    {
        $orders = $request->user()->orders()
            ->with(['items.purchasable'])
            ->latest()
            ->paginate(10);

        return view('student.subscriptions', ['orders' => $orders]);
    }
}
