<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    /** الفواتير: سجل الطلبات بحالاتها */
    public function index(Request $request): View
    {
        return view('student.invoices', [
            'orders' => $request->user()->orders()->with('coupon')->latest()->paginate(10),
        ]);
    }
}
