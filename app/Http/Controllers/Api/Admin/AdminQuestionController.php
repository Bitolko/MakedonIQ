<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdminQuestionController extends Controller
{
    public function index(Quiz $quiz): JsonResponse
    {
        $questions = $quiz->questions()
            ->ordered()
            ->with(['quiz.category', 'answers' => fn ($query) => $query->ordered()])
            ->withCount(['attemptAnswers as attempt_answers_count'])
            ->get()
            ->map(fn (Question $question): array => $this->questionPayload($question))
            ->values();

        return response()->json(['data' => $questions]);
    }

    public function store(Request $request, Quiz $quiz): JsonResponse
    {
        $validated = $this->validatedData($request);

        $question = DB::transaction(function () use ($quiz, $validated): Question {
            $question = $quiz->questions()->create($this->questionAttributes($validated));
            $question->answers()->createMany($this->answerRows($validated['answers']));

            return $question;
        });

        return response()->json([
            'data' => $this->questionPayload($this->questionWithDetails($question)),
        ], 201);
    }

    public function show(Question $question): JsonResponse
    {
        return response()->json([
            'data' => $this->questionPayload($this->questionWithDetails($question)),
        ]);
    }

    public function update(Request $request, Question $question): JsonResponse
    {
        $validated = $this->validatedData($request);
        $question = $this->questionWithDetails($question);
        $hasAttemptHistory = $this->hasAttemptHistory($question);

        if ($hasAttemptHistory && ! $this->answersMatch($question, $validated['answers'])) {
            throw ValidationException::withMessages([
                'question' => 'This question has quiz attempts linked to it. Unpublish it and create a new question instead.',
            ]);
        }

        $updated = DB::transaction(function () use ($question, $validated, $hasAttemptHistory): Question {
            $question->update($this->questionAttributes($validated));

            if (! $hasAttemptHistory) {
                $question->answers()->delete();
                $question->answers()->createMany($this->answerRows($validated['answers']));
            }

            return $question;
        });

        return response()->json([
            'data' => $this->questionPayload($this->questionWithDetails($updated)),
        ]);
    }

    public function destroy(Question $question): JsonResponse
    {
        if ($this->hasAttemptHistory($question)) {
            throw ValidationException::withMessages([
                'question' => 'This question has been used in attempts. Unpublish it instead.',
            ]);
        }

        $question->delete();

        return response()->json([
            'message' => 'Question deleted.',
        ]);
    }

    private function validatedData(Request $request): array
    {
        $validated = $request->validate([
            'question_type' => ['sometimes', 'string', 'in:multiple_choice,map_guess'],
            'metadata' => ['nullable', 'array'],
            'metadata.map_x' => ['nullable', 'integer', 'min:0', 'max:100'],
            'metadata.map_y' => ['nullable', 'integer', 'min:0', 'max:100'],
            'metadata.target_type' => ['nullable', 'string', 'in:city,lake,landmark,region'],
            'metadata.map_target_key' => ['nullable', 'string', 'max:100'],
            'metadata.map_target_label_en' => ['nullable', 'string', 'max:255'],
            'metadata.map_target_label_mk' => ['nullable', 'string', 'max:255'],
            'question_en' => ['required', 'string'],
            'question_mk' => ['nullable', 'string'],
            'explanation_en' => ['nullable', 'string'],
            'explanation_mk' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'points' => ['nullable', 'integer', 'min:1', 'max:100'],
            'is_published' => ['sometimes', 'boolean'],
            'answers' => ['required', 'array', 'size:4'],
            'answers.*.answer_en' => ['required', 'string'],
            'answers.*.answer_mk' => ['nullable', 'string'],
            'answers.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'answers.*.is_correct' => ['required', 'boolean'],
        ]);

        $correctAnswers = collect($validated['answers'])
            ->filter(fn (array $answer): bool => $this->booleanValue($answer['is_correct']))
            ->count();

        if ($correctAnswers !== 1) {
            throw ValidationException::withMessages([
                'answers' => 'Exactly one answer must be marked correct.',
            ]);
        }

        if (($validated['question_type'] ?? 'multiple_choice') === 'map_guess') {
            $metadata = $validated['metadata'] ?? [];

            if (! isset($metadata['map_x'], $metadata['map_y'])) {
                throw ValidationException::withMessages([
                    'metadata' => 'Map guess questions need map X and map Y percentages.',
                ]);
            }
        }

        return $validated;
    }

    private function questionAttributes(array $validated): array
    {
        return [
            'question_type' => $validated['question_type'] ?? 'multiple_choice',
            'metadata' => $this->metadataAttributes($validated),
            'question_en' => $validated['question_en'],
            'question_mk' => $this->nullableString($validated['question_mk'] ?? null),
            'explanation_en' => $this->nullableString($validated['explanation_en'] ?? null),
            'explanation_mk' => $this->nullableString($validated['explanation_mk'] ?? null),
            'sort_order' => $validated['sort_order'] ?? 0,
            'points' => $validated['points'] ?? null,
            'is_published' => array_key_exists('is_published', $validated) ? $this->booleanValue($validated['is_published']) : true,
        ];
    }

    private function answerRows(array $answers): array
    {
        return collect($answers)
            ->values()
            ->map(fn (array $answer, int $index): array => [
                'answer_en' => $answer['answer_en'],
                'answer_mk' => $this->nullableString($answer['answer_mk'] ?? null),
                'sort_order' => $answer['sort_order'] ?? $index + 1,
                'is_correct' => $this->booleanValue($answer['is_correct']),
            ])
            ->all();
    }

    private function metadataAttributes(array $validated): ?array
    {
        if (($validated['question_type'] ?? 'multiple_choice') !== 'map_guess') {
            return null;
        }

        $metadata = $validated['metadata'] ?? [];

        return [
            'map_x' => isset($metadata['map_x']) ? (int) $metadata['map_x'] : null,
            'map_y' => isset($metadata['map_y']) ? (int) $metadata['map_y'] : null,
            'target_type' => $this->nullableString($metadata['target_type'] ?? 'city') ?? 'city',
            'map_target_key' => $this->nullableString($metadata['map_target_key'] ?? null),
            'map_target_label_en' => $this->nullableString($metadata['map_target_label_en'] ?? null),
            'map_target_label_mk' => $this->nullableString($metadata['map_target_label_mk'] ?? null),
        ];
    }

    private function answersMatch(Question $question, array $answers): bool
    {
        return $this->normalizedExistingAnswers($question) === $this->normalizedSubmittedAnswers($answers);
    }

    private function normalizedExistingAnswers(Question $question): array
    {
        return $question->answers
            ->sortBy([['sort_order', 'asc'], ['id', 'asc']])
            ->values()
            ->map(fn ($answer): array => [
                'answer_en' => $answer->answer_en,
                'answer_mk' => $this->nullableString($answer->answer_mk),
                'sort_order' => (int) $answer->sort_order,
                'is_correct' => (bool) $answer->is_correct,
            ])
            ->all();
    }

    private function normalizedSubmittedAnswers(array $answers): array
    {
        return collect($answers)
            ->values()
            ->map(fn (array $answer, int $index): array => [
                'answer_en' => $answer['answer_en'],
                'answer_mk' => $this->nullableString($answer['answer_mk'] ?? null),
                'sort_order' => (int) ($answer['sort_order'] ?? $index + 1),
                'is_correct' => $this->booleanValue($answer['is_correct']),
            ])
            ->sortBy([['sort_order', 'asc'], ['answer_en', 'asc'], ['answer_mk', 'asc']])
            ->values()
            ->all();
    }

    private function questionWithDetails(Question $question): Question
    {
        return Question::query()
            ->whereKey($question->id)
            ->with(['quiz.category', 'answers' => fn ($query) => $query->ordered()])
            ->withCount(['attemptAnswers as attempt_answers_count'])
            ->firstOrFail();
    }

    private function questionPayload(Question $question): array
    {
        $answers = $question->answers
            ->sortBy([['sort_order', 'asc'], ['id', 'asc']])
            ->values();
        $correctAnswer = $answers->firstWhere('is_correct', true);

        return [
            'id' => $question->id,
            'quiz_id' => $question->quiz_id,
            'quiz_title_en' => $question->quiz->title_en,
            'quiz_slug' => $question->quiz->slug,
            'category_name_en' => $question->quiz->category->name_en,
            'category_slug' => $question->quiz->category->slug,
            'question_type' => $question->question_type,
            'metadata' => $question->metadata,
            'question_en' => $question->question_en,
            'question_mk' => $question->question_mk,
            'explanation_en' => $question->explanation_en,
            'explanation_mk' => $question->explanation_mk,
            'sort_order' => $question->sort_order,
            'points' => $question->points,
            'is_published' => $question->is_published,
            'answers_count' => $answers->count(),
            'attempt_answers_count' => (int) ($question->attempt_answers_count ?? 0),
            'used_in_attempts' => (int) ($question->attempt_answers_count ?? 0) > 0,
            'correct_answer_en' => $correctAnswer?->answer_en,
            'correct_answer_mk' => $correctAnswer?->answer_mk,
            'answers' => $answers->map(fn ($answer): array => [
                'id' => $answer->id,
                'answer_en' => $answer->answer_en,
                'answer_mk' => $answer->answer_mk,
                'sort_order' => $answer->sort_order,
                'is_correct' => $answer->is_correct,
            ])->all(),
            'created_at' => $question->created_at?->toISOString(),
            'updated_at' => $question->updated_at?->toISOString(),
        ];
    }

    private function hasAttemptHistory(Question $question): bool
    {
        return $question->attemptAnswers()->exists();
    }

    private function nullableString(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function booleanValue(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
