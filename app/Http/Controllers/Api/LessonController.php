<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Lesson;
use App\Models\Quiz;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;

class LessonController extends Controller
{
    public function index(): JsonResponse
    {
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
            ->map(fn (Lesson $lesson): array => $this->lessonListPayload($lesson))
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

    public function category(Category $category): JsonResponse
    {
        abort_unless($category->is_published, 404);

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
            ->map(fn (Lesson $lesson): array => $this->lessonListPayload($lesson))
            ->values();

        return response()->json([
            'data' => [
                'category' => $this->categoryPayload($category),
                'lessons' => $lessons,
            ],
        ]);
    }

    public function show(Lesson $lesson): JsonResponse
    {
        abort_unless($lesson->is_published && $lesson->category()->published()->exists(), 404);

        $lesson->load([
            'category',
            'quizzes' => fn ($query) => $this->publishedRelatedQuizzes($query),
        ]);

        return response()->json([
            'data' => $this->lessonDetailPayload($lesson),
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

    private function lessonListPayload(Lesson $lesson): array
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
            'category_name_en' => $lesson->category->name_en,
            'category_name_mk' => $lesson->category->name_mk,
            'category_slug' => $lesson->category->slug,
            'related_quizzes_count' => (int) ($lesson->related_quizzes_count ?? 0),
            'related_quiz' => $lesson->quizzes->first()
                ? $this->relatedQuizPayload($lesson->quizzes->first())
                : null,
            'url' => "/learn/{$lesson->category->slug}/{$lesson->slug}",
        ];
    }

    private function lessonDetailPayload(Lesson $lesson): array
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
            'category' => $this->categoryPayload($lesson->category),
            'related_quizzes' => $lesson->quizzes
                ->map(fn (Quiz $quiz): array => $this->relatedQuizPayload($quiz))
                ->values(),
        ];
    }

    private function relatedQuizPayload(Quiz $quiz): array
    {
        return [
            'id' => $quiz->id,
            'title_en' => $quiz->title_en,
            'title_mk' => $quiz->title_mk,
            'slug' => $quiz->slug,
            'category_slug' => $quiz->category->slug,
            'difficulty' => $quiz->difficulty,
            'estimated_minutes' => $quiz->estimated_minutes,
            'start_url' => "/quizzes/{$quiz->category->slug}/{$quiz->slug}/start",
        ];
    }
}
