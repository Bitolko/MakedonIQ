<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Category> */
class CategoryFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name_en' => Str::title($name),
            'name_mk' => Str::title($name),
            'slug' => Str::slug($name),
            'description_en' => fake()->sentence(),
            'description_mk' => fake()->sentence(),
            'icon' => fake()->randomElement(['Aa', 'AB', 'HI', 'GE', 'CU', 'FM']),
            'sort_order' => fake()->numberBetween(0, 50),
            'is_published' => true,
        ];
    }
}
