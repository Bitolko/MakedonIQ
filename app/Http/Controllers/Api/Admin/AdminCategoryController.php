<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Question;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AdminCategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::query()
            ->ordered()
            ->withCount([
                'quizzes as quizzes_count',
                'quizzes as published_quizzes_count' => fn ($query) => $query->published(),
            ])
            ->selectSub(function ($query): void {
                $query->from('questions')
                    ->join('quizzes', 'questions.quiz_id', '=', 'quizzes.id')
                    ->selectRaw('count(*)')
                    ->whereColumn('quizzes.category_id', 'categories.id');
            }, 'questions_count')
            ->get()
            ->map(fn (Category $category): array => $this->categoryPayload($category))
            ->values();

        return response()->json(['data' => $categories]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validatedData($request);
        $category = Category::create($this->categoryAttributes($validated));

        return response()->json([
            'data' => $this->categoryPayload($this->categoryWithCounts($category)),
        ], 201);
    }

    public function show(Category $category): JsonResponse
    {
        return response()->json([
            'data' => $this->categoryPayload($this->categoryWithCounts($category)),
        ]);
    }

    public function update(Request $request, Category $category): JsonResponse
    {
        $validated = $this->validatedData($request);
        $category->update($this->categoryAttributes($validated, $category));

        return response()->json([
            'data' => $this->categoryPayload($this->categoryWithCounts($category)),
        ]);
    }

    public function destroy(Category $category): JsonResponse
    {
        if ($category->quizzes()->exists()) {
            throw ValidationException::withMessages([
                'category' => 'This category has quizzes. Unpublish it instead or move/delete its quizzes first.',
            ]);
        }

        $category->delete();

        return response()->json([
            'message' => 'Category deleted.',
        ]);
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'name_en' => ['required', 'string', 'max:255'],
            'name_mk' => ['nullable', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'description_en' => ['nullable', 'string'],
            'description_mk' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:100'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_published' => ['sometimes', 'boolean'],
        ]);
    }

    private function categoryAttributes(array $validated, ?Category $category = null): array
    {
        $baseSlug = $this->baseSlug($validated['slug'] ?? null, $validated['name_en'], 'category');

        return [
            'name_en' => $validated['name_en'],
            'name_mk' => filled($validated['name_mk'] ?? null) ? $validated['name_mk'] : $validated['name_en'],
            'slug' => $this->uniqueSlug($baseSlug, $category?->id),
            'description_en' => $validated['description_en'] ?? null,
            'description_mk' => $validated['description_mk'] ?? null,
            'icon' => $validated['icon'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_published' => array_key_exists('is_published', $validated)
                ? (bool) $validated['is_published']
                : (bool) ($category?->is_published ?? false),
        ];
    }

    private function categoryWithCounts(Category $category): Category
    {
        return Category::query()
            ->whereKey($category->id)
            ->withCount([
                'quizzes as quizzes_count',
                'quizzes as published_quizzes_count' => fn ($query) => $query->published(),
            ])
            ->selectSub(function ($query): void {
                $query->from('questions')
                    ->join('quizzes', 'questions.quiz_id', '=', 'quizzes.id')
                    ->selectRaw('count(*)')
                    ->whereColumn('quizzes.category_id', 'categories.id');
            }, 'questions_count')
            ->firstOrFail();
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
            'is_published' => $category->is_published,
            'quizzes_count' => $category->quizzes_count,
            'published_quizzes_count' => $category->published_quizzes_count,
            'questions_count' => (int) ($category->questions_count ?? Question::query()
                ->whereHas('quiz', fn ($query) => $query->where('category_id', $category->id))
                ->count()),
            'created_at' => $category->created_at?->toISOString(),
            'updated_at' => $category->updated_at?->toISOString(),
        ];
    }

    private function baseSlug(?string $slug, string $fallback, string $default): string
    {
        return Str::slug(filled($slug) ? $slug : $fallback) ?: $default;
    }

    private function uniqueSlug(string $baseSlug, ?int $ignoreId = null): string
    {
        $slug = $baseSlug;
        $suffix = 2;

        while (Category::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
