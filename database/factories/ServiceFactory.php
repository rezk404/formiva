<?php

namespace Database\Factories;

use App\Models\Service;
use App\Enums\ContentStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['slug' => fake()->unique()->slug(), 'index_label' => fake()->numerify('##'), 'title' => fake()->words(2, true), 'form' => 'modular', 'lede' => fake()->sentence(), 'why' => fake()->sentence(), 'outcome' => fake()->sentence(), 'note' => fake()->sentence(), 'position' => fake()->numberBetween(0, 20), 'status' => ContentStatus::Published];
    }
}
