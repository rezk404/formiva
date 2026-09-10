<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CategoryType;
use App\Enums\ContentStatus;
use App\Models\Category;
use App\Models\Insight;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Insight> */
class InsightFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'slug' => fake()->unique()->slug(),
            'index_label' => fake()->numerify('##'),
            'category_id' => Category::factory()->state(['type' => CategoryType::Insight]),
            'title' => fake()->sentence(6),
            'dek' => fake()->sentence(),
            'body' => fake()->paragraph(),
            'reading_minutes' => fake()->numberBetween(3, 12),
            'plate_seed' => fake()->numberBetween(1000, 9999),
            'plate_variant' => 'ink',
            'plate_ratio' => '16/10',
            'alt' => fake()->sentence(),
            'status' => ContentStatus::Published,
            'published_at' => now()->subDay(),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => ContentStatus::Draft,
            'published_at' => null,
        ]);
    }

    public function scheduled(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => ContentStatus::Scheduled,
            'published_at' => now()->addWeek(),
        ]);
    }

    /** Scheduled, but its moment has already passed — the publish-due queue. */
    public function due(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => ContentStatus::Scheduled,
            'published_at' => now()->subHour(),
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => ContentStatus::Archived,
        ]);
    }

    public function uncategorised(): static
    {
        return $this->state(fn (array $attributes): array => ['category_id' => null]);
    }
}
