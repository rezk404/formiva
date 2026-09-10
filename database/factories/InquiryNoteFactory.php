<?php

namespace Database\Factories;

use App\Models\InquiryNote;
use App\Models\Inquiry;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InquiryNote>
 */
class InquiryNoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'inquiry_id' => Inquiry::factory(),
            'user_id' => User::factory(),
            'body' => fake()->paragraph(),
            'is_pinned' => false,
        ];
    }
}
