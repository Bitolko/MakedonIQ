<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use App\Services\LearnerProgressService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class UserStatsController extends Controller
{
    public function __construct(private LearnerProgressService $progress) {}

    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();
        $attempts = $this->progress->completedAttemptsFor($user);

        return response()->json([
            'data' => [
                'user' => $this->userPayload($user),
                'summary' => $this->progress->summaryPayload($attempts, totalAttemptsKey: 'total_attempts_count'),
                'recent_attempts' => $attempts
                    ->take(5)
                    ->map(fn (QuizAttempt $attempt): array => $this->attemptPayload($attempt))
                    ->values(),
                'recommended_quizzes' => $this->recommendedQuizzes($attempts),
                'category_progress' => $this->progress->categoryProgress($attempts),
            ],
        ]);
    }

    public function progress(Request $request): JsonResponse
    {
        $user = $request->user();
        $attempts = $this->progress->completedAttemptsFor($user);
        $categoryProgress = $this->progress->categoryProgress($attempts);

        return response()->json([
            'data' => [
                'overall' => $this->progress->summaryPayload($attempts, totalAttemptsKey: 'total_attempts'),
                'category_progress' => $categoryProgress,
                'quiz_history' => $attempts
                    ->take(20)
                    ->map(fn (QuizAttempt $attempt): array => $this->attemptPayload($attempt))
                    ->values(),
                'achievements' => $this->achievements($attempts),
                'score_trends' => $this->scoreTrends($attempts),
            ],
        ]);
    }

    private function userPayload(User $user): array
    {
        return [
            'name' => $user->name,
            'email' => $user->email,
        ];
    }

    private function attemptPayload(QuizAttempt $attempt): array
    {
        $quiz = $attempt->quiz;
        $category = $quiz?->category;

        return [
            'id' => $attempt->id,
            'quiz_title_en' => $quiz?->title_en ?? 'Untitled quiz',
            'quiz_title_mk' => $quiz?->title_mk,
            'quiz_slug' => $quiz?->slug,
            'category_name_en' => $category?->name_en ?? 'Uncategorized',
            'category_name_mk' => $category?->name_mk,
            'category_slug' => $category?->slug,
            'score' => $attempt->score,
            'total_questions' => $attempt->total_questions,
            'correct_answers' => $attempt->correct_answers,
            'percentage' => $this->progress->percentageOrZero($attempt->percentage),
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

    private function publishedQuizQuery(): Builder
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
            'title_en' => $quiz->title_en,
            'title_mk' => $quiz->title_mk,
            'slug' => $quiz->slug,
            'category_name_en' => $quiz->category->name_en,
            'category_name_mk' => $quiz->category->name_mk,
            'category_slug' => $quiz->category->slug,
            'difficulty' => $quiz->difficulty,
            'estimated_minutes' => $quiz->estimated_minutes,
            'question_count' => $quiz->questions_count,
            'start_url' => "/quizzes/{$quiz->category->slug}/{$quiz->slug}/start",
        ];
    }

    private function achievements(Collection $attempts): array
    {
        $completedQuizzesCount = $this->progress->completedQuizCount($attempts);
        $passedAttemptsCount = $attempts->where('passed', true)->count();
        $completedCategoryCount = $attempts
            ->map(fn (QuizAttempt $attempt) => $attempt->quiz?->category_id)
            ->filter()
            ->unique()
            ->count();

        return [
            [
                'key' => 'first_quiz_completed',
                'title' => 'First Quiz Completed',
                'description' => 'Completed your first MakedonIQ quiz.',
                'unlocked' => $attempts->count() >= 1,
            ],
            [
                'key' => 'passed_first_quiz',
                'title' => 'Passed First Quiz',
                'description' => 'Passed at least one quiz.',
                'unlocked' => $passedAttemptsCount >= 1,
            ],
            [
                'key' => 'perfect_score',
                'title' => 'Perfect Score',
                'description' => 'Answered every question correctly in a quiz.',
                'unlocked' => $attempts->contains(fn (QuizAttempt $attempt): bool => (float) $attempt->percentage >= 100),
            ],
            [
                'key' => 'completed_three_quizzes',
                'title' => 'Completed 3 Quizzes',
                'description' => 'Completed three different quizzes.',
                'unlocked' => $completedQuizzesCount >= 3,
            ],
            [
                'key' => 'macedonian_explorer',
                'title' => 'Macedonian Explorer',
                'description' => 'Completed quizzes in three different categories.',
                'unlocked' => $completedCategoryCount >= 3,
            ],
            [
                'key' => 'dedicated_learner',
                'title' => 'Dedicated Learner',
                'description' => 'Built a learning streak of at least three days.',
                'unlocked' => $this->progress->currentStreak($attempts) >= 3,
            ],
        ];
    }

    private function scoreTrends(Collection $attempts): Collection
    {
        return $attempts
            ->take(10)
            ->reverse()
            ->map(fn (QuizAttempt $attempt): array => [
                'attempt_id' => $attempt->id,
                'quiz_title_en' => $attempt->quiz?->title_en ?? 'Untitled quiz',
                'percentage' => $this->progress->percentageOrZero($attempt->percentage),
                'completed_at' => $attempt->completed_at?->toISOString(),
            ])
            ->values();
    }
}
