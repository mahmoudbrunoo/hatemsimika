<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Models\VideoView;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $totalVideoViews = VideoView::count();

        $stats = [
            'students' => User::role('student')->count(),
            'pending' => User::where('status', User::STATUS_PENDING)->count(),
            'revenue' => (float) Order::where('status', Order::STATUS_PAID)->sum('total'),
            'pending_orders' => Order::where('status', Order::STATUS_PENDING)
                ->whereIn('payment_method', ['manual_vodafone', 'manual_instapay'])
                ->count(),
            'active_sessions' => DB::table('sessions')->whereNotNull('user_id')
                ->where('last_activity', '>=', now()->subMinutes(15)->getTimestamp())
                ->count(),
            'completion_rate' => $totalVideoViews > 0
                ? round(VideoView::where('completed', true)->count() / $totalVideoViews * 100, 1)
                : 0,
            'avg_score' => round((float) DB::table('exam_attempts')->whereNotNull('submitted_at')->avg('percent'), 1),
        ];

        // إيرادات آخر 14 يوم
        $revenueSeries = collect(range(13, 0))->map(function ($daysAgo) {
            $date = now()->subDays($daysAgo)->toDateString();

            return [
                'label' => now()->subDays($daysAgo)->format('m/d'),
                'value' => (float) Order::where('status', Order::STATUS_PAID)
                    ->whereDate('paid_at', $date)
                    ->sum('total'),
            ];
        })->values();

        // تسجيلات آخر 14 يوم
        $signupSeries = collect(range(13, 0))->map(fn ($daysAgo) => [
            'label' => now()->subDays($daysAgo)->format('m/d'),
            'value' => User::whereDate('created_at', now()->subDays($daysAgo)->toDateString())->count(),
        ])->values();

        return view('admin.dashboard', compact('stats', 'revenueSeries', 'signupSeries'));
    }
}
