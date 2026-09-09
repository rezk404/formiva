<?php

namespace Database\Factories;

use App\Models\Client;
use App\Enums\ClientStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Client>
 */
class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['name' => fake()->company(), 'slug' => fake()->unique()->slug(), 'wordmark' => fake()->company(), 'sector' => fake()->word(), 'country' => fake()->country(), 'status' => ClientStatus::Prospect, 'is_featured' => false];
    }
}
