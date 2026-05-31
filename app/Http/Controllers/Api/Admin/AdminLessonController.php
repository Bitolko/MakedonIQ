<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminLessonController extends Controller
{
    public function index(): JsonResponse
    {
        $lessons = $this->lessonListQuery()
            ->get()
            ->map(fn (Lesson $lesson): array => $this->lessonPayload($lesson))
            ->values();

        return response()->json(['data' => $lessons]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validatedData($request);
        $lesson = Lesson::create($this->lessonAttributes($validated));

        return response()->json([
            'data' => $this->lessonPayload($this->lessonWithDetails($lesson)),
        ], 201);
    }

    public function show(Lesson $lesson): JsonResponse
    {
        return response()->json([
            'data' => $this->lessonPayload($this->lessonWithDetails($lesson)),
        ]);
    }

    public function update(Request $request, Lesson $lesson): JsonResponse
    {
        $validated = $this->validatedData($request);
        $lesson->update($this->lessonAttributes($validated, $lesson));

        return response()->json([
            'data' => $this->lessonPayload($this->lessonWithDetails($lesson)),
        ]);
    }

    public function destroy(Lesson $lesson): JsonResponse
    {
        if ($lesson->quizzes()->exists()) {
            throw ValidationException::withMessages([
                'lesson' => 'This lesson is linked to quizzes. Unlink or update those quizzes first, or unpublish the lesson instead.',
            ]);
        }

        $lesson->delete();

        return response()->json([
            'message' => 'Lesson deleted.',
        ]);
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'title_en' => ['required', 'string', 'max:255'],
            'title_mk' => ['nullable', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'summary_en' => ['nullable', 'string'],
            'summary_mk' => ['nullable', 'string'],
            'content_en' => ['required', 'string'],
            'content_mk' => ['nullable', 'string'],
            'difficulty' => ['required', Rule::in(['beginner', 'intermediate', 'advanced'])],
            'estimated_minutes' => ['nullable', 'integer', 'min:1', 'max:300'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_published' => ['sometimes', 'boolean'],
        ]);
    }

    private function lessonAttributes(array $validated, ?Lesson $lesson = null): array
    {
        $baseSlug = $this->baseSlug($validated['slug'] ?? null, $validated['title_en'], 'lesson');

        return [
            'category_id' => $validated['category_id'],
            'title_en' => $validated['title_en'],
            'title_mk' => filled($validated['title_mk'] ?? null) ? $validated['title_mk'] : $validated['title_en'],
            'slug' => $this->uniqueSlug($baseSlug, $lesson?->id),
            'summary_en' => $validated['summary_en'] ?? null,
            'summary_mk' => $validated['summary_mk'] ?? null,
            'content_en' => $validated['content_en'],
            'content_mk' => $validated['content_mk'] ?? null,
            'difficulty' => $validated['difficulty'],
            'estimated_minutes' => $validated['estimated_minutes'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_published' => array_key_exists('is_published', $validated)
                ? (bool) $validated['is_published']
                : (bool) ($lesson?->is_published ?? false),
        ];
    }

    private function lessonListQuery()
    {
        return Lesson::query()
            ->select('lessons.*')
            ->join('categories', 'categories.id', '=', 'lessons.category_id')
            ->with('category')
            ->withCount('quizzes as linked_quizzes_count')
            ->orderBy('categories.sort_order')
            ->orderBy('categories.name_en')
            ->orderBy('lessons.sort_order')
            ->orderBy('lessons.title_en');
    }

    private function lessonWithDetails(Lesson $lesson): Lesson
    {
        return $this->lessonListQuery()
            ->where('lessons.id', $lesson->id)
            ->firstOrFail();
    }

    private function lessonPayload(Lesson $lesson): array
    {
        return [
            'id' => $lesson->id,
            'category_id' => $lesson->category_id,
            'category_name_en' => $lesson->category->name_en,
            'category_slug' => $lesson->category->slug,
            'title_en' => $lesson->title_en,
            'title_mk' => $lesson->title_mk,
            'slug' => $lesson->slug,
            'summary_en' => $lesson->summary_en,
            'summary_mk' => $lesson->summary_mk,
            'content_en' => $lesson->content_en,
            'content_mk' => $lesson->content_mk,
            'difficulty' => $lesson->difficulty,
            'estimated_minutes' => $lesson->estimated_minutes,
            'sort_order' => $lesson->sort_order,
            'is_published' => $lesson->is_published,
            'linked_quizzes_count' => $lesson->linked_quizzes_count,
            'created_at' => $lesson->created_at?->toISOString(),
            'updated_at' => $lesson->updated_at?->toISOString(),
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

        while (Lesson::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
