<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QuizAttemptController extends Controller
{
    public function store(Request $request, Quiz $quiz): JsonResponse
    {
        $this->ensurePublishedQuiz($quiz);

        $validated = $request->validate([
            'answers' => ['required', 'array'],
            'answers.*.question_id' => ['required', 'integer'],
            'answers.*.answer_id' => ['required', 'integer'],
        ]);

        $questions = $quiz->questions()
            ->published()
            ->ordered()
            ->with('answers')
            ->get();

        if ($questions->isEmpty()) {
            throw ValidationException::withMessages([
                'answers' => 'This quiz does not have any published questions yet.',
            ]);
        }

        $submittedAnswers = collect($validated['answers']);

        if ($submittedAnswers->pluck('question_id')->duplicates()->isNotEmpty()) {
            throw ValidationException::withMessages([
                'answers' => 'Each question can only be answered once.',
            ]);
        }

        $submittedByQuestion = $submittedAnswers->keyBy('question_id');
        $publishedQuestionIds = $questions->pluck('id')->sort()->values();
        $submittedQuestionIds = $submittedByQuestion->keys()->sort()->values();

        if ($publishedQuestionIds->values()->all() !== $submittedQuestionIds->values()->all()) {
            throw ValidationException::withMessages([
                'answers' => 'Please answer every published question in this quiz.',
            ]);
        }

        $completedAt = now();

        $attempt = DB::transaction(function () use ($request, $quiz, $questions, $submittedByQuestion, $completedAt): QuizAttempt {
            $correctAnswers = 0;
            $score = 0;
            $attemptRows = [];

            foreach ($questions as $question) {
                $answerId = (int) $submittedByQuestion->get($question->id)['answer_id'];
                $selectedAnswer = $question->answers->firstWhere('id', $answerId);

                if (! $selectedAnswer) {
                    throw ValidationException::withMessages([
                        'answers' => 'One or more selected answers do not belong to their questions.',
                    ]);
                }

                $isCorrect = (bool) $selectedAnswer->is_correct;
                $points = $question->points ?? $quiz->points_per_question;
                $pointsAwarded = $isCorrect ? $points : 0;

                if ($isCorrect) {
                    $correctAnswers++;
                }

                $score += $pointsAwarded;
                $attemptRows[] = [
                    'question_id' => $question->id,
                    'answer_id' => $selectedAnswer->id,
                    'is_correct' => $isCorrect,
                    'points_awarded' => $pointsAwarded,
                    'created_at' => $completedAt,
                    'updated_at' => $completedAt,
                ];
            }

            $totalQuestions = $questions->count();
            $percentage = round(($correctAnswers / $totalQuestions) * 100, 2);

            $attempt = QuizAttempt::create([
                'user_id' => $request->user()->id,
                'quiz_id' => $quiz->id,
                'score' => $score,
                'total_questions' => $totalQuestions,
                'correct_answers' => $correctAnswers,
                'percentage' => $percentage,
                'passed' => $percentage >= 70,
                'started_at' => $completedAt,
                'completed_at' => $completedAt,
            ]);

            $attempt->answers()->createMany($attemptRows);

            return $attempt;
        });

        $quiz->load('category');

        return response()->json([
            'data' => [
                'attempt_id' => $attempt->id,
                'quiz_slug' => $quiz->slug,
                'score' => $attempt->score,
                'total_questions' => $attempt->total_questions,
                'correct_answers' => $attempt->correct_answers,
                'percentage' => (float) $attempt->percentage,
                'passed' => $attempt->passed,
                'result_url' => "/quizzes/{$quiz->category->slug}/{$quiz->slug}/results/{$attempt->id}",
            ],
        ], 201);
    }

    public function show(Request $request, QuizAttempt $attempt): JsonResponse
    {
        abort_unless($attempt->user_id === $request->user()->id, 403);

        $attempt->load([
            'quiz.category',
            'answers' => fn ($query) => $query->orderBy('id'),
            'answers.question.correctAnswer',
            'answers.selectedAnswer',
        ]);

        return response()->json([
            'data' => [
                'attempt' => [
                    'id' => $attempt->id,
                    'score' => $attempt->score,
                    'total_questions' => $attempt->total_questions,
                    'correct_answers' => $attempt->correct_answers,
                    'percentage' => (float) $attempt->percentage,
                    'passed' => $attempt->passed,
                    'started_at' => $attempt->started_at?->toISOString(),
                    'completed_at' => $attempt->completed_at?->toISOString(),
                ],
                'quiz' => [
                    'id' => $attempt->quiz->id,
                    'title_en' => $attempt->quiz->title_en,
                    'title_mk' => $attempt->quiz->title_mk,
                    'slug' => $attempt->quiz->slug,
                    'difficulty' => $attempt->quiz->difficulty,
                    'estimated_minutes' => $attempt->quiz->estimated_minutes,
                    'points_per_question' => $attempt->quiz->points_per_question,
                ],
                'category' => [
                    'id' => $attempt->quiz->category->id,
                    'name_en' => $attempt->quiz->category->name_en,
                    'name_mk' => $attempt->quiz->category->name_mk,
                    'slug' => $attempt->quiz->category->slug,
                    'icon' => $attempt->quiz->category->icon,
                ],
                'answers' => $attempt->answers->map(fn ($answer): array => $this->answerPayload($answer))->values(),
            ],
        ]);
    }

    private function ensurePublishedQuiz(Quiz $quiz): void
    {
        abort_unless(
            $quiz->is_published && $quiz->category()->published()->exists(),
            404,
        );
    }

    private function answerPayload($attemptAnswer): array
    {
        $question = $attemptAnswer->question;
        $selectedAnswer = $attemptAnswer->selectedAnswer;
        $correctAnswer = $question->correctAnswer;

        return [
            'id' => $attemptAnswer->id,
            'is_correct' => $attemptAnswer->is_correct,
            'points_awarded' => $attemptAnswer->points_awarded,
            'question' => [
                'id' => $question->id,
                'question_en' => $question->question_en,
                'question_mk' => $question->question_mk,
                'explanation_en' => $question->explanation_en,
                'explanation_mk' => $question->explanation_mk,
                'sort_order' => $question->sort_order,
            ],
            'selected_answer' => [
                'id' => $selectedAnswer?->id,
                'answer_en' => $selectedAnswer?->answer_en,
                'answer_mk' => $selectedAnswer?->answer_mk,
            ],
            'correct_answer' => [
                'id' => $correctAnswer?->id,
                'answer_en' => $correctAnswer?->answer_en,
                'answer_mk' => $correctAnswer?->answer_mk,
            ],
        ];
    }
}
