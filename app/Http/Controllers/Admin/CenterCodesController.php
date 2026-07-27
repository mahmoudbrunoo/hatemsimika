<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\CenterCode;
use App\Models\Course;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

/** أكواد السنتر: شحن رصيد أو فتح كورس مباشرة — تولد على دفعات */
class CenterCodesController extends Controller
{
    public function index(Request $request): View
    {
        $batch = $request->string('batch')->toString() ?: null;
        $unusedOnly = $request->boolean('unused');

        $codes = CenterCode::query()
            ->when($batch, fn ($q) => $q->where('batch', $batch))
            ->when($unusedOnly, fn ($q) => $q->whereNull('used_by'))
            ->with(['course', 'usedBy'])
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('admin.codes.index', [
            'codes' => $codes,
            'batch' => $batch,
            'unusedOnly' => $unusedOnly,
            'batches' => CenterCode::whereNotNull('batch')->distinct()->pluck('batch'),
            'courses' => Course::orderBy('academic_year')->orderBy('position')->get(),
        ]);
    }

    public function generate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'count' => ['required', 'integer', 'min:1', 'max:500'],
            'value' => ['required_without:course_id', 'nullable', 'numeric', 'min:0'],
            'course_id' => ['nullable', 'exists:courses,id'],
            'batch' => ['required', 'string', 'max:40'],
        ], [
            'value.required_without' => 'حدد قيمة الشحن أو اختر كورس.',
        ], [
            'count' => 'عدد الأكواد', 'value' => 'قيمة الشحن',
            'course_id' => 'الكورس', 'batch' => 'اسم الدفعة',
        ]);

        for ($i = 0; $i < (int) $data['count']; $i++) {
            CenterCode::create([
                'code' => $this->uniqueCode(),
                'value' => $data['course_id'] ? 0 : (float) ($data['value'] ?? 0),
                'course_id' => $data['course_id'] ?? null,
                'batch' => trim($data['batch']),
            ]);
        }

        AuditLog::record('center_codes.generate', null, [
            'count' => $data['count'],
            'batch' => $data['batch'],
        ]);

        return redirect()->route('admin.codes.index', ['batch' => trim($data['batch'])])
            ->with('status', 'تم توليد ' . $data['count'] . ' كود في دفعة: ' . $data['batch']);
    }

    protected function uniqueCode(): string
    {
        do {
            $code = Str::upper(Str::random(4) . random_int(1000, 9999) . Str::random(4));
        } while (CenterCode::where('code', $code)->exists());

        return $code;
    }
}
