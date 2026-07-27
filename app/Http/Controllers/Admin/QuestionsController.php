<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Question;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * إدارة أسئلة الامتحان: اختياري (نص/صورة/صوت في السؤال والاختيارات)
 * أو مقالي — مع الشرح التفصيلي والتلميحات.
 */
class QuestionsController extends Controller
{
    public function index(Exam $exam): View
    {
        return view('admin.questions.index', [
            'exam' => $exam,
            'questions' => $exam->questions()->with('options')->get(),
        ]);
    }

    public function store(Request $request, Exam $exam): RedirectResponse
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($request, $exam, $data) {
            $question = $exam->questions()->create($this->questionPayload($request, $data) + [
                'position' => (int) $exam->questions()->max('position') + 1,
            ]);

            if ($data['type'] === Question::TYPE_MCQ) {
                $this->syncOptions($request, $question, $data);
            }
        });

        return back()->with('status', 'تم إضافة السؤال.');
    }

    public function edit(Question $question): View
    {
        abort_if($question->exam === null, 404);

        return view('admin.questions.edit', [
            'question' => $question->load('options'),
            'exam' => $question->exam,
        ]);
    }

    public function update(Request $request, Question $question): RedirectResponse
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($request, $question, $data) {
            $question->update($this->questionPayload($request, $data));

            if ($data['type'] === Question::TYPE_MCQ) {
                $this->syncOptions($request, $question, $data);
            } else {
                $question->options()->delete();
            }
        });

        return redirect()->route('admin.questions.index', $question->exam)
            ->with('status', 'تم حفظ السؤال.');
    }

    public function destroy(Question $question): RedirectResponse
    {
        $question->delete();

        return back()->with('status', 'تم حذف السؤال.');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'type' => ['required', Rule::in([Question::TYPE_MCQ, Question::TYPE_ESSAY])],
            'body' => ['required', 'string'],
            'subject' => ['nullable', 'string', 'max:60'],
            'points' => ['required', 'numeric', 'min:0.25', 'max:100'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'audio' => ['nullable', 'file', 'mimes:mp3,wav,ogg,m4a', 'max:10240'],
            'hint' => ['nullable', 'string'],
            'explanation' => ['nullable', 'string'],
            'explanation_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'explanation_video_url' => ['nullable', 'url', 'max:500'],
            'options' => ['required_if:type,mcq', 'array', 'min:2', 'max:6'],
            'options.*.body' => ['nullable', 'string'],
            'options.*.image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'options.*.audio' => ['nullable', 'file', 'mimes:mp3,wav,ogg,m4a', 'max:10240'],
            'correct' => ['required_if:type,mcq', 'nullable', 'integer', 'min:0'],
        ], [
            'options.required_if' => 'أضف اختيارات للسؤال.',
            'correct.required_if' => 'حدد الإجابة الصحيحة.',
        ], [
            'body' => 'نص السؤال', 'points' => 'الدرجة', 'subject' => 'المادة/الفرع',
            'image' => 'صورة السؤال', 'audio' => 'تسجيل صوتي',
        ]);
    }

    protected function questionPayload(Request $request, array $data): array
    {
        $payload = [
            'type' => $data['type'],
            'body' => $data['body'],
            'subject' => $data['subject'] ?? null,
            'points' => $data['points'],
            'hint' => $data['hint'] ?? null,
            'explanation' => $data['explanation'] ?? null,
            'explanation_video_url' => $data['explanation_video_url'] ?? null,
        ];

        if ($request->hasFile('image')) {
            $payload['image_path'] = $request->file('image')->store('questions', 'public');
        }

        if ($request->hasFile('audio')) {
            $payload['audio_path'] = $request->file('audio')->store('questions/audio', 'public');
        }

        if ($request->hasFile('explanation_image')) {
            $payload['explanation_image'] = $request->file('explanation_image')->store('questions', 'public');
        }

        return $payload;
    }

    /** إعادة بناء الاختيارات (FK إجابات الطلاب nullOnDelete فآمنة) */
    protected function syncOptions(Request $request, Question $question, array $data): void
    {
        $question->options()->delete();
        $correct = (int) ($data['correct'] ?? 0);

        foreach (array_values($data['options'] ?? []) as $i => $option) {
            $imagePath = null;
            $audioPath = null;

            if ($request->hasFile("options.{$i}.image")) {
                $imagePath = $request->file("options.{$i}.image")->store('questions/options', 'public');
            }

            if ($request->hasFile("options.{$i}.audio")) {
                $audioPath = $request->file("options.{$i}.audio")->store('questions/options', 'public');
            }

            if (($option['body'] ?? null) === null && $imagePath === null && $audioPath === null) {
                continue;
            }

            $question->options()->create([
                'body' => $option['body'] ?? null,
                'image_path' => $imagePath,
                'audio_path' => $audioPath,
                'is_correct' => $i === $correct,
                'position' => $i,
            ]);
        }
    }
}
