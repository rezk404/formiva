<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CategoryType;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Category> */
class CategoryFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'type' => CategoryType::Project,
            'name' => fake()->words(2, true),
            'slug' => fake()->unique()->slug(),
            'position' => 0,
        ];
    }

    public function insight(): static
    {
        return $this->state(fn (array $attributes): array => ['type' => CategoryType::Insight]);
    }

    public function project(): static
    {
        return $this->state(fn (array $attributes): array => ['type' => CategoryType::Project]);
    }
}
