<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** أكواد الخصم: نسبة أو مبلغ ثابت مع حد استخدام وتاريخ انتهاء */
class CouponsController extends Controller
{
    public function index(): View
    {
        return view('admin.coupons.index', [
            'coupons' => Coupon::latest()->paginate(15),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Coupon::create($this->validated($request));

        return back()->with('status', 'تم إنشاء كود الخصم.');
    }

    public function update(Request $request, Coupon $coupon): RedirectResponse
    {
        $coupon->update($this->validated($request, $coupon));

        return back()->with('status', 'تم تحديث كود الخصم.');
    }

    public function destroy(Coupon $coupon): RedirectResponse
    {
        $coupon->delete();

        return back()->with('status', 'تم حذف كود الخصم.');
    }

    protected function validated(Request $request, ?Coupon $coupon = null): array
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:40', Rule::unique('coupons')->ignore($coupon?->id)],
            'type' => ['required', Rule::in(['percent', 'fixed'])],
            'value' => ['required', 'numeric', 'min:1', Rule::when($request->input('type') === 'percent', ['max:100'])],
            'max_uses' => ['nullable', 'integer', 'min:1'],
            'expires_at' => ['nullable', 'date'],
            'is_active' => ['nullable', 'boolean'],
        ], [], [
            'code' => 'الكود', 'type' => 'نوع الخصم', 'value' => 'قيمة الخصم',
            'max_uses' => 'أقصى عدد استخدام', 'expires_at' => 'تاريخ الانتهاء',
        ]);

        $data['code'] = mb_strtoupper(trim($data['code']));
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }
}
