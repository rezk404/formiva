<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\TeamMember;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<TeamMember> */
class TeamMemberFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        $name = fake()->name();

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 99999),
            'role' => fake()->randomElement([
                'Creative Director',
                'Technical Director',
                'Product Designer',
                'Motion Director',
                'Systems Engineer',
                'Researcher',
            ]),
            'bio' => fake()->sentence(14),
            'since_year' => fake()->numberBetween(2016, (int) date('Y')),
            'email' => fake()->unique()->safeEmail(),
            'photo_media_id' => null,
            'plate_seed' => fake()->numberBetween(1000, 9999),
            'plate_variant' => fake()->randomElement(['ink', 'bone', 'signal']),
            'plate_ratio' => '3/4',
            'alt' => 'Portrait plate for '.$name.'.',
            'position' => 0,
            'is_published' => true,
        ];
    }

    public function hidden(): static
    {
        return $this->state(fn (array $attributes): array => ['is_published' => false]);
    }
}
