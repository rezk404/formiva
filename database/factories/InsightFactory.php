<?php

namespace Database\Factories;

use App\Models\Insight;
use App\Enums\ContentStatus;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Insight>
 */
class InsightFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['slug' => fake()->unique()->slug(), 'index_label' => fake()->numerify('##'), 'category_id' => Category::factory()->state(['type' => 'insight']), 'title' => fake()->sentence(6), 'dek' => fake()->sentence(), 'body' => fake()->paragraph(), 'reading_minutes' => fake()->numberBetween(3, 12), 'plate_seed' => fake()->numberBetween(1000, 9999), 'plate_variant' => 'ink', 'plate_ratio' => '16/10', 'alt' => fake()->sentence(), 'status' => ContentStatus::Published, 'published_at' => now()];
    }
}
