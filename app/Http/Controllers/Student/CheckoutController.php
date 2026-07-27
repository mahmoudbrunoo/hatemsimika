<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\ManualPaymentRequest;
use App\Models\Book;
use App\Models\Coupon;
use App\Models\Course;
use App\Services\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(protected CheckoutService $checkout)
    {
    }

    public function course(Request $request, Course $course): View|RedirectResponse
    {
        abort_unless($course->is_published, 404);

        if ($request->user()->isEnrolledIn($course)) {
            return redirect()->route('student.learn.course', $course);
        }

        return view('student.checkout', ['item' => $course, 'type' => 'course']);
    }

    public function book(Request $request, Book $book): View
    {
        abort_unless($book->is_published, 404);

        return view('student.checkout', ['item' => $book, 'type' => 'book']);
    }

    public function store(ManualPaymentRequest $request, string $type, int $id): RedirectResponse
    {
        $item = $type === 'course'
            ? Course::published()->findOrFail($id)
            : Book::where('is_published', true)->findOrFail($id);

        $coupon = null;

        if ($request->filled('coupon_code')) {
            $coupon = Coupon::whereRaw('LOWER(code) = ?', [mb_strtolower($request->validated('coupon_code'))])->first();

            if ($coupon === null || ! $coupon->isUsable()) {
                return back()->withErrors(['coupon_code' => 'كود الخصم غير صالح أو منتهي.'])->withInput();
            }
        }

        $extra = ['sender_phone' => $request->validated('sender_phone')];

        if ($request->hasFile('receipt')) {
            $extra['receipt_path'] = $request->file('receipt')->store('receipts', 'public');
        }

        try {
            $order = $this->checkout->createOrder(
                $request->user(),
                $item,
                $request->validated('payment_method'),
                $coupon,
                $extra,
            );
        } catch (\RuntimeException $e) {
            return back()->withErrors(['payment_method' => $e->getMessage()])->withInput();
        }

        if ($order->status === \App\Models\Order::STATUS_PAID) {
            return $type === 'course'
                ? redirect()->route('student.learn.course', $item)->with('status', 'تم الاشتراك بنجاح — بالتوفيق!')
                : redirect()->route('student.invoices')->with('status', 'تم الدفع بنجاح.');
        }

        return redirect()->route('student.invoices')
            ->with('status', 'تم استلام طلبك — جاري مراجعة إيصال الدفع وسيتم التفعيل خلال ساعات.');
    }
}
