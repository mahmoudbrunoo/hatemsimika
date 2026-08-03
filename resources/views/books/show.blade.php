@extends('layouts.app')

@section('title', $book->title)

@section('content')
    <section class="mx-auto max-w-5xl px-4 py-10 sm:px-6">
        <div class="grid gap-8 md:grid-cols-2">
            <div class="card overflow-hidden">
                <div class="aspect-[3/4] bg-slate-100 dark:bg-slate-800">
                    @if ($book->cover_path)
                        <img src="{{ $book->cover_path }}" alt="{{ $book->title }}" class="size-full object-cover">
                    @else
                        <div class="grid size-full place-items-center bg-gradient-to-br from-amber-400 to-amber-700 text-8xl">📕</div>
                    @endif
                </div>
            </div>

            <div>
                @if ($book->academic_year)
                    <span class="badge-sky">{{ \App\Models\User::YEARS[$book->academic_year] }}</span>
                @endif
                <h1 class="mt-3 text-3xl font-black text-slate-900 dark:text-white">{{ $book->title }}</h1>

                @if ($book->description)
                    <p class="mt-4 leading-8 text-slate-600 dark:text-slate-300">{{ $book->description }}</p>
                @endif

                <div class="mt-6 flex items-baseline gap-2">
                    @if ($book->discount_price !== null)
                        <span class="text-3xl font-black text-brand-600">{{ egp($book->discount_price) }}</span>
                        <span class="text-lg font-bold text-slate-400 line-through">{{ egp($book->price) }}</span>
                        <span class="badge-red">وفر {{ $book->discountPercent() }}%</span>
                    @else
                        <span class="text-3xl font-black text-brand-600">{{ egp($book->price) }}</span>
                    @endif
                </div>

                @if ($book->stock !== null)
                    <p class="mt-2 text-sm font-bold {{ $book->stock > 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                        {{ $book->stock > 0 ? 'متوفر — ' . $book->stock . ' نسخة' : 'نفدت الكمية' }}
                    </p>
                @endif

                <div class="mt-6 flex flex-wrap gap-3">
                    @if ($book->stock === null || $book->stock > 0)
                        @auth
                            <a href="{{ route('student.checkout.book', $book) }}" class="btn-primary px-6 py-3 text-base">اطلب الكتاب الآن</a>
                        @else
                            <a href="{{ route('register') }}" class="btn-primary px-6 py-3 text-base">سجل واطلب الكتاب</a>
                        @endauth
                    @endif

                    @if ($book->preview_pdf_path)
                        <a href="{{ $book->preview_pdf_path }}" target="_blank" class="btn-secondary px-6 py-3 text-base">معاينة الكتاب</a>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection
