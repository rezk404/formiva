<?php

namespace Database\Factories;

use App\Enums\ActivityEvent;
use App\Models\ActivityLog;
use App\Models\Inquiry;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ActivityLog>
 */
class ActivityLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'subject_type' => Inquiry::class,
            'subject_id' => Inquiry::factory(),
            'event' => ActivityEvent::Created,
            'description' => 'Inquiry received.',
            'properties' => [],
            'created_at' => now(),
        ];
    }
}
