<?php

namespace Database\Factories;

use App\Models\Answer;
use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Answer> */
class AnswerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'question_id' => Question::factory(),
            'answer_en' => fake()->words(2, true),
            'answer_mk' => fake()->words(2, true),
            'is_correct' => false,
            'sort_order' => fake()->numberBetween(0, 4),
        ];
    }

    public function correct(): static
    {
        return $this->state(fn (): array => [
            'is_correct' => true,
        ]);
    }
}
