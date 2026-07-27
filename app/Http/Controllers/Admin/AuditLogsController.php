<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** سجل التدقيق: الانتحال والموافقات والطلبات وكل الأحداث الحساسة */
class AuditLogsController extends Controller
{
    public function index(Request $request): View
    {
        $action = $request->string('action')->toString() ?: null;

        $logs = AuditLog::query()
            ->when($action, fn ($q) => $q->where('action', $action))
            ->with('actor')
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.audit.index', [
            'logs' => $logs,
            'action' => $action,
            'actions' => AuditLog::distinct()->orderBy('action')->pluck('action'),
        ]);
    }
}
