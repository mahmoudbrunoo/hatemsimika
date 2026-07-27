@extends('layouts.app')

@section('title', 'الكتب')

@section('content')
    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6">
        <h1 class="text-3xl font-black text-slate-900 dark:text-white">كتب مستر حاتم سميكة</h1>
        <p class="mt-2 font-semibold text-slate-500 dark:text-slate-400">كتب ورقية ورقمية بشرح مبسط وتدريبات شاملة</p>

        @if ($books->isEmpty())
            <div class="card-pad mt-8 text-center text-slate-500 dark:text-slate-400">لا توجد كتب متاحة حالياً.</div>
        @else
            <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($books as $book)
                    <a href="{{ route('books.show', $book) }}" class="card group flex flex-col overflow-hidden transition hover:-translate-y-1 hover:shadow-lg">
                        <div class="relative aspect-[3/4] bg-slate-100 dark:bg-slate-800">
                            @if ($book->cover_path)
                                <img src="{{ Storage::url($book->cover_path) }}" alt="{{ $book->title }}" class="size-full object-cover">
                            @else
                                <div class="grid size-full place-items-center bg-gradient-to-br from-amber-400 to-amber-700 text-5xl">📕</div>
                            @endif
                            @if ($book->discountPercent())
                                <span class="absolute left-3 top-3 badge-red">خصم {{ $book->discountPercent() }}%</span>
                            @endif
                        </div>
                        <div class="flex flex-1 flex-col p-4">
                            <h3 class="font-extrabold text-slate-900 group-hover:text-brand-600 dark:text-white">{{ $book->title }}</h3>
                            @if ($book->academic_year)
                                <span class="badge-sky mt-2 w-fit">{{ \App\Models\User::YEARS[$book->academic_year] }}</span>
                            @endif
                            <div class="mt-auto pt-3">
                                @if ($book->discount_price !== null)
                                    <span class="text-lg font-black text-brand-600">{{ egp($book->discount_price) }}</span>
                                    <span class="mr-1 text-sm font-bold text-slate-400 line-through">{{ egp($book->price) }}</span>
                                @else
                                    <span class="text-lg font-black text-brand-600">{{ egp($book->price) }}</span>
                                @endif
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-8">{{ $books->links() }}</div>
        @endif
    </section>
@endsection
