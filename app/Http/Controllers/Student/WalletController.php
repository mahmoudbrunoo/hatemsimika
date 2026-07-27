<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\CenterCode;
use App\Services\EnrollmentService;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WalletController extends Controller
{
    public function __construct(
        protected WalletService $wallet,
        protected EnrollmentService $enrollment,
    ) {
    }

    /** صفحة رصيدي: الشحن + سجل حركات المحفظة */
    public function index(Request $request): View
    {
        return view('student.wallet', [
            'user' => $request->user(),
            'transactions' => $request->user()->walletTransactions()->paginate(10),
        ]);
    }

    /** تصدير حركات المحفظة CSV (يفتح في Excel) */
    public function export(Request $request): StreamedResponse
    {
        $user = $request->user();

        return response()->streamDownload(function () use ($user) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM للعربي في Excel
            fputcsv($out, ['التسلسل', 'المبلغ المتغير', 'الرصيد قبل التغيير', 'الرصيد بعد التغيير', 'ملحوظة', 'وقت العملية']);

            foreach ($user->walletTransactions()->get() as $t) {
                fputcsv($out, [
                    $t->id, $t->amount, $t->balance_before, $t->balance_after,
                    $t->note, $t->created_at->format('Y-m-d H:i'),
                ]);
            }

            fclose($out);
        }, 'wallet-transactions.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function chargeForm(): View
    {
        return view('student.charge-code');
    }

    /** شحن كود سنتر: يشحن رصيد أو يفتح كورس مباشرة */
    public function chargeCode(Request $request): RedirectResponse
    {
        $data = $request->validate(
            ['code' => ['required', 'string', 'max:20']],
            [],
            ['code' => 'كود السنتر'],
        );

        $user = $request->user();

        return DB::transaction(function () use ($data, $user) {
            $code = CenterCode::whereRaw('LOWER(code) = ?', [mb_strtolower(trim($data['code']))])
                ->lockForUpdate()
                ->first();

            if ($code === null || $code->used_by !== null) {
                return back()->withErrors(['code' => 'الكود غير صحيح أو مستخدم من قبل.']);
            }

            $code->update(['used_by' => $user->id, 'used_at' => now()]);

            if ($code->course !== null) {
                $this->enrollment->enroll($user, $code->course, 'code');
                $message = 'تم فتح كورس: ' . $code->course->title;
            } else {
                $this->wallet->credit($user, (float) $code->value, 'center_code', 'شحن كود سنتر', $code);
                $message = 'تم شحن ' . egp($code->value) . ' في محفظتك.';
            }

            AuditLog::record('center_code.redeem', $code, ['code' => $code->code]);

            return back()->with('status', $message);
        });
    }

    public function linkForm(): View
    {
        return view('student.link-center');
    }

    /** ربط ID السنتر بالحساب لفتح محاضرات السنتر */
    public function linkCenter(Request $request): RedirectResponse
    {
        $data = $request->validate(
            ['center_id' => ['required', 'string', 'max:30']],
            [],
            ['center_id' => 'كود السنتر الخاص بك'],
        );

        $request->user()->update(['center_id' => trim($data['center_id'])]);

        return back()->with('status', 'تم ربط الـ ID بحسابك بنجاح.');
    }
}
