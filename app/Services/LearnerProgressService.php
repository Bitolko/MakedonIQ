<?php

namespace App\Services;

use App\Models\Category;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Support\Collection;

class LearnerProgressService
{
    public function completedAttemptsFor(User $user, ?Collection $quizIds = null, array $with = ['quiz.category']): Collection
    {
        if ($quizIds !== null && $quizIds->isEmpty()) {
            return collect();
        }

        return QuizAttempt::query()
            ->where('user_id', $user->id)
            ->when(
                $quizIds !== null,
                fn ($query) => $query->whereIn('quiz_id', $quizIds->values()->all()),
            )
            ->whereNotNull('completed_at')
            ->when($with !== [], fn ($query) => $query->with($with))
            ->orderByDesc('completed_at')
            ->orderByDesc('id')
            ->get();
    }

    public function summaryPayload(Collection $attempts, string $totalAttemptsKey): array
    {
        return [
            'total_points' => $this->totalBestPoints($attempts),
            'completed_quizzes_count' => $this->completedQuizCount($attempts),
            $totalAttemptsKey => $attempts->count(),
            'passed_attempts_count' => $attempts->where('passed', true)->count(),
            'current_streak' => $this->currentStreak($attempts),
            'average_percentage' => $this->percentageOrZero(
                $attempts->avg(fn (QuizAttempt $attempt): float => (float) $attempt->percentage),
            ),
            'best_percentage' => $this->percentageOrZero(
                $attempts->max(fn (QuizAttempt $attempt): float => (float) $attempt->percentage),
            ),
        ];
    }

    public function categoryProgress(Collection $attempts): Collection
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
                $categoryAttempts = $this->attemptsForQuizIds($attempts, $publishedQuizIds);
                $totalPublishedQuizzes = $publishedQuizIds->count();
                $completedQuizzes = $this->completedQuizCount($categoryAttempts);

                return [
                    'id' => $category->id,
                    'name_en' => $category->name_en,
                    'name_mk' => $category->name_mk,
                    'slug' => $category->slug,
                    'icon' => $category->icon,
                    'total_published_quizzes' => $totalPublishedQuizzes,
                    'completed_quizzes' => $completedQuizzes,
                    'progress_percentage' => $totalPublishedQuizzes > 0
                        ? round(($completedQuizzes / $totalPublishedQuizzes) * 100, 1)
                        : 0,
                    'best_percentage' => $this->nullableBestPercentage($categoryAttempts),
                    'total_points' => $this->totalBestPoints($categoryAttempts),
                ];
            })
            ->values();
    }

    public function categoryUserProgress(?User $user, Collection $quizzes, Collection $attempts): array
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

        $completedQuizzes = $this->completedQuizCount($attempts);

        return [
            'is_authenticated' => true,
            'completed_quizzes' => $completedQuizzes,
            'total_quizzes' => $totalQuizzes,
            'progress_percentage' => $totalQuizzes > 0
                ? $this->percentageOrZero(($completedQuizzes / $totalQuizzes) * 100)
                : 0,
            'total_attempts' => $attempts->count(),
            'best_percentage' => $this->nullableBestPercentage($attempts),
            'average_percentage' => $attempts->isEmpty()
                ? null
                : $this->percentageOrZero(
                    $attempts->avg(fn (QuizAttempt $attempt): float => (float) $attempt->percentage),
                ),
            'total_points' => $this->totalBestPoints($attempts),
        ];
    }

    public function quizUserProgress(Collection $attempts): array
    {
        $lastAttempt = $attempts->first();

        return [
            'attempts_count' => $attempts->count(),
            'best_percentage' => $this->nullableBestPercentage($attempts),
            'last_attempted_at' => $lastAttempt?->completed_at?->toISOString(),
            'completed' => $attempts->isNotEmpty(),
        ];
    }

    public function completedQuizCount(Collection $attempts): int
    {
        return $attempts->pluck('quiz_id')->unique()->count();
    }

    public function totalBestPoints(Collection $attempts): int
    {
        return (int) $attempts
            ->groupBy('quiz_id')
            ->sum(fn (Collection $quizAttempts): int => (int) $quizAttempts->max('score'));
    }

    public function nullableBestPercentage(Collection $attempts): ?float
    {
        if ($attempts->isEmpty()) {
            return null;
        }

        return $this->percentageOrZero(
            $attempts->max(fn (QuizAttempt $attempt): float => (float) $attempt->percentage),
        );
    }

    public function currentStreak(Collection $attempts): int
    {
        $dates = $attempts
            ->pluck('completed_at')
            ->filter()
            ->map(fn ($date) => $date->copy()->startOfDay())
            ->unique(fn ($date): string => $date->toDateString())
            ->sortByDesc(fn ($date): int => $date->getTimestamp())
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

    public function percentageOrZero(mixed $value): float
    {
        return round((float) ($value ?? 0), 1);
    }

    private function attemptsForQuizIds(Collection $attempts, Collection $quizIds): Collection
    {
        return $attempts->filter(
            fn (QuizAttempt $attempt): bool => $quizIds->contains($attempt->quiz_id),
        );
    }
}
