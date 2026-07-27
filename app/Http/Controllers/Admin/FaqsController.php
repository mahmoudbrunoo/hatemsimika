<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** الأسئلة الشائعة: تغذي صفحة المساعدة والشات بوت الذكي */
class FaqsController extends Controller
{
    public function index(): View
    {
        return view('admin.faqs.index', [
            'faqs' => Faq::orderBy('position')->paginate(20),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Faq::create($this->validated($request));

        return back()->with('status', 'تم إضافة السؤال.');
    }

    public function update(Request $request, Faq $faq): RedirectResponse
    {
        $faq->update($this->validated($request));

        return back()->with('status', 'تم حفظ السؤال.');
    }

    public function destroy(Faq $faq): RedirectResponse
    {
        $faq->delete();

        return back()->with('status', 'تم حذف السؤال.');
    }

    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'question' => ['required', 'string', 'max:255'],
            'answer' => ['required', 'string'],
            'keywords' => ['nullable', 'string', 'max:255'],
            'position' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ], [], [
            'question' => 'السؤال', 'answer' => 'الإجابة',
            'keywords' => 'كلمات مفتاحية للبوت',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $data['position'] = $data['position'] ?? 0;

        return $data;
    }
}
