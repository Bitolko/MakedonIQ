<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Lesson;
use App\Models\Quiz;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    private const LOCKED_LESSON_MESSAGE = 'Create a free account to unlock this lesson.';

    public function index(Request $request): JsonResponse
    {
        $isGuest = $this->isGuest($request);

        $lessons = $this->publishedLessons()
            ->with([
                'category',
                'quizzes' => fn ($query) => $this->publishedRelatedQuizzes($query),
            ])
            ->withCount([
                'quizzes as related_quizzes_count' => fn ($query) => $this->publishedRelatedQuizzes($query),
            ])
            ->ordered()
            ->get()
            ->map(fn (Lesson $lesson): array => $this->lessonListPayload($lesson, $isGuest))
            ->values();

        $categories = Category::query()
            ->published()
            ->ordered()
            ->withCount([
                'lessons as lessons_count' => fn ($query) => $query->published(),
                'quizzes as quizzes_count' => fn ($query) => $query->published(),
            ])
            ->get()
            ->map(fn (Category $category): array => $this->categoryPayload($category))
            ->values();

        return response()->json([
            'data' => [
                'categories' => $categories,
                'lessons' => $lessons,
            ],
        ]);
    }

    public function category(Request $request, Category $category): JsonResponse
    {
        abort_unless($category->is_published, 404);

        $isGuest = $this->isGuest($request);

        $category->loadCount([
            'lessons as lessons_count' => fn ($query) => $query->published(),
            'quizzes as quizzes_count' => fn ($query) => $query->published(),
        ]);

        $lessons = $category->lessons()
            ->published()
            ->with([
                'category',
                'quizzes' => fn ($query) => $this->publishedRelatedQuizzes($query),
            ])
            ->withCount([
                'quizzes as related_quizzes_count' => fn ($query) => $this->publishedRelatedQuizzes($query),
            ])
            ->ordered()
            ->get()
            ->map(fn (Lesson $lesson): array => $this->lessonListPayload($lesson, $isGuest))
            ->values();

        return response()->json([
            'data' => [
                'category' => $this->categoryPayload($category),
                'lessons' => $lessons,
            ],
        ]);
    }

    public function show(Request $request, Lesson $lesson): JsonResponse
    {
        abort_unless($lesson->is_published && $lesson->category()->published()->exists(), 404);

        $this->ensureLessonAccessible($lesson, $request);

        $lesson->load([
            'category',
            'quizzes' => fn ($query) => $this->publishedRelatedQuizzes($query),
        ]);

        return response()->json([
            'data' => $this->lessonDetailPayload($lesson, $this->isGuest($request)),
        ]);
    }

    private function publishedLessons(): Builder
    {
        return Lesson::query()
            ->published()
            ->whereHas('category', fn ($query) => $query->published());
    }

    private function publishedRelatedQuizzes($query)
    {
        return $query
            ->published()
            ->whereHas('category', fn ($categoryQuery) => $categoryQuery->published())
            ->with('category')
            ->ordered();
    }

    private function categoryPayload(Category $category): array
    {
        return [
            'id' => $category->id,
            'name_en' => $category->name_en,
            'name_mk' => $category->name_mk,
            'slug' => $category->slug,
            'description_en' => $category->description_en,
            'description_mk' => $category->description_mk,
            'icon' => $category->icon,
            'sort_order' => $category->sort_order,
            'lessons_count' => (int) ($category->lessons_count ?? 0),
            'quizzes_count' => (int) ($category->quizzes_count ?? 0),
        ];
    }

    private function lessonListPayload(Lesson $lesson, bool $isGuest): array
    {
        return [
            'id' => $lesson->id,
            'title_en' => $lesson->title_en,
            'title_mk' => $lesson->title_mk,
            'slug' => $lesson->slug,
            'summary_en' => $lesson->summary_en,
            'summary_mk' => $lesson->summary_mk,
            'difficulty' => $lesson->difficulty,
            'estimated_minutes' => $lesson->estimated_minutes,
            'is_demo' => $lesson->is_demo,
            'is_locked' => $isGuest && ! $lesson->is_demo,
            'category_name_en' => $lesson->category->name_en,
            'category_name_mk' => $lesson->category->name_mk,
            'category_slug' => $lesson->category->slug,
            'related_quizzes_count' => (int) ($lesson->related_quizzes_count ?? 0),
            'related_quiz' => $lesson->quizzes->first()
                ? $this->relatedQuizPayload($lesson->quizzes->first(), $isGuest)
                : null,
            'url' => "/learn/{$lesson->category->slug}/{$lesson->slug}",
        ];
    }

    private function lessonDetailPayload(Lesson $lesson, bool $isGuest): array
    {
        return [
            'id' => $lesson->id,
            'title_en' => $lesson->title_en,
            'title_mk' => $lesson->title_mk,
            'slug' => $lesson->slug,
            'summary_en' => $lesson->summary_en,
            'summary_mk' => $lesson->summary_mk,
            'content_en' => $lesson->content_en,
            'content_mk' => $lesson->content_mk,
            'difficulty' => $lesson->difficulty,
            'estimated_minutes' => $lesson->estimated_minutes,
            'is_demo' => $lesson->is_demo,
            'is_locked' => $isGuest && ! $lesson->is_demo,
            'category' => $this->categoryPayload($lesson->category),
            'related_quizzes' => $lesson->quizzes
                ->map(fn (Quiz $quiz): array => $this->relatedQuizPayload($quiz, $isGuest))
                ->values(),
        ];
    }

    private function relatedQuizPayload(Quiz $quiz, bool $isGuest): array
    {
        return [
            'id' => $quiz->id,
            'title_en' => $quiz->title_en,
            'title_mk' => $quiz->title_mk,
            'slug' => $quiz->slug,
            'category_slug' => $quiz->category->slug,
            'difficulty' => $quiz->difficulty,
            'estimated_minutes' => $quiz->estimated_minutes,
            'is_demo' => $quiz->is_demo,
            'is_locked' => $isGuest && ! $quiz->is_demo,
            'start_url' => "/quizzes/{$quiz->category->slug}/{$quiz->slug}/start",
        ];
    }

    private function ensureLessonAccessible(Lesson $lesson, Request $request): void
    {
        abort_if($this->isGuest($request) && ! $lesson->is_demo, 403, self::LOCKED_LESSON_MESSAGE);
    }

    private function isGuest(Request $request): bool
    {
        return $request->user() === null;
    }
}
