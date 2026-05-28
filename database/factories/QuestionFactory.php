<?php

namespace Database\Factories;

use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Question> */
class QuestionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'quiz_id' => Quiz::factory(),
            'question_en' => fake()->sentence() . '?',
            'question_mk' => fake()->sentence() . '?',
            'explanation_en' => fake()->sentence(),
            'explanation_mk' => fake()->sentence(),
            'sort_order' => fake()->numberBetween(0, 50),
            'points' => null,
            'is_published' => true,
        ];
    }
}
