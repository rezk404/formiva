<?php

namespace Database\Factories;

use App\Models\Category;
use App\Enums\CategoryType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['type' => CategoryType::Project, 'name' => fake()->words(2, true), 'slug' => fake()->unique()->slug(), 'position' => fake()->numberBetween(0, 20)];
    }
}
