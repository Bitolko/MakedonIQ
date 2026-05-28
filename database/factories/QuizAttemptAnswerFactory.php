<?php

namespace Database\Factories;

use App\Models\Answer;
use App\Models\Question;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptAnswer;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<QuizAttemptAnswer> */
class QuizAttemptAnswerFactory extends Factory
{
    public function definition(): array
    {
        $isCorrect = fake()->boolean();

        return [
            'quiz_attempt_id' => QuizAttempt::factory(),
            'question_id' => Question::factory(),
            'answer_id' => Answer::factory(),
            'is_correct' => $isCorrect,
            'points_awarded' => $isCorrect ? 10 : 0,
        ];
    }
}
