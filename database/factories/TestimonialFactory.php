<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Testimonial> */
class TestimonialFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'quote' => fake()->sentence(18),
            'author_name' => fake()->name(),
            'author_role' => fake()->randomElement(['VP Product', 'Head of Operations', 'Founder', 'CTO', 'Marketing Director']),
            'company' => fake()->company(),
            'client_id' => null,
            'project_id' => null,
            'is_published' => true,
            'position' => 0,
        ];
    }

    public function hidden(): static
    {
        return $this->state(fn (array $attributes): array => ['is_published' => false]);
    }
}
