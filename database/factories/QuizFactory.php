<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Quiz;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Quiz> */
class QuizFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->unique()->words(3, true);

        return [
            'category_id' => Category::factory(),
            'title_en' => Str::title($title),
            'title_mk' => Str::title($title),
            'slug' => Str::slug($title),
            'description_en' => fake()->sentence(),
            'description_mk' => fake()->sentence(),
            'difficulty' => fake()->randomElement(['beginner', 'intermediate', 'advanced']),
            'estimated_minutes' => fake()->numberBetween(5, 15),
            'points_per_question' => 10,
            'is_published' => true,
            'is_demo' => false,
            'sort_order' => fake()->numberBetween(0, 50),
        ];
    }
}
