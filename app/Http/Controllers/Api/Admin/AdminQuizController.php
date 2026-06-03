<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminQuizController extends Controller
{
    public function index(): JsonResponse
    {
        $quizzes = $this->quizListQuery()
            ->get()
            ->map(fn (Quiz $quiz): array => $this->quizPayload($quiz))
            ->values();

        return response()->json(['data' => $quizzes]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validatedData($request);
        $quiz = Quiz::create($this->quizAttributes($validated));

        return response()->json([
            'data' => $this->quizPayload($this->quizWithDetails($quiz)),
        ], 201);
    }

    public function show(Quiz $quiz): JsonResponse
    {
        return response()->json([
            'data' => $this->quizPayload($this->quizWithDetails($quiz)),
        ]);
    }

    public function update(Request $request, Quiz $quiz): JsonResponse
    {
        $validated = $this->validatedData($request);
        $quiz->update($this->quizAttributes($validated, $quiz));

        return response()->json([
            'data' => $this->quizPayload($this->quizWithDetails($quiz)),
        ]);
    }

    public function destroy(Quiz $quiz): JsonResponse
    {
        if ($quiz->questions()->exists() || $quiz->attempts()->exists()) {
            throw ValidationException::withMessages([
                'quiz' => 'This quiz already has questions or attempts. Unpublish it instead.',
            ]);
        }

        $quiz->delete();

        return response()->json([
            'message' => 'Quiz deleted.',
        ]);
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'lesson_id' => ['nullable', 'integer', 'exists:lessons,id'],
            'title_en' => ['required', 'string', 'max:255'],
            'title_mk' => ['nullable', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'description_en' => ['nullable', 'string'],
            'description_mk' => ['nullable', 'string'],
            'difficulty' => ['required', Rule::in(['beginner', 'intermediate', 'advanced'])],
            'estimated_minutes' => ['nullable', 'integer', 'min:1', 'max:300'],
            'points_per_question' => ['required', 'integer', 'min:1', 'max:100'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_published' => ['sometimes', 'boolean'],
            'is_demo' => ['sometimes', 'boolean'],
        ]);
    }

    private function quizAttributes(array $validated, ?Quiz $quiz = null): array
    {
        $baseSlug = $this->baseSlug($validated['slug'] ?? null, $validated['title_en'], 'quiz');

        return [
            'category_id' => $validated['category_id'],
            'lesson_id' => $validated['lesson_id'] ?? null,
            'title_en' => $validated['title_en'],
            'title_mk' => filled($validated['title_mk'] ?? null) ? $validated['title_mk'] : $validated['title_en'],
            'slug' => $this->uniqueSlug($baseSlug, $quiz?->id),
            'description_en' => $validated['description_en'] ?? null,
            'description_mk' => $validated['description_mk'] ?? null,
            'difficulty' => $validated['difficulty'],
            'estimated_minutes' => $validated['estimated_minutes'] ?? null,
            'points_per_question' => $validated['points_per_question'],
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_published' => array_key_exists('is_published', $validated)
                ? (bool) $validated['is_published']
                : (bool) ($quiz?->is_published ?? false),
            'is_demo' => array_key_exists('is_demo', $validated)
                ? (bool) $validated['is_demo']
                : (bool) ($quiz?->is_demo ?? false),
        ];
    }

    private function quizListQuery()
    {
        return Quiz::query()
            ->select('quizzes.*')
            ->join('categories', 'categories.id', '=', 'quizzes.category_id')
            ->with(['category', 'lesson.category'])
            ->withCount([
                'questions as questions_count',
                'questions as published_questions_count' => fn ($query) => $query->published(),
                'questions as sound_questions_count' => fn ($query) => $query->where('question_type', 'sound_choice'),
                'attempts as attempts_count' => fn ($query) => $query->whereNotNull('completed_at'),
            ])
            ->selectSub(function ($query): void {
                $query->from('quiz_attempts')
                    ->selectRaw('avg(percentage)')
                    ->whereColumn('quiz_attempts.quiz_id', 'quizzes.id')
                    ->whereNotNull('completed_at');
            }, 'average_percentage')
            ->orderBy('categories.sort_order')
            ->orderBy('categories.name_en')
            ->orderBy('quizzes.sort_order')
            ->orderBy('quizzes.title_en');
    }

    private function quizWithDetails(Quiz $quiz): Quiz
    {
        return $this->quizListQuery()
            ->where('quizzes.id', $quiz->id)
            ->firstOrFail();
    }

    private function quizPayload(Quiz $quiz): array
    {
        return [
            'id' => $quiz->id,
            'category_id' => $quiz->category_id,
            'category_name_en' => $quiz->category->name_en,
            'category_slug' => $quiz->category->slug,
            'lesson_id' => $quiz->lesson_id,
            'lesson_title_en' => $quiz->lesson?->title_en,
            'lesson_slug' => $quiz->lesson?->slug,
            'lesson_category_slug' => $quiz->lesson?->category?->slug,
            'related_lesson' => $quiz->lesson ? [
                'id' => $quiz->lesson->id,
                'title_en' => $quiz->lesson->title_en,
                'title_mk' => $quiz->lesson->title_mk,
                'slug' => $quiz->lesson->slug,
                'category_slug' => $quiz->lesson->category?->slug,
                'is_published' => $quiz->lesson->is_published,
                'is_demo' => $quiz->lesson->is_demo,
            ] : null,
            'title_en' => $quiz->title_en,
            'title_mk' => $quiz->title_mk,
            'slug' => $quiz->slug,
            'description_en' => $quiz->description_en,
            'description_mk' => $quiz->description_mk,
            'difficulty' => $quiz->difficulty,
            'estimated_minutes' => $quiz->estimated_minutes,
            'points_per_question' => $quiz->points_per_question,
            'sort_order' => $quiz->sort_order,
            'is_published' => $quiz->is_published,
            'is_demo' => $quiz->is_demo,
            'questions_count' => $quiz->questions_count,
            'published_questions_count' => $quiz->published_questions_count,
            'sound_questions_count' => $quiz->sound_questions_count,
            'has_sound_questions' => (int) $quiz->sound_questions_count > 0,
            'attempts_count' => $quiz->attempts_count,
            'average_percentage' => $quiz->average_percentage === null ? null : round((float) $quiz->average_percentage, 1),
            'created_at' => $quiz->created_at?->toISOString(),
            'updated_at' => $quiz->updated_at?->toISOString(),
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

        while (Quiz::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
