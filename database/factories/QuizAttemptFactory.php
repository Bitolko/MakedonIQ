<?php

namespace Database\Factories;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<QuizAttempt> */
class QuizAttemptFactory extends Factory
{
    public function definition(): array
    {
        $total = fake()->numberBetween(5, 15);
        $correct = fake()->numberBetween(0, $total);
        $score = $correct * 10;
        $percentage = round(($correct / $total) * 100, 2);

        return [
            'user_id' => User::factory(),
            'quiz_id' => Quiz::factory(),
            'score' => $score,
            'total_questions' => $total,
            'correct_answers' => $correct,
            'percentage' => $percentage,
            'passed' => $percentage >= 70,
            'started_at' => now()->subMinutes(15),
            'completed_at' => now(),
        ];
    }
}
