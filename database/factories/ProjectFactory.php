<?php

namespace Database\Factories;

use App\Models\Project;
use App\Enums\ProjectStage;
use App\Enums\ProjectStatus;
use App\Models\Category;
use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['slug' => fake()->unique()->slug(), 'index_label' => fake()->numerify('##'), 'name' => fake()->words(2, true), 'title' => fake()->sentence(3), 'category_id' => Category::factory(), 'client_id' => Client::factory(), 'year' => fake()->year(), 'statement' => fake()->sentence(), 'description' => fake()->paragraph(), 'challenge' => fake()->sentence(), 'solution' => fake()->sentence(), 'outcome' => fake()->sentence(), 'result_value' => fake()->numerify('+##%'), 'result_label' => fake()->sentence(2), 'disciplines' => [], 'stack' => [], 'plate_seed' => fake()->numberBetween(1000, 9999), 'plate_variant' => 'ink', 'plate_ratio' => '4/3', 'alt' => fake()->sentence(), 'is_featured' => false, 'status' => ProjectStatus::Published, 'stage' => ProjectStage::Completed, 'position' => fake()->numberBetween(0, 20)];
    }
}
