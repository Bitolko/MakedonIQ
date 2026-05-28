<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::query()
            ->published()
            ->ordered()
            ->withCount([
                'quizzes as quizzes_count' => fn ($query) => $query->published(),
            ])
            ->get()
            ->map(fn (Category $category): array => $this->categoryPayload($category));

        return response()->json([
            'data' => $categories,
        ]);
    }

    public function quizzes(string $slug): JsonResponse
    {
        $category = Category::query()
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        $quizzes = $category->quizzes()
            ->published()
            ->ordered()
            ->withCount([
                'questions as questions_count' => fn ($query) => $query->published(),
            ])
            ->get()
            ->map(fn ($quiz): array => [
                'id' => $quiz->id,
                'category_id' => $quiz->category_id,
                'title_en' => $quiz->title_en,
                'title_mk' => $quiz->title_mk,
                'slug' => $quiz->slug,
                'description_en' => $quiz->description_en,
                'description_mk' => $quiz->description_mk,
                'difficulty' => $quiz->difficulty,
                'estimated_minutes' => $quiz->estimated_minutes,
                'points_per_question' => $quiz->points_per_question,
                'sort_order' => $quiz->sort_order,
                'questions_count' => $quiz->questions_count,
            ]);

        return response()->json([
            'data' => [
                'category' => $this->categoryPayload($category),
                'quizzes' => $quizzes,
            ],
        ]);
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
            'quizzes_count' => $category->quizzes_count ?? null,
        ];
    }
}
