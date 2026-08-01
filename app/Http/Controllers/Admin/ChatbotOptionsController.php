<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatbotOption;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/** إدارة شجرة الشات بوت التفاعلي: أسئلة رئيسية وفرعية بلا حدود مع الردود والروابط */
class ChatbotOptionsController extends Controller
{
    public function index(): View
    {
        $options = ChatbotOption::orderBy('position')->orderBy('id')->get();
        $grouped = $options->groupBy(fn ($option) => $option->parent_id ?? 0);

        // قائمة مسطحة بعمق كل خيار — لقوائم اختيار الأب مع مسافات بادئة
        $flat = [];
        $walk = function (int $parentId, int $depth) use (&$walk, &$flat, $grouped): void {
            foreach ($grouped[$parentId] ?? [] as $option) {
                $flat[] = ['option' => $option, 'depth' => $depth];
                $walk($option->id, $depth + 1);
            }
        };
        $walk(0, 0);

        return view('admin.chatbot.index', [
            'grouped' => $grouped,
            'flat' => $flat,
            'descendants' => $this->descendantsMap($options),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        ChatbotOption::create($this->validated($request));

        return back()->with('status', 'تم إضافة الخيار للشات بوت.');
    }

    public function update(Request $request, ChatbotOption $option): RedirectResponse
    {
        $option->update($this->validated($request, $option));

        return back()->with('status', 'تم حفظ الخيار.');
    }

    public function destroy(ChatbotOption $option): RedirectResponse
    {
        $option->delete(); // الحذف يشمل كل الخيارات الفرعية بداخله (cascade)

        return back()->with('status', 'تم حذف الخيار وكل خياراته الفرعية.');
    }

    protected function validated(Request $request, ?ChatbotOption $option = null): array
    {
        $data = $request->validate([
            'parent_id' => ['nullable', 'integer', 'exists:chatbot_options,id'],
            'label' => ['required', 'string', 'max:120'],
            'response' => ['nullable', 'string', 'max:5000'],
            'link_url' => ['nullable', 'url', 'max:500'],
            'link_label' => ['nullable', 'string', 'max:120'],
            'position' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ], [], [
            'parent_id' => 'الخيار الأب', 'label' => 'نص الزر', 'response' => 'الرد',
            'link_url' => 'رابط الزر', 'link_label' => 'نص زر الرابط',
        ]);

        // نقل الخيار داخل نفسه أو داخل أحد فروعه يكسر الشجرة
        if ($option && ! empty($data['parent_id'])) {
            $blocked = [$option->id, ...($this->descendantsMap(ChatbotOption::all())[$option->id] ?? [])];

            if (in_array((int) $data['parent_id'], $blocked, true)) {
                throw ValidationException::withMessages([
                    'parent_id' => 'لا يمكن نقل الخيار داخل نفسه أو داخل أحد خياراته الفرعية.',
                ]);
            }
        }

        $data['is_active'] = $request->boolean('is_active', true);
        $data['position'] = $data['position'] ?? 0;

        return $data;
    }

    /**
     * كل الفروع (بكل المستويات) لكل خيار — لمنع الحلقات ولتعطيل الاختيارات غير الصالحة.
     *
     * @return array<int, array<int, int>>
     */
    protected function descendantsMap($options): array
    {
        $childrenMap = [];
        foreach ($options as $option) {
            $childrenMap[$option->parent_id ?? 0][] = $option->id;
        }

        $map = [];
        $collect = function (int $id) use (&$collect, $childrenMap): array {
            $ids = [];
            foreach ($childrenMap[$id] ?? [] as $childId) {
                $ids[] = $childId;
                $ids = [...$ids, ...$collect($childId)];
            }

            return $ids;
        };

        foreach ($options as $option) {
            $map[$option->id] = $collect($option->id);
        }

        return $map;
    }
}
