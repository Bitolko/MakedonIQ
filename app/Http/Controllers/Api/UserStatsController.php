<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class UserStatsController extends Controller
{
    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();
        $attempts = $this->completedAttemptsFor($user);

        return response()->json([
            'data' => [
                'user' => $this->userPayload($user),
                'total_points' => $attempts->sum('score'),
                'completed_quizzes_count' => $this->completedQuizCount($attempts),
                'total_attempts_count' => $attempts->count(),
                'passed_attempts_count' => $attempts->where('passed', true)->count(),
                'current_streak' => $this->currentStreak($attempts),
                'average_percentage' => $this->percentageOrZero($attempts->avg(fn (QuizAttempt $attempt): float => (float) $attempt->percentage)),
                'best_percentage' => $this->percentageOrZero($attempts->max(fn (QuizAttempt $attempt): float => (float) $attempt->percentage)),
                'recent_attempts' => $attempts
                    ->take(5)
                    ->map(fn (QuizAttempt $attempt): array => $this->recentAttemptPayload($attempt))
                    ->values(),
                'recommended_quizzes' => $this->recommendedQuizzes($attempts),
                'category_progress' => $this->categoryProgress($attempts),
            ],
        ]);
    }

    public function progress(Request $request): JsonResponse
    {
        $user = $request->user();
        $attempts = $this->completedAttemptsFor($user);
        $categoryProgress = $this->categoryProgress($attempts);

        return response()->json([
            'data' => [
                'overall' => [
                    'total_points' => $attempts->sum('score'),
                    'total_attempts' => $attempts->count(),
                    'completed_quizzes_count' => $this->completedQuizCount($attempts),
                    'average_percentage' => $this->percentageOrZero($attempts->avg(fn (QuizAttempt $attempt): float => (float) $attempt->percentage)),
                    'best_percentage' => $this->percentageOrZero($attempts->max(fn (QuizAttempt $attempt): float => (float) $attempt->percentage)),
                    'passed_attempts_count' => $attempts->where('passed', true)->count(),
                ],
                'category_progress' => $categoryProgress,
                'quiz_history' => $attempts
                    ->take(20)
                    ->map(fn (QuizAttempt $attempt): array => $this->quizHistoryPayload($attempt))
                    ->values(),
                'achievements' => $this->achievements($attempts),
                'score_trends' => $this->scoreTrends($attempts),
            ],
        ]);
    }

    private function completedAttemptsFor(User $user): Collection
    {
        return QuizAttempt::query()
            ->where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->with('quiz.category')
            ->orderByDesc('completed_at')
            ->orderByDesc('id')
            ->get();
    }

    private function userPayload(User $user): array
    {
        return [
            'name' => $user->name,
            'email' => $user->email,
        ];
    }

    private function completedQuizCount(Collection $attempts): int
    {
        return $attempts->pluck('quiz_id')->unique()->count();
    }

    private function recentAttemptPayload(QuizAttempt $attempt): array
    {
        $quiz = $attempt->quiz;
        $category = $quiz?->category;

        return [
            'id' => $attempt->id,
            'quiz_title' => $quiz?->title_en ?? 'Untitled quiz',
            'category_name' => $category?->name_en ?? 'Uncategorized',
            'score' => $attempt->score,
            'total_questions' => $attempt->total_questions,
            'correct_answers' => $attempt->correct_answers,
            'percentage' => $this->percentageOrZero($attempt->percentage),
            'passed' => $attempt->passed,
            'completed_at' => $attempt->completed_at?->toISOString(),
            'result_url' => $this->resultUrl($attempt),
        ];
    }

    private function quizHistoryPayload(QuizAttempt $attempt): array
    {
        $quiz = $attempt->quiz;
        $category = $quiz?->category;

        return [
            'id' => $attempt->id,
            'quiz_title' => $quiz?->title_en ?? 'Untitled quiz',
            'category' => $category?->name_en ?? 'Uncategorized',
            'score' => $attempt->score,
            'percentage' => $this->percentageOrZero($attempt->percentage),
            'passed' => $attempt->passed,
            'completed_at' => $attempt->completed_at?->toISOString(),
            'result_url' => $this->resultUrl($attempt),
        ];
    }

    private function resultUrl(QuizAttempt $attempt): ?string
    {
        $quiz = $attempt->quiz;
        $category = $quiz?->category;

        if (! $quiz || ! $category) {
            return null;
        }

        return "/quizzes/{$category->slug}/{$quiz->slug}/results/{$attempt->id}";
    }

    private function recommendedQuizzes(Collection $attempts): Collection
    {
        $attemptedQuizIds = $attempts->pluck('quiz_id')->unique()->values()->all();

        $recommended = $this->publishedQuizQuery()
            ->when($attemptedQuizIds !== [], fn ($query) => $query->whereNotIn('id', $attemptedQuizIds))
            ->ordered()
            ->limit(4)
            ->get();

        if ($recommended->isEmpty()) {
            $recommended = $this->publishedQuizQuery()
                ->latest()
                ->limit(4)
                ->get();
        }

        return $recommended
            ->map(fn (Quiz $quiz): array => $this->recommendedQuizPayload($quiz))
            ->values();
    }

    private function publishedQuizQuery()
    {
        return Quiz::query()
            ->published()
            ->whereHas('category', fn ($query) => $query->published())
            ->with('category')
            ->withCount([
                'questions as questions_count' => fn ($query) => $query->published(),
            ]);
    }

    private function recommendedQuizPayload(Quiz $quiz): array
    {
        return [
            'id' => $quiz->id,
            'title' => $quiz->title_en,
            'slug' => $quiz->slug,
            'category_name' => $quiz->category->name_en,
            'category_slug' => $quiz->category->slug,
            'difficulty' => $quiz->difficulty,
            'estimated_minutes' => $quiz->estimated_minutes,
            'questions_count' => $quiz->questions_count,
            'start_url' => "/quizzes/{$quiz->category->slug}/{$quiz->slug}/start",
        ];
    }

    private function categoryProgress(Collection $attempts): Collection
    {
        $categories = Category::query()
            ->published()
            ->ordered()
            ->with([
                'quizzes' => fn ($query) => $query
                    ->published()
                    ->select('id', 'category_id', 'title_en', 'slug', 'is_published', 'sort_order'),
            ])
            ->get();

        return $categories
            ->map(function (Category $category) use ($attempts): array {
                $publishedQuizIds = $category->quizzes->pluck('id');
                $categoryAttempts = $attempts->filter(
                    fn (QuizAttempt $attempt): bool => $publishedQuizIds->contains($attempt->quiz_id),
                );
                $totalPublishedQuizzes = $publishedQuizIds->count();
                $completedQuizzes = $categoryAttempts->pluck('quiz_id')->unique()->count();

                return [
                    'name' => $category->name_en,
                    'slug' => $category->slug,
                    'icon' => $category->icon,
                    'total_published_quizzes' => $totalPublishedQuizzes,
                    'completed_quizzes' => $completedQuizzes,
                    'best_percentage' => $categoryAttempts->isEmpty()
                        ? null
                        : $this->percentageOrZero($categoryAttempts->max(fn (QuizAttempt $attempt): float => (float) $attempt->percentage)),
                    'progress_percentage' => $totalPublishedQuizzes > 0
                        ? round(($completedQuizzes / $totalPublishedQuizzes) * 100, 1)
                        : 0,
                ];
            })
            ->values();
    }

    private function achievements(Collection $attempts): array
    {
        $firstAttempt = $attempts->sortBy('completed_at')->first();
        $firstPassedAttempt = $attempts
            ->filter(fn (QuizAttempt $attempt): bool => (float) $attempt->percentage >= 70)
            ->sortBy('completed_at')
            ->first();
        $perfectAttempt = $attempts
            ->filter(fn (QuizAttempt $attempt): bool => (float) $attempt->percentage >= 100)
            ->sortBy('completed_at')
            ->first();
        $threeQuizDate = $this->distinctQuizMilestoneDate($attempts, 3);
        $explorerDate = $this->distinctCategoryMilestoneDate($attempts, 3);

        return [
            [
                'title' => 'First Quiz Completed',
                'description' => 'Completed your first MakedonIQ quiz.',
                'icon' => '01',
                'earned' => (bool) $firstAttempt,
                'earned_at' => $firstAttempt?->completed_at?->toISOString(),
            ],
            [
                'title' => 'Scored 70%+',
                'description' => 'Passed a quiz with a score of at least 70%.',
                'icon' => '70',
                'earned' => (bool) $firstPassedAttempt,
                'earned_at' => $firstPassedAttempt?->completed_at?->toISOString(),
            ],
            [
                'title' => 'Perfect Score',
                'description' => 'Answered every question correctly in a quiz.',
                'icon' => '100',
                'earned' => (bool) $perfectAttempt,
                'earned_at' => $perfectAttempt?->completed_at?->toISOString(),
            ],
            [
                'title' => 'Completed 3 Quizzes',
                'description' => 'Completed three different quizzes.',
                'icon' => '03',
                'earned' => (bool) $threeQuizDate,
                'earned_at' => $threeQuizDate,
            ],
            [
                'title' => 'Macedonian Explorer',
                'description' => 'Completed quizzes in three different categories.',
                'icon' => 'MK',
                'earned' => (bool) $explorerDate,
                'earned_at' => $explorerDate,
            ],
        ];
    }

    private function distinctQuizMilestoneDate(Collection $attempts, int $threshold): ?string
    {
        $seenQuizIds = [];

        foreach ($attempts->sortBy('completed_at') as $attempt) {
            $seenQuizIds[$attempt->quiz_id] = true;

            if (count($seenQuizIds) >= $threshold) {
                return $attempt->completed_at?->toISOString();
            }
        }

        return null;
    }

    private function distinctCategoryMilestoneDate(Collection $attempts, int $threshold): ?string
    {
        $seenCategoryIds = [];

        foreach ($attempts->sortBy('completed_at') as $attempt) {
            $categoryId = $attempt->quiz?->category_id;

            if (! $categoryId) {
                continue;
            }

            $seenCategoryIds[$categoryId] = true;

            if (count($seenCategoryIds) >= $threshold) {
                return $attempt->completed_at?->toISOString();
            }
        }

        return null;
    }

    private function scoreTrends(Collection $attempts): Collection
    {
        return $attempts
            ->take(10)
            ->reverse()
            ->map(fn (QuizAttempt $attempt): array => [
                'id' => $attempt->id,
                'quiz_title' => $attempt->quiz?->title_en ?? 'Untitled quiz',
                'percentage' => $this->percentageOrZero($attempt->percentage),
                'completed_at' => $attempt->completed_at?->toISOString(),
            ])
            ->values();
    }

    private function currentStreak(Collection $attempts): int
    {
        $dates = $attempts
            ->pluck('completed_at')
            ->filter()
            ->map(fn ($date) => $date->copy()->startOfDay())
            ->unique(fn ($date): string => $date->toDateString())
            ->sortDesc()
            ->values();

        if ($dates->isEmpty()) {
            return 0;
        }

        $today = now()->startOfDay();
        $firstDate = $dates->first();

        if (! $firstDate->isSameDay($today) && ! $firstDate->isSameDay($today->copy()->subDay())) {
            return 0;
        }

        $streak = 0;
        $expectedDate = $firstDate->copy();

        foreach ($dates as $date) {
            if (! $date->isSameDay($expectedDate)) {
                break;
            }

            $streak++;
            $expectedDate->subDay();
        }

        return $streak;
    }

    private function percentageOrZero(mixed $value): float
    {
        return round((float) ($value ?? 0), 1);
    }
}
