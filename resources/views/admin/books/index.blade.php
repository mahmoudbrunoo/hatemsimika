@extends('layouts.admin')

@section('title', 'الكتب — لوحة التحكم')
@section('page-title', 'الكتب')

@section('page-actions')
    <a href="{{ route('admin.books.create') }}" class="btn-primary">إضافة كتاب</a>
@endsection

@section('page')
    @if ($books->isEmpty())
        <div class="card-pad text-center text-slate-500 dark:text-slate-400">
            لا توجد كتب بعد — اضغط "إضافة كتاب" لإضافة أول كتاب.
        </div>
    @else
        <div class="table-box">
            <table class="table">
                <thead>
                    <tr>
                        <th>الغلاف</th>
                        <th>الكتاب</th>
                        <th>السعر</th>
                        <th>الكمية</th>
                        <th>الصف</th>
                        <th>الحالة</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($books as $book)
                        <tr>
                            <td>
                                @if ($book->cover_path)
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($book->cover_path) }}"
                                         alt="{{ $book->title }}" class="h-16 w-12 rounded-lg border border-slate-200 object-cover dark:border-slate-700">
                                @else
                                    <div class="grid h-16 w-12 place-items-center rounded-lg bg-slate-100 text-xl dark:bg-slate-800">📕</div>
                                @endif
                            </td>
                            <td>
                                <p class="font-bold text-slate-900 dark:text-white">{{ $book->title }}</p>
                                @if ($book->preview_pdf_path)
                                    <a href="{{ \Illuminate\Support\Facades\Storage::url($book->preview_pdf_path) }}" target="_blank"
                                       class="text-xs font-bold text-brand-600 hover:text-brand-700 dark:text-brand-400">ملف المعاينة (PDF)</a>
                                @endif
                            </td>
                            <td>
                                @if ($book->discount_price !== null)
                                    <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ egp($book->discount_price) }}</span>
                                    <span class="block text-xs font-semibold text-slate-400 line-through">{{ egp($book->price) }}</span>
                                @else
                                    <span class="font-bold">{{ egp($book->price) }}</span>
                                @endif
                            </td>
                            <td class="font-semibold">{{ $book->stock ?? 'غير محدود' }}</td>
                            <td class="text-xs font-semibold text-slate-500">
                                {{ $book->academic_year ? 'الصف ' . ['1' => 'الأول', '2' => 'الثاني', '3' => 'الثالث'][(string) $book->academic_year] . ' الثانوي' : 'كل الصفوف' }}
                            </td>
                            <td>
                                @if ($book->is_published)
                                    <span class="badge-green">منشور</span>
                                @else
                                    <span class="badge-gray">مسودة</span>
                                @endif
                            </td>
                            <td>
                                <div class="flex gap-2">
                                    <a href="{{ route('admin.books.edit', $book) }}" class="btn-secondary btn-sm">تعديل</a>
                                    <form method="POST" action="{{ route('admin.books.destroy', $book) }}"
                                          onsubmit="return confirm('حذف كتاب «{{ $book->title }}» نهائياً؟')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-danger btn-sm">حذف</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $books->links() }}</div>
    @endif
@endsection
