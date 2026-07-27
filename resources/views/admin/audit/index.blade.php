@extends('layouts.admin')

@section('title', 'سجل التدقيق — لوحة التحكم')
@section('page-title', 'سجل التدقيق')

@section('page')
    {{-- فلتر نوع الحدث --}}
    <form method="GET" action="{{ route('admin.audit.index') }}" class="card-pad mb-6 flex flex-wrap items-end gap-3">
        <div class="min-w-56">
            <label for="action" class="label">نوع الحدث</label>
            <select id="action" name="action" class="input" dir="ltr">
                <option value="">كل الأحداث</option>
                @foreach ($actions as $a)
                    <option value="{{ $a }}" @selected($action === $a)>{{ $a }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn-secondary">تصفية</button>
        @if ($action)
            <a href="{{ route('admin.audit.index') }}" class="btn-secondary">مسح الفلتر</a>
        @endif
    </form>

    @if ($logs->isEmpty())
        <div class="card-pad text-center text-slate-500 dark:text-slate-400">لا توجد أحداث مسجلة.</div>
    @else
        <div class="table-box">
            <table class="table">
                <thead>
                    <tr>
                        <th>التاريخ والوقت</th>
                        <th>المنفّذ</th>
                        <th>الحدث</th>
                        <th>الهدف</th>
                        <th>تفاصيل</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($logs as $log)
                        <tr>
                            <td class="whitespace-nowrap text-xs font-semibold text-slate-500">
                                {{ $log->created_at->format('Y/m/d H:i:s') }}
                            </td>
                            <td class="font-bold text-slate-900 dark:text-white">{{ $log->actor?->name ?? 'النظام' }}</td>
                            <td>
                                @php($act = (string) $log->action)
                                @if (str_contains($act, 'impersonate'))
                                    <span class="badge-amber" dir="ltr">{{ $act }}</span>
                                @elseif (str_contains($act, 'approve') || str_contains($act, 'grade') || str_contains($act, 'answer'))
                                    <span class="badge-green" dir="ltr">{{ $act }}</span>
                                @elseif (str_contains($act, 'reject') || str_contains($act, 'ban') || str_contains($act, 'delete') || str_contains($act, 'destroy'))
                                    <span class="badge-red" dir="ltr">{{ $act }}</span>
                                @else
                                    <span class="badge-gray" dir="ltr">{{ $act }}</span>
                                @endif
                            </td>
                            <td class="text-xs font-semibold text-slate-500">
                                @if ($log->target_type)
                                    <span dir="ltr">{{ class_basename($log->target_type) }} #{{ $log->target_id }}</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @if ($log->meta)
                                    <div class="max-w-72 space-y-0.5">
                                        @foreach ($log->meta as $key => $value)
                                            <p class="truncate text-xs font-semibold text-slate-500" dir="ltr">
                                                <span class="text-slate-400">{{ $key }}:</span>
                                                {{ is_scalar($value) || $value === null ? $value : json_encode($value, JSON_UNESCAPED_UNICODE) }}
                                            </p>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-xs text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="text-xs font-semibold text-slate-400" dir="ltr">{{ $log->ip ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $logs->links() }}</div>
    @endif
@endsection
