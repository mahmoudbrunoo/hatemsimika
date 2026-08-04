@extends('layouts.admin')

@section('title', 'الطلاب والموافقات — الإدارة')
@section('page-title', 'الطلاب والموافقات')

@section('page')
    {{-- شريط البحث والفلترة --}}
    <form method="GET" action="{{ route('admin.users.index') }}" class="card-pad mb-6">
        <div class="grid gap-4 sm:grid-cols-[1fr_12rem_12rem_auto]">
            <div>
                <label for="q" class="label">بحث</label>
                <input id="q" name="q" type="text" value="{{ $search }}" class="input"
                       placeholder="الاسم أو الموبايل أو البريد أو الرقم القومي">
            </div>

            <div>
                <label for="status" class="label">حالة الحساب</label>
                <select id="status" name="status" class="input">
                    <option value="">كل الحالات</option>
                    @foreach (\App\Models\User::STATUSES as $value => $label)
                        <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="role" class="label">الدور</label>
                <select id="role" name="role" class="input">
                    <option value="">كل الأدوار</option>
                    @foreach (\App\Support\Rbac::ROLE_LABELS as $value => $label)
                        <option value="{{ $value }}" @selected($role === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="self-end">
                <button type="submit" class="btn-secondary w-full">فلترة</button>
            </div>
        </div>
    </form>

    {{-- جدول الطلاب --}}
    <div class="table-box">
        <table class="table">
            <thead>
                <tr>
                    <th>الاسم</th>
                    <th>الموبايل</th>
                    <th>البريد الإلكتروني</th>
                    <th>الصف الدراسي</th>
                    <th>الدور</th>
                    <th>الحالة</th>
                    <th>تاريخ التسجيل</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td class="font-bold text-slate-900 dark:text-white">{{ $user->name }}</td>
                        <td dir="ltr">{{ $user->phone }}</td>
                        <td dir="ltr">{{ $user->email }}</td>
                        <td>{{ $user->yearLabel() }}</td>
                        <td>
                            <span class="{{ ['super_admin' => 'badge-red', 'admin' => 'badge-sky', 'teacher' => 'badge-green'][$user->roleName()] ?? 'badge-gray' }}">
                                {{ $user->roleLabel() }}
                            </span>
                        </td>
                        <td>
                            @switch($user->status)
                                @case(\App\Models\User::STATUS_PENDING)
                                    <span class="badge-amber">قيد المراجعة</span>
                                    @break
                                @case(\App\Models\User::STATUS_APPROVED)
                                    <span class="badge-green">مفعل</span>
                                    @break
                                @case(\App\Models\User::STATUS_REJECTED)
                                    <span class="badge-red">مرفوض</span>
                                    @break
                                @case(\App\Models\User::STATUS_BANNED)
                                    <span class="badge-gray">محظور</span>
                                    @break
                                @default
                                    <span class="badge-gray">{{ $user->status }}</span>
                            @endswitch
                        </td>
                        <td>{{ $user->created_at->format('Y/m/d') }}</td>
                        <td>
                            <a href="{{ route('admin.users.show', $user) }}" class="btn-secondary btn-sm">عرض</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-slate-500 dark:text-slate-400">لا يوجد طلاب مطابقون للبحث.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $users->links() }}
    </div>
@endsection
