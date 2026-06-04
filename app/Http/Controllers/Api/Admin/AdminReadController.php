<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptAnswer;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
                'questions as map_questions_count' => fn ($query) => $query->where('question_type', 'map_guess'),
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
                'map_questions_count' => $quiz->map_questions_count,
                'has_map_questions' => (int) $quiz->map_questions_count > 0,
                'sound_questions_count' => $quiz->sound_questions_count,
                'has_sound_questions' => (int) $quiz->sound_questions_count > 0,
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
                'question_type' => $question->question_type,
                'translation_direction' => $question->translation_direction,
                'metadata' => $question->metadata,
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

    public function attempts(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'quiz_id' => ['nullable', 'integer', 'exists:quizzes,id'],
            'status' => ['nullable', 'in:passed,review'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:50'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $search = trim((string) ($validated['search'] ?? ''));
        $perPage = (int) ($validated['per_page'] ?? 20);

        $query = $this->completedAttemptsQuery()
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query
                        ->whereHas('user', function (Builder $query) use ($search): void {
                            $query->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        })
                        ->orWhereHas('quiz', function (Builder $query) use ($search): void {
                            $query->where('title_en', 'like', "%{$search}%")
                                ->orWhere('title_mk', 'like', "%{$search}%");
                        })
                        ->orWhereHas('quiz.category', function (Builder $query) use ($search): void {
                            $query->where('name_en', 'like', "%{$search}%")
                                ->orWhere('name_mk', 'like', "%{$search}%");
                        });
                });
            })
            ->when(
                isset($validated['category_id']),
                fn (Builder $query) => $query->whereHas(
                    'quiz',
                    fn (Builder $query) => $query->where('category_id', $validated['category_id']),
                ),
            )
            ->when(
                isset($validated['quiz_id']),
                fn (Builder $query) => $query->where('quiz_id', $validated['quiz_id']),
            )
            ->when(
                ($validated['status'] ?? null) === 'passed',
                fn (Builder $query) => $query->where('passed', true),
            )
            ->when(
                ($validated['status'] ?? null) === 'review',
                fn (Builder $query) => $query->where('passed', false),
            );

        $paginator = $query->paginate($perPage);

        return response()->json([
            'data' => collect($paginator->items())
                ->map(fn (QuizAttempt $attempt): array => $this->attemptListPayload($attempt))
                ->values(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
            'filters' => $this->attemptFilterOptions(),
        ]);
    }

    public function attempt(QuizAttempt $attempt): JsonResponse
    {
        abort_if($attempt->completed_at === null, 404);

        $attempt->load([
            'user',
            'quiz.category',
            'answers' => fn ($query) => $query->orderBy('id'),
            'answers.question.correctAnswer',
            'answers.selectedAnswer',
        ]);

        $learnerQuizAttempts = QuizAttempt::query()
            ->where('user_id', $attempt->user_id)
            ->where('quiz_id', $attempt->quiz_id)
            ->whereNotNull('completed_at');

        return response()->json([
            'data' => [
                'attempt' => [
                    'id' => $attempt->id,
                    'score' => $attempt->score,
                    'total_questions' => $attempt->total_questions,
                    'correct_answers' => $attempt->correct_answers,
                    'percentage' => $this->percentageOrZero($attempt->percentage),
                    'passed' => $attempt->passed,
                    'started_at' => $attempt->started_at?->toISOString(),
                    'completed_at' => $attempt->completed_at?->toISOString(),
                ],
                'learner' => [
                    'id' => $attempt->user->id,
                    'name' => $attempt->user->name,
                    'email' => $attempt->user->email,
                ],
                'quiz' => [
                    'id' => $attempt->quiz->id,
                    'title_en' => $attempt->quiz->title_en,
                    'title_mk' => $attempt->quiz->title_mk,
                    'slug' => $attempt->quiz->slug,
                    'difficulty' => $attempt->quiz->difficulty,
                    'estimated_minutes' => $attempt->quiz->estimated_minutes,
                    'points_per_question' => $attempt->quiz->points_per_question,
                    'admin_url' => "/admin/quizzes?quiz={$attempt->quiz->id}",
                ],
                'category' => [
                    'id' => $attempt->quiz->category->id,
                    'name_en' => $attempt->quiz->category->name_en,
                    'name_mk' => $attempt->quiz->category->name_mk,
                    'slug' => $attempt->quiz->category->slug,
                    'icon' => $attempt->quiz->category->icon,
                ],
                'learner_quiz_stats' => [
                    'total_attempts' => (clone $learnerQuizAttempts)->count(),
                    'best_percentage' => $this->percentageOrZero((clone $learnerQuizAttempts)->max('percentage')),
                    'best_points' => (int) (clone $learnerQuizAttempts)->max('score'),
                ],
                'answers' => $attempt->answers
                    ->map(fn (QuizAttemptAnswer $answer, int $index): array => $this->attemptAnswerPayload($answer, $index))
                    ->values(),
            ],
        ]);
    }

    private function recentAttempts(int $limit): array
    {
        return $this->completedAttemptsQuery()
            ->limit($limit)
            ->get()
            ->map(fn (QuizAttempt $attempt): array => $this->attemptListPayload($attempt))
            ->values()
            ->all();
    }

    private function completedAttemptsQuery(): Builder
    {
        return QuizAttempt::query()
            ->whereNotNull('completed_at')
            ->with(['user', 'quiz.category'])
            ->orderByDesc('completed_at')
            ->orderByDesc('id');
    }

    private function attemptListPayload(QuizAttempt $attempt): array
    {
        return [
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
            'admin_result_url' => "/admin/attempts/{$attempt->id}",
        ];
    }

    private function attemptAnswerPayload(QuizAttemptAnswer $attemptAnswer, int $index): array
    {
        $question = $attemptAnswer->question;
        $selectedAnswer = $attemptAnswer->selectedAnswer;
        $correctAnswer = $question?->correctAnswer;

        return [
            'id' => $attemptAnswer->id,
            'number' => $index + 1,
            'is_correct' => $attemptAnswer->is_correct,
            'points_awarded' => $attemptAnswer->points_awarded,
            'question_points' => $attemptAnswer->question_points_snapshot ?? $question?->points,
            'question' => [
                'id' => $question?->id,
                'question_type' => $attemptAnswer->question_type_snapshot ?? $question?->question_type,
                'translation_direction' => $attemptAnswer->translation_direction_snapshot ?? $question?->translation_direction,
                'metadata' => $attemptAnswer->question_metadata_snapshot ?? $question?->metadata ?? [],
                'question_en' => $attemptAnswer->question_en_snapshot ?? $question?->question_en,
                'question_mk' => $attemptAnswer->question_mk_snapshot ?? $question?->question_mk,
                'explanation_en' => $attemptAnswer->explanation_en_snapshot ?? $question?->explanation_en,
                'explanation_mk' => $attemptAnswer->explanation_mk_snapshot ?? $question?->explanation_mk,
            ],
            'selected_answer' => [
                'id' => $selectedAnswer?->id,
                'answer_en' => $attemptAnswer->selected_answer_en_snapshot ?? $selectedAnswer?->answer_en,
                'answer_mk' => $attemptAnswer->selected_answer_mk_snapshot ?? $selectedAnswer?->answer_mk,
            ],
            'correct_answer' => [
                'id' => $correctAnswer?->id,
                'answer_en' => $attemptAnswer->correct_answer_en_snapshot ?? $correctAnswer?->answer_en,
                'answer_mk' => $attemptAnswer->correct_answer_mk_snapshot ?? $correctAnswer?->answer_mk,
            ],
        ];
    }

    private function attemptFilterOptions(): array
    {
        return [
            'categories' => Category::query()
                ->ordered()
                ->get(['id', 'name_en', 'slug'])
                ->map(fn (Category $category): array => [
                    'id' => $category->id,
                    'name_en' => $category->name_en,
                    'slug' => $category->slug,
                ])
                ->values(),
            'quizzes' => Quiz::query()
                ->select('quizzes.id', 'quizzes.title_en', 'quizzes.slug', 'quizzes.category_id')
                ->join('categories', 'categories.id', '=', 'quizzes.category_id')
                ->orderBy('categories.sort_order')
                ->orderBy('categories.name_en')
                ->orderBy('quizzes.sort_order')
                ->orderBy('quizzes.title_en')
                ->get()
                ->map(fn (Quiz $quiz): array => [
                    'id' => $quiz->id,
                    'category_id' => $quiz->category_id,
                    'title_en' => $quiz->title_en,
                    'slug' => $quiz->slug,
                ])
                ->values(),
        ];
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
