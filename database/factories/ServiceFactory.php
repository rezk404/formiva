<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ContentStatus;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Service> */
class ServiceFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'slug' => fake()->unique()->slug(),
            'index_label' => fake()->numerify('##'),
            'title' => fake()->words(2, true),
            'form' => fake()->randomElement(['modular', 'networked', 'layered']),
            'lede' => fake()->sentence(),
            'why' => fake()->sentence(),
            'outcome' => fake()->sentence(),
            'note' => fake()->sentence(),
            'position' => 0,
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
}
