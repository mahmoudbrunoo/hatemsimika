<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Course;
use App\Models\Faq;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request): View
    {
        $year = auth()->user()?->academic_year;

        $featured = Course::published()
            ->when($year, fn ($q) => $q->forYear($year))
            ->orderByDesc('is_featured')
            ->orderBy('position')
            ->take(6)
            ->get();

        return view('home', [
            'featured' => $featured,
            'years' => \App\Models\User::YEARS,
        ]);
    }

    public function courses(Request $request): View
    {
        $year = $request->integer('year') ?: null;
        $category = $request->string('category')->toString() ?: null;

        $courses = Course::published()
            ->when($year, fn ($q) => $q->forYear($year))
            ->when($category, fn ($q) => $q->where('category', $category))
            ->orderBy('position')
            ->paginate(12)
            ->withQueryString();

        return view('courses.index', [
            'courses' => $courses,
            'year' => $year,
            'category' => $category,
        ]);
    }

    public function course(Course $course): View
    {
        abort_unless($course->is_published, 404);

        $course->load(['lectures' => fn ($q) => $q->where('is_published', true)]);

        return view('courses.show', [
            'course' => $course,
            'enrolled' => auth()->check() && auth()->user()->isEnrolledIn($course),
        ]);
    }

    public function books(): View
    {
        return view('books.index', [
            'books' => Book::where('is_published', true)->orderBy('position')->paginate(12),
        ]);
    }

    public function book(Book $book): View
    {
        abort_unless($book->is_published, 404);

        return view('books.show', ['book' => $book]);
    }

    public function help(): View
    {
        return view('help', ['faqs' => Faq::where('is_active', true)->orderBy('position')->get()]);
    }

    /** شات بوت الأسئلة الشائعة: مطابقة بالكلمات المفتاحية */
    public function chatbot(Request $request): JsonResponse
    {
        $message = mb_strtolower(trim((string) $request->input('message')));

        if ($message === '') {
            return response()->json(['answer' => setting('chatbot.welcome', 'أهلاً بيك! اسألني عن الاشتراك أو الدفع أو المنصة.')]);
        }

        $faqs = Faq::where('is_active', true)->get();
        $best = null;
        $bestScore = 0;

        foreach ($faqs as $faq) {
            $keywords = array_filter(array_map('trim', explode(',', (string) $faq->keywords)));
            $keywords[] = $faq->question;
            $score = 0;

            foreach ($keywords as $keyword) {
                foreach (preg_split('/\s+/u', mb_strtolower($keyword)) as $word) {
                    if (mb_strlen($word) >= 3 && mb_strpos($message, $word) !== false) {
                        $score++;
                    }
                }
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $faq;
            }
        }

        return response()->json([
            'answer' => $best?->answer
                ?? setting('chatbot.fallback', 'معرفتش أجاوبك على السؤال ده — كلمنا على واتساب وهنساعدك فوراً.'),
        ]);
    }
}
