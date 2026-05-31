<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;

class QuizController extends Controller
{
    public function show(string $slug): JsonResponse
    {
        $quiz = $this->publishedQuiz($slug)
            ->withCount([
                'questions as questions_count' => fn ($query) => $query->published(),
                'questions as map_questions_count' => fn ($query) => $query->published()->where('question_type', 'map_guess'),
            ])
            ->firstOrFail();

        return response()->json([
            'data' => $this->quizPayload($quiz),
        ]);
    }

    public function questions(string $slug): JsonResponse
    {
        $quiz = $this->publishedQuiz($slug)
            ->with([
                'questions' => fn ($query) => $query
                    ->published()
                    ->ordered()
                    ->with([
                        'answers' => fn ($answerQuery) => $answerQuery
                            ->ordered()
                            ->select('id', 'question_id', 'answer_en', 'answer_mk', 'sort_order'),
                    ]),
            ])
            ->firstOrFail();

        $questions = $quiz->questions->map(fn ($question): array => [
            'id' => $question->id,
            'quiz_id' => $question->quiz_id,
            'question_type' => $question->question_type,
            'translation_direction' => $question->translation_direction,
            'metadata' => $question->question_type === 'map_guess'
                ? $this->publicQuestionMetadata($question->metadata ?? [])
                : null,
            'question_en' => $question->question_en,
            'question_mk' => $question->question_mk,
            'sort_order' => $question->sort_order,
            'points' => $question->points,
            'answers' => $question->answers->map(fn ($answer): array => [
                'id' => $answer->id,
                'question_id' => $answer->question_id,
                'answer_en' => $answer->answer_en,
                'answer_mk' => $answer->answer_mk,
                'sort_order' => $answer->sort_order,
            ]),
        ]);

        return response()->json([
            'data' => [
                'quiz' => $this->quizPayload($quiz),
                'questions' => $questions,
            ],
        ]);
    }

    private function publishedQuiz(string $slug): Builder
    {
        return Quiz::query()
            ->published()
            ->where('slug', $slug)
            ->whereHas('category', fn ($query) => $query->published())
            ->with(['category', 'lesson.category']);
    }

    private function quizPayload(Quiz $quiz): array
    {
        return [
            'id' => $quiz->id,
            'category' => [
                'id' => $quiz->category->id,
                'name_en' => $quiz->category->name_en,
                'name_mk' => $quiz->category->name_mk,
                'slug' => $quiz->category->slug,
            ],
            'title_en' => $quiz->title_en,
            'title_mk' => $quiz->title_mk,
            'slug' => $quiz->slug,
            'description_en' => $quiz->description_en,
            'description_mk' => $quiz->description_mk,
            'difficulty' => $quiz->difficulty,
            'estimated_minutes' => $quiz->estimated_minutes,
            'points_per_question' => $quiz->points_per_question,
            'sort_order' => $quiz->sort_order,
            'questions_count' => $quiz->questions_count ?? $quiz->questions->count(),
            'map_questions_count' => $this->mapQuestionsCount($quiz),
            'has_map_questions' => $this->mapQuestionsCount($quiz) > 0,
            'related_lesson' => $this->lessonPayload($quiz),
        ];
    }

    private function mapQuestionsCount(Quiz $quiz): int
    {
        if ($quiz->relationLoaded('questions')) {
            return $quiz->questions
                ->where('question_type', 'map_guess')
                ->count();
        }

        return (int) ($quiz->map_questions_count ?? 0);
    }

    private function publicQuestionMetadata(?array $metadata): array
    {
        $metadata = $metadata ?? [];

        return [
            'map_x' => isset($metadata['map_x']) ? (int) $metadata['map_x'] : null,
            'map_y' => isset($metadata['map_y']) ? (int) $metadata['map_y'] : null,
            'target_type' => $metadata['target_type'] ?? null,
        ];
    }

    private function lessonPayload(Quiz $quiz): ?array
    {
        $lesson = $quiz->lesson;

        if (! $lesson || ! $lesson->is_published || ! $lesson->category?->is_published) {
            return null;
        }

        return [
            'id' => $lesson->id,
            'title_en' => $lesson->title_en,
            'title_mk' => $lesson->title_mk,
            'slug' => $lesson->slug,
            'summary_en' => $lesson->summary_en,
            'summary_mk' => $lesson->summary_mk,
            'difficulty' => $lesson->difficulty,
            'estimated_minutes' => $lesson->estimated_minutes,
            'category_slug' => $lesson->category->slug,
            'url' => "/learn/{$lesson->category->slug}/{$lesson->slug}",
        ];
    }
}
