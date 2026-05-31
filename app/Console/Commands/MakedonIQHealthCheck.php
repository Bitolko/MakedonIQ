<?php

namespace App\Console\Commands;

use App\Models\Answer;
use App\Models\Category;
use App\Models\Lesson;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Console\Command;

class MakedonIQHealthCheck extends Command
{
    protected $signature = 'makedoniq:health-check';

    protected $description = 'Check core MakedonIQ content integrity and production-readiness signals.';

    /** @var list<string> */
    private array $failures = [];

    /** @var list<string> */
    private array $warnings = [];

    public function handle(): int
    {
        $this->info('MakedonIQ health check');
        $this->line('');

        $this->checkContentCounts();
        $this->checkQuestionAnswerIntegrity();
        $this->checkMapQuestions();
        $this->checkPublicQuizControllerSource();
        $this->checkAdminAndDebugState();

        $this->line('');

        if ($this->failures !== []) {
            $this->error(count($this->failures).' failed check(s). Fix these before a production deploy.');

            return self::FAILURE;
        }

        if ($this->warnings !== []) {
            $this->warn(count($this->warnings).' warning(s). Review before demo or production deploy.');

            return self::SUCCESS;
        }

        $this->info('All checks passed.');

        return self::SUCCESS;
    }

    private function checkContentCounts(): void
    {
        $counts = [
            'categories' => Category::count(),
            'lessons' => Lesson::count(),
            'quizzes' => Quiz::count(),
            'questions' => Question::count(),
            'answers' => Answer::count(),
        ];

        foreach ($counts as $label => $count) {
            if ($count > 0) {
                $this->pass(ucfirst($label).": {$count}");

                continue;
            }

            $this->failed(ucfirst($label).' count is zero.');
        }
    }

    private function checkQuestionAnswerIntegrity(): void
    {
        $questions = Question::query()
            ->select('id', 'question_type', 'metadata')
            ->withCount([
                'answers',
                'answers as correct_answers_count' => fn ($query) => $query->where('is_correct', true),
            ])
            ->get();

        $wrongAnswerCount = $questions->filter(fn (Question $question): bool => $question->answers_count !== 4);
        $wrongCorrectCount = $questions->filter(fn (Question $question): bool => $question->correct_answers_count !== 1);

        if ($wrongAnswerCount->isEmpty()) {
            $this->pass('Every question has exactly 4 answers.');
        } else {
            $this->failed($wrongAnswerCount->count().' question(s) do not have exactly 4 answers.');
        }

        if ($wrongCorrectCount->isEmpty()) {
            $this->pass('Every question has exactly 1 correct answer.');
        } else {
            $this->failed($wrongCorrectCount->count().' question(s) do not have exactly 1 correct answer.');
        }
    }

    private function checkMapQuestions(): void
    {
        $mapQuestions = Question::query()
            ->where('question_type', 'map_guess')
            ->get(['id', 'metadata']);

        if ($mapQuestions->isEmpty()) {
            $this->warning('No map_guess questions found.');

            return;
        }

        $missingCoordinates = $mapQuestions->filter(function (Question $question): bool {
            $metadata = $question->metadata ?? [];

            return ! array_key_exists('map_x', $metadata)
                || ! array_key_exists('map_y', $metadata)
                || ! is_numeric($metadata['map_x'])
                || ! is_numeric($metadata['map_y']);
        });

        if ($missingCoordinates->isEmpty()) {
            $this->pass('Map challenge questions have numeric map_x and map_y metadata.');
        } else {
            $this->failed($missingCoordinates->count().' map_guess question(s) are missing numeric map coordinates.');
        }
    }

    private function checkPublicQuizControllerSource(): void
    {
        $sourcePath = app_path('Http/Controllers/Api/QuizController.php');
        $source = is_file($sourcePath) ? (string) file_get_contents($sourcePath) : '';

        if ($source === '') {
            $this->failed('Could not inspect the public QuizController source.');

            return;
        }

        if (str_contains($source, "'is_correct'") || str_contains($source, '"is_correct"')) {
            $this->failed('Public QuizController source contains an is_correct response key.');
        } else {
            $this->pass('Public QuizController source does not expose is_correct.');
        }

        if (str_contains($source, 'map_target_key') || str_contains($source, 'map_target_label')) {
            $this->failed('Public QuizController source appears to expose answer-revealing map target metadata.');
        } else {
            $this->pass('Public map metadata source check hides target keys and labels.');
        }
    }

    private function checkAdminAndDebugState(): void
    {
        if (User::where('is_admin', true)->exists()) {
            $this->pass('At least one admin user exists.');
        } else {
            $this->warning('No admin users found. Create one with Tinker before demo/admin testing.');
        }

        if (config('app.debug')) {
            $this->warning('APP_DEBUG is currently true. Set APP_DEBUG=false in production.');
        } else {
            $this->pass('APP_DEBUG is false.');
        }
    }

    private function pass(string $message): void
    {
        $this->line("[OK] {$message}");
    }

    private function warning(string $message): void
    {
        $this->warnings[] = $message;
        $this->warn("[WARN] {$message}");
    }

    private function failed(string $message): void
    {
        $this->failures[] = $message;
        $this->error("[FAIL] {$message}");
    }
}
