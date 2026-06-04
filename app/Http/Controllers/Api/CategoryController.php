<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\User;
use App\Services\LearnerProgressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(private LearnerProgressService $progress) {}

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

    public function show(Request $request, string $slug): JsonResponse
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
                'questions as map_questions_count' => fn ($query) => $query->published()->where('question_type', 'map_guess'),
                'questions as picture_questions_count' => fn ($query) => $query->published()->where('question_type', 'picture_choice'),
                'questions as sound_questions_count' => fn ($query) => $query->published()->where('question_type', 'sound_choice'),
            ])
            ->get();

        $user = $request->user();
        $attempts = $user
            ? $this->progress->completedAttemptsFor($user, $quizzes->pluck('id'), [])
            : collect();
        $attemptsByQuiz = $attempts->groupBy('quiz_id');

        $quizPayloads = $quizzes
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
                'is_demo' => $quiz->is_demo,
                'is_locked' => $this->isQuizLocked($quiz, $user),
                'questions_count' => $quiz->questions_count,
                'map_questions_count' => $quiz->map_questions_count,
                'has_map_questions' => (int) $quiz->map_questions_count > 0,
                'picture_questions_count' => $quiz->picture_questions_count,
                'has_picture_questions' => (int) $quiz->picture_questions_count > 0,
                'sound_questions_count' => $quiz->sound_questions_count,
                'has_sound_questions' => (int) $quiz->sound_questions_count > 0,
                'user_progress' => $user
                    ? $this->progress->quizUserProgress($attemptsByQuiz->get($quiz->id, collect()))
                    : null,
            ])
            ->values();

        return response()->json([
            'data' => [
                'category' => $this->categoryPayload($category),
                'quizzes' => $quizPayloads,
                'user_progress' => $this->progress->categoryUserProgress($user, $quizzes, $attempts),
            ],
        ]);
    }

    public function quizzes(Request $request, string $slug): JsonResponse
    {
        return $this->show($request, $slug);
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

    private function isQuizLocked($quiz, ?User $user): bool
    {
        return ! $user && ! $quiz->is_demo;
    }
}
