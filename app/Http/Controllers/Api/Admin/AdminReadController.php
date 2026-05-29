<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class AdminReadController extends Controller
{
    public function overview(): JsonResponse
    {
        $completedAttempts = QuizAttempt::query()->whereNotNull('completed_at');
        $totalAttempts = (clone $completedAttempts)->count();
        $passedAttempts = (clone $completedAttempts)->where('passed', true)->count();

        return response()->json([
            'data' => [
                'totals' => [
                    'total_users' => User::query()->count(),
                    'total_categories' => Category::query()->count(),
                    'total_published_categories' => Category::query()->published()->count(),
                    'total_quizzes' => Quiz::query()->count(),
                    'total_published_quizzes' => Quiz::query()->published()->count(),
                    'total_questions' => Question::query()->count(),
                    'total_published_questions' => Question::query()->published()->count(),
                    'total_attempts' => $totalAttempts,
                    'average_score' => $this->percentageOrZero((clone $completedAttempts)->avg('percentage')),
                    'pass_rate' => $totalAttempts > 0 ? round(($passedAttempts / $totalAttempts) * 100, 1) : 0,
                ],
                'recent_attempts' => $this->recentAttempts(8),
                'most_popular_quizzes' => $this->popularQuizzes(),
                'quick_counts_by_category' => $this->categoryQuickCounts(),
            ],
        ]);
    }

    public function categories(): JsonResponse
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
            ->map(fn (Category $category): array => [
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
                'questions_count' => (int) $category->questions_count,
                'created_at' => $category->created_at?->toISOString(),
                'updated_at' => $category->updated_at?->toISOString(),
            ])
            ->values();

        return response()->json(['data' => $categories]);
    }

    public function quizzes(): JsonResponse
    {
        $quizzes = Quiz::query()
            ->select('quizzes.*')
            ->join('categories', 'categories.id', '=', 'quizzes.category_id')
            ->with('category')
            ->withCount([
                'questions as questions_count',
                'questions as published_questions_count' => fn ($query) => $query->published(),
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
            ->orderBy('quizzes.title_en')
            ->get()
            ->map(fn (Quiz $quiz): array => [
                'id' => $quiz->id,
                'category_id' => $quiz->category_id,
                'category_name_en' => $quiz->category->name_en,
                'category_slug' => $quiz->category->slug,
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
                'questions_count' => $quiz->questions_count,
                'published_questions_count' => $quiz->published_questions_count,
                'attempts_count' => $quiz->attempts_count,
                'average_percentage' => $quiz->average_percentage === null ? null : $this->percentageOrZero($quiz->average_percentage),
                'created_at' => $quiz->created_at?->toISOString(),
                'updated_at' => $quiz->updated_at?->toISOString(),
            ])
            ->values();

        return response()->json(['data' => $quizzes]);
    }

    public function questions(): JsonResponse
    {
        $questions = Question::query()
            ->select('questions.*')
            ->join('quizzes', 'quizzes.id', '=', 'questions.quiz_id')
            ->join('categories', 'categories.id', '=', 'quizzes.category_id')
            ->with([
                'quiz.category',
                'correctAnswer',
                'answers' => fn ($query) => $query->ordered(),
            ])
            ->withCount([
                'answers as answers_count',
                'attemptAnswers as attempt_answers_count',
            ])
            ->orderBy('categories.sort_order')
            ->orderBy('categories.name_en')
            ->orderBy('quizzes.sort_order')
            ->orderBy('quizzes.title_en')
            ->orderBy('questions.sort_order')
            ->orderBy('questions.id')
            ->get()
            ->map(fn (Question $question): array => [
                'id' => $question->id,
                'quiz_id' => $question->quiz_id,
                'quiz_title_en' => $question->quiz->title_en,
                'quiz_slug' => $question->quiz->slug,
                'category_name_en' => $question->quiz->category->name_en,
                'category_slug' => $question->quiz->category->slug,
                'question_en' => $question->question_en,
                'question_mk' => $question->question_mk,
                'explanation_en' => $question->explanation_en,
                'explanation_mk' => $question->explanation_mk,
                'sort_order' => $question->sort_order,
                'points' => $question->points,
                'is_published' => $question->is_published,
                'answers_count' => $question->answers_count,
                'attempt_answers_count' => $question->attempt_answers_count,
                'used_in_attempts' => $question->attempt_answers_count > 0,
                'correct_answer_en' => $question->correctAnswer?->answer_en,
                'correct_answer_mk' => $question->correctAnswer?->answer_mk,
                'answers' => $question->answers->map(fn ($answer): array => [
                    'id' => $answer->id,
                    'answer_en' => $answer->answer_en,
                    'answer_mk' => $answer->answer_mk,
                    'sort_order' => $answer->sort_order,
                    'is_correct' => $answer->is_correct,
                ])->values(),
                'created_at' => $question->created_at?->toISOString(),
                'updated_at' => $question->updated_at?->toISOString(),
            ])
            ->values();

        return response()->json(['data' => $questions]);
    }

    public function attempts(): JsonResponse
    {
        return response()->json([
            'data' => $this->recentAttempts(50),
        ]);
    }

    private function recentAttempts(int $limit): array
    {
        return QuizAttempt::query()
            ->whereNotNull('completed_at')
            ->with(['user', 'quiz.category'])
            ->orderByDesc('completed_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->map(fn (QuizAttempt $attempt): array => [
                'id' => $attempt->id,
                'user_name' => $attempt->user->name,
                'user_email' => $attempt->user->email,
                'quiz_title_en' => $attempt->quiz->title_en,
                'quiz_slug' => $attempt->quiz->slug,
                'category_name_en' => $attempt->quiz->category->name_en,
                'category_slug' => $attempt->quiz->category->slug,
                'score' => $attempt->score,
                'total_questions' => $attempt->total_questions,
                'correct_answers' => $attempt->correct_answers,
                'percentage' => $this->percentageOrZero($attempt->percentage),
                'passed' => $attempt->passed,
                'completed_at' => $attempt->completed_at?->toISOString(),
            ])
            ->values()
            ->all();
    }

    private function popularQuizzes(): array
    {
        return Quiz::query()
            ->select('quizzes.*')
            ->with('category')
            ->withCount([
                'attempts as attempts_count' => fn ($query) => $query->whereNotNull('completed_at'),
            ])
            ->selectSub(function ($query): void {
                $query->from('quiz_attempts')
                    ->selectRaw('avg(percentage)')
                    ->whereColumn('quiz_attempts.quiz_id', 'quizzes.id')
                    ->whereNotNull('completed_at');
            }, 'average_percentage')
            ->orderByDesc('attempts_count')
            ->orderBy('title_en')
            ->limit(5)
            ->get()
            ->map(fn (Quiz $quiz): array => [
                'id' => $quiz->id,
                'title_en' => $quiz->title_en,
                'slug' => $quiz->slug,
                'category_name_en' => $quiz->category->name_en,
                'attempts_count' => $quiz->attempts_count,
                'average_percentage' => $quiz->average_percentage === null ? null : $this->percentageOrZero($quiz->average_percentage),
            ])
            ->values()
            ->all();
    }

    private function categoryQuickCounts(): array
    {
        return Category::query()
            ->ordered()
            ->select('id', 'name_en', 'slug')
            ->selectSub(function ($query): void {
                $query->from('quizzes')
                    ->selectRaw('count(*)')
                    ->whereColumn('quizzes.category_id', 'categories.id');
            }, 'quiz_count')
            ->selectSub(function ($query): void {
                $query->from('questions')
                    ->join('quizzes', 'questions.quiz_id', '=', 'quizzes.id')
                    ->selectRaw('count(*)')
                    ->whereColumn('quizzes.category_id', 'categories.id');
            }, 'question_count')
            ->selectSub(function ($query): void {
                $query->from('quiz_attempts')
                    ->join('quizzes', 'quiz_attempts.quiz_id', '=', 'quizzes.id')
                    ->selectRaw('count(*)')
                    ->whereColumn('quizzes.category_id', 'categories.id')
                    ->whereNotNull('quiz_attempts.completed_at');
            }, 'attempt_count')
            ->get()
            ->map(fn (Category $category): array => [
                'id' => $category->id,
                'name_en' => $category->name_en,
                'slug' => $category->slug,
                'quiz_count' => (int) $category->quiz_count,
                'question_count' => (int) $category->question_count,
                'attempt_count' => (int) $category->attempt_count,
            ])
            ->values()
            ->all();
    }

    private function percentageOrZero(mixed $value): float
    {
        return round((float) ($value ?? 0), 1);
    }
}
