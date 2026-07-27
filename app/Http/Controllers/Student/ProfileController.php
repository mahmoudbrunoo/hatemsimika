<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Rules\EgyptianPhone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function pending(Request $request): View|RedirectResponse
    {
        if ($request->user()->isApproved() || $request->user()->isStaff()) {
            return redirect()->route('student.dashboard');
        }

        return view('auth.pending');
    }

    public function show(Request $request): View
    {
        $user = $request->user();

        // احصائيات كورساتك — حلقات دائرية كما في التصميم المرجعي
        $submitted = \App\Models\ExamAttempt::where('user_id', $user->id)->whereNotNull('submitted_at');

        $examsDone = (clone $submitted)->distinct('exam_id')->count('exam_id');
        $examsTotal = max(\App\Models\Exam::count(), $examsDone);
        $avgPercent = (int) round((clone $submitted)->avg('percent') ?? 0);

        $videosWatched = \App\Models\VideoView::where('user_id', $user->id)->where('completed', true)->count();
        $videosTotal = max(\App\Models\Video::count(), $videosWatched);

        return view('student.profile', [
            'user' => $user,
            'stats' => [
                'avg_percent' => min($avgPercent, 100),
                'exams_done' => $examsDone,
                'exams_total' => $examsTotal,
                'exams_percent' => $examsTotal > 0 ? (int) round($examsDone / $examsTotal * 100) : 0,
                'videos_watched' => $videosWatched,
                'videos_total' => $videosTotal,
                'videos_percent' => $videosTotal > 0 ? (int) round($videosWatched / $videosTotal * 100) : 0,
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'father_phone' => ['nullable', new EgyptianPhone, 'different:phone'],
            'mother_phone' => ['nullable', new EgyptianPhone, 'different:phone'],
            'school' => ['nullable', 'string', 'max:255'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($request->hasFile('avatar')) {
            $data['avatar_path'] = $request->file('avatar')->store('avatars', 'public');
        }

        unset($data['avatar']);
        $user->update($data);

        return back()->with('status', 'تم تحديث البيانات بنجاح.');
    }

    public function password(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $request->user()->update(['password' => Hash::make($data['password'])]);

        return back()->with('status', 'تم تغيير كلمة المرور بنجاح.');
    }

    /** مزامنة الوضع الليلي/النهاري مع قاعدة البيانات */
    public function theme(Request $request): JsonResponse
    {
        $data = $request->validate(['theme' => ['required', Rule::in(['light', 'dark'])]]);
        $request->user()->update(['theme' => $data['theme']]);

        return response()->json(['ok' => true]);
    }
}
