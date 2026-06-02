<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

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
            ])
            ->get();

        $user = $request->user();
        $attempts = $this->completedAttemptsFor($user, $quizzes->pluck('id'));
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
                'user_progress' => $user
                    ? $this->quizUserProgress($attemptsByQuiz->get($quiz->id, collect()))
                    : null,
            ])
            ->values();

        return response()->json([
            'data' => [
                'category' => $this->categoryPayload($category),
                'quizzes' => $quizPayloads,
                'user_progress' => $this->categoryUserProgress($user, $quizzes, $attempts),
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

    private function completedAttemptsFor(?User $user, Collection $quizIds): Collection
    {
        if (! $user || $quizIds->isEmpty()) {
            return collect();
        }

        return QuizAttempt::query()
            ->where('user_id', $user->id)
            ->whereIn('quiz_id', $quizIds->all())
            ->whereNotNull('completed_at')
            ->orderByDesc('completed_at')
            ->orderByDesc('id')
            ->get();
    }

    private function categoryUserProgress(?User $user, Collection $quizzes, Collection $attempts): array
    {
        $totalQuizzes = $quizzes->count();

        if (! $user) {
            return [
                'is_authenticated' => false,
                'completed_quizzes' => 0,
                'total_quizzes' => $totalQuizzes,
                'progress_percentage' => 0,
                'message' => 'Log in to track your progress',
            ];
        }

        $completedQuizzes = $attempts->pluck('quiz_id')->unique()->count();

        return [
            'is_authenticated' => true,
            'completed_quizzes' => $completedQuizzes,
            'total_quizzes' => $totalQuizzes,
            'progress_percentage' => $totalQuizzes > 0
                ? $this->percentageOrZero(($completedQuizzes / $totalQuizzes) * 100)
                : 0,
            'total_attempts' => $attempts->count(),
            'best_percentage' => $attempts->isEmpty()
                ? null
                : $this->percentageOrZero($attempts->max(fn (QuizAttempt $attempt): float => (float) $attempt->percentage)),
            'average_percentage' => $attempts->isEmpty()
                ? null
                : $this->percentageOrZero($attempts->avg(fn (QuizAttempt $attempt): float => (float) $attempt->percentage)),
            'total_points' => $attempts->sum('score'),
        ];
    }

    private function quizUserProgress(Collection $attempts): array
    {
        $lastAttempt = $attempts->first();

        return [
            'attempts_count' => $attempts->count(),
            'best_percentage' => $attempts->isEmpty()
                ? null
                : $this->percentageOrZero($attempts->max(fn (QuizAttempt $attempt): float => (float) $attempt->percentage)),
            'last_attempted_at' => $lastAttempt?->completed_at?->toISOString(),
            'completed' => $attempts->isNotEmpty(),
        ];
    }

    private function isQuizLocked($quiz, ?User $user): bool
    {
        return ! $user && ! $quiz->is_demo;
    }

    private function percentageOrZero(mixed $value): float
    {
        return round((float) ($value ?? 0), 1);
    }
}
