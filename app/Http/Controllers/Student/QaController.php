<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\QaThreadRequest;
use App\Models\Lecture;
use App\Models\QaThread;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class QaController extends Controller
{
    /** نشر سؤال على الدرس — يدخل قائمة المراجعة قبل الظهور */
    public function store(QaThreadRequest $request, Lecture $lecture): RedirectResponse
    {
        abort_unless($request->user()->isEnrolledIn($lecture->course), 403);

        $imageUrl = null;

        if ($request->hasFile('image')) {
            $path = Storage::disk('supabase_public')->putFile('qa-images', $request->file('image'));
            $imageUrl = Storage::disk('supabase_public')->url($path);
        }

        QaThread::create([
            'lecture_id' => $lecture->id,
            'user_id' => $request->user()->id,
            'body' => $request->validated('body'),
            'image_path' => $imageUrl,
            'status' => QaThread::STATUS_PENDING,
        ]);

        return back()->with('status', 'تم إرسال سؤالك — سيظهر بعد مراجعته من المساعدين.');
    }
}
