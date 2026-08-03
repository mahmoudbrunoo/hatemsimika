@extends('layouts.admin')

@section('title', 'الكورسات — الإدارة')
@section('page-title', 'الكورسات')

@section('page-actions')
    <a href="{{ route('admin.courses.create') }}" class="btn-primary">إضافة كورس</a>
@endsection

@section('page')
    <div class="table-box">
        <table class="table">
            <thead>
                <tr>
                    <th>الغلاف</th>
                    <th>اسم الكورس</th>
                    <th>الصف</th>
                    <th>التصنيف</th>
                    <th>السعر</th>
                    <th>الحالة</th>
                    <th>المحاضرات</th>
                    <th>المشتركون</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($courses as $course)
                    <tr>
                        <td>
                            @if ($course->thumbnail_path)
                                <img src="{{ $course->thumbnail_path }}"
                                     alt="{{ $course->title }}" class="h-10 w-16 rounded-lg object-cover">
                            @else
                                <div class="grid h-10 w-16 place-items-center rounded-lg bg-slate-100 text-lg dark:bg-slate-800">📚</div>
                            @endif
                        </td>
                        <td class="font-bold text-slate-900 dark:text-white">{{ $course->title }}</td>
                        <td>{{ $course->yearLabel() }}</td>
                        <td>{{ $course->categoryLabel() }}</td>
                        <td>
                            @if ($course->discount_price !== null)
                                <span class="font-bold text-slate-900 dark:text-white">{{ egp($course->discount_price) }}</span>
                                <span class="block text-xs text-slate-400 line-through">{{ egp($course->price) }}</span>
                            @else
                                <span class="font-bold text-slate-900 dark:text-white">{{ egp($course->price) }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="flex flex-wrap gap-1">
                                @if ($course->is_published)
                                    <span class="badge-green">منشور</span>
                                @else
                                    <span class="badge-gray">مسودة</span>
                                @endif
                                @if ($course->is_featured)
                                    <span class="badge-sky">مميز</span>
                                @endif
                            </div>
                        </td>
                        <td>{{ number_format($course->lectures_count) }}</td>
                        <td>{{ number_format($course->enrollments_count) }}</td>
                        <td>
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('admin.lectures.index', $course) }}" class="btn-secondary btn-sm">المحاضرات</a>
                                <a href="{{ route('admin.courses.edit', $course) }}" class="btn-secondary btn-sm">تعديل</a>
                                <form method="POST" action="{{ route('admin.courses.destroy', $course) }}"
                                      onsubmit="return confirm('هل أنت متأكد من حذف الكورس؟ سيتم حذف كل محاضراته ومحتواه نهائياً.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-danger btn-sm">حذف</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-slate-500 dark:text-slate-400">
                            لا توجد كورسات بعد —
                            <a href="{{ route('admin.courses.create') }}" class="font-bold text-brand-600 hover:underline dark:text-brand-400">أضف أول كورس</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $courses->links() }}
    </div>
@endsection
