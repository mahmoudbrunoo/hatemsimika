<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** إدارة الكتب: كتب مستر حاتم سميكة الورقية والرقمية */
class BooksController extends Controller
{
    public function index(): View
    {
        return view('admin.books.index', [
            'books' => Book::orderBy('position')->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.books.form', ['book' => new Book]);
    }

    public function store(Request $request): RedirectResponse
    {
        Book::create($this->validated($request));

        return redirect()->route('admin.books.index')->with('status', 'تم إضافة الكتاب.');
    }

    public function edit(Book $book): View
    {
        return view('admin.books.form', ['book' => $book]);
    }

    public function update(Request $request, Book $book): RedirectResponse
    {
        $book->update($this->validated($request, $book));

        return redirect()->route('admin.books.index')->with('status', 'تم حفظ التعديلات.');
    }

    public function destroy(Book $book): RedirectResponse
    {
        $book->delete();

        return back()->with('status', 'تم حذف الكتاب.');
    }

    protected function validated(Request $request, ?Book $book = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'discount_price' => ['nullable', 'numeric', 'min:0', 'lt:price'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'academic_year' => ['nullable', Rule::in([1, 2, 3])],
            'position' => ['nullable', 'integer', 'min:0'],
            'is_published' => ['nullable', 'boolean'],
            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'preview_pdf' => ['nullable', 'file', 'mimes:pdf', 'max:20480'],
        ], [], [
            'title' => 'اسم الكتاب', 'price' => 'السعر', 'discount_price' => 'السعر بعد الخصم',
            'stock' => 'الكمية المتاحة', 'cover' => 'صورة الغلاف', 'preview_pdf' => 'ملف المعاينة',
        ]);

        $data['is_published'] = $request->boolean('is_published', true);
        $data['position'] = $data['position'] ?? 0;

        if ($book === null || $book->title !== $data['title']) {
            $data['slug'] = (Str::slug($data['title']) ?: 'book') . '-' . Str::lower(Str::random(5));
        }

        if ($request->hasFile('cover')) {
            $data['cover_path'] = $request->file('cover')->store('books', 'public');
        }

        if ($request->hasFile('preview_pdf')) {
            $data['preview_pdf_path'] = $request->file('preview_pdf')->store('books/previews', 'public');
        }

        unset($data['cover'], $data['preview_pdf']);

        return $data;
    }
}
