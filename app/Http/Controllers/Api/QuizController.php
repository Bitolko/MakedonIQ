<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    private const LOCKED_QUIZ_MESSAGE = 'Create a free account to unlock this quiz.';
    private const LOCKED_SOUND_DEMO_MESSAGE = 'Create a free account to unlock more sound quizzes.';
    private const SOUND_DEMO_SESSION_KEY = 'makedoniq.sound_demo_quiz_slug';

    public function show(Request $request, string $slug): JsonResponse
    {
        $quiz = $this->publishedQuiz($slug)
            ->withCount([
                'questions as questions_count' => fn ($query) => $query->published(),
                'questions as map_questions_count' => fn ($query) => $query->published()->where('question_type', 'map_guess'),
                'questions as picture_questions_count' => fn ($query) => $query->published()->where('question_type', 'picture_choice'),
                'questions as sound_questions_count' => fn ($query) => $query->published()->where('question_type', 'sound_choice'),
            ])
            ->firstOrFail();

        $this->ensureQuizAccessible($quiz, $request);

        return response()->json([
            'data' => $this->quizPayload($quiz, $this->isGuest($request)),
        ]);
    }

    public function questions(Request $request, string $slug): JsonResponse
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

        $this->ensureQuizAccessible($quiz, $request);
        $this->ensureGuestSoundPreviewAccessible($quiz, $request);

        $questions = $quiz->questions->map(fn ($question): array => [
            'id' => $question->id,
            'quiz_id' => $question->quiz_id,
            'question_type' => $question->question_type,
            'translation_direction' => $question->translation_direction,
            'metadata' => $this->publicQuestionMetadata($question->metadata ?? [], $question->question_type),
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
                'quiz' => $this->quizPayload($quiz, $this->isGuest($request)),
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

    private function ensureQuizAccessible(Quiz $quiz, Request $request): void
    {
        abort_if($this->isGuest($request) && ! $quiz->is_demo, 403, self::LOCKED_QUIZ_MESSAGE);
    }

    private function isGuest(Request $request): bool
    {
        return $request->user() === null;
    }

    private function quizPayload(Quiz $quiz, bool $isGuest): array
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
            'is_demo' => $quiz->is_demo,
            'is_locked' => $isGuest && ! $quiz->is_demo,
            'questions_count' => $quiz->questions_count ?? $quiz->questions->count(),
            'map_questions_count' => $this->mapQuestionsCount($quiz),
            'has_map_questions' => $this->mapQuestionsCount($quiz) > 0,
            'picture_questions_count' => $this->pictureQuestionsCount($quiz),
            'has_picture_questions' => $this->pictureQuestionsCount($quiz) > 0,
            'sound_questions_count' => $this->soundQuestionsCount($quiz),
            'has_sound_questions' => $this->soundQuestionsCount($quiz) > 0,
            'related_lesson' => $this->lessonPayload($quiz, $isGuest),
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

    private function pictureQuestionsCount(Quiz $quiz): int
    {
        if ($quiz->relationLoaded('questions')) {
            return $quiz->questions
                ->where('question_type', 'picture_choice')
                ->count();
        }

        return (int) ($quiz->picture_questions_count ?? 0);
    }

    private function soundQuestionsCount(Quiz $quiz): int
    {
        if ($quiz->relationLoaded('questions')) {
            return $quiz->questions
                ->where('question_type', 'sound_choice')
                ->count();
        }

        return (int) ($quiz->sound_questions_count ?? 0);
    }

    private function publicQuestionMetadata(?array $metadata, ?string $questionType): ?array
    {
        return match ($questionType) {
            'map_guess' => $this->publicMapQuestionMetadata($metadata),
            'picture_choice' => $this->publicPictureQuestionMetadata($metadata),
            'sound_choice' => $this->publicSoundQuestionMetadata($metadata),
            default => null,
        };
    }

    private function publicMapQuestionMetadata(?array $metadata): array
    {
        $metadata = $metadata ?? [];

        return [
            'map_x' => isset($metadata['map_x']) ? (float) $metadata['map_x'] : null,
            'map_y' => isset($metadata['map_y']) ? (float) $metadata['map_y'] : null,
            'target_type' => $metadata['target_type'] ?? null,
        ];
    }

    private function publicPictureQuestionMetadata(?array $metadata): array
    {
        $metadata = $metadata ?? [];

        return [
            'image_path' => $this->nullableMetadataString($metadata['image_path'] ?? null),
            'image_alt_en' => $this->nullableMetadataString($metadata['image_alt_en'] ?? null),
            'image_alt_mk' => $this->nullableMetadataString($metadata['image_alt_mk'] ?? null),
            'image_type' => $this->nullableMetadataString($metadata['image_type'] ?? null) ?? 'other',
        ];
    }

    private function publicSoundQuestionMetadata(?array $metadata): array
    {
        $metadata = $metadata ?? [];

        return [
            'audio_path' => $this->nullableMetadataString($metadata['audio_path'] ?? null),
            'audio_alt_en' => $this->nullableMetadataString($metadata['audio_alt_en'] ?? null),
            'audio_alt_mk' => $this->nullableMetadataString($metadata['audio_alt_mk'] ?? null),
            'audio_type' => $this->nullableMetadataString($metadata['audio_type'] ?? null) ?? 'folklore',
        ];
    }

    private function nullableMetadataString(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }

    private function ensureGuestSoundPreviewAccessible(Quiz $quiz, Request $request): void
    {
        if (! $this->isGuest($request) || ! $this->quizHasSoundQuestions($quiz)) {
            return;
        }

        $previewSlug = $request->session()->get(self::SOUND_DEMO_SESSION_KEY);

        abort_if($previewSlug && $previewSlug !== $quiz->slug, 403, self::LOCKED_SOUND_DEMO_MESSAGE);

        $request->session()->put(self::SOUND_DEMO_SESSION_KEY, $quiz->slug);
    }

    private function quizHasSoundQuestions(Quiz $quiz): bool
    {
        if ($quiz->relationLoaded('questions')) {
            return $quiz->questions->contains('question_type', 'sound_choice');
        }

        return $quiz->questions()
            ->published()
            ->where('question_type', 'sound_choice')
            ->exists();
    }

    private function lessonPayload(Quiz $quiz, bool $isGuest): ?array
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
            'is_demo' => $lesson->is_demo,
            'is_locked' => $isGuest && ! $lesson->is_demo,
            'category_slug' => $lesson->category->slug,
            'url' => "/learn/{$lesson->category->slug}/{$lesson->slug}",
        ];
    }
}
