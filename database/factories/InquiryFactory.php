<?php

namespace Database\Factories;

use App\Enums\InquiryKind;
use App\Enums\InquiryPriority;
use App\Enums\InquiryStatus;
use App\Models\Inquiry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Inquiry>
 */
class InquiryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reference' => 'FM-'.fake()->unique()->numerify('####'),
            'kind' => InquiryKind::Project,
            'name' => fake()->name(),
            'company' => fake()->company(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'country' => 'Egypt',
            'project_type' => 'corporate-website',
            'project_group' => 'web',
            'industry' => 'Technology',
            'company_size' => '11–50 people',
            'problem' => fake()->paragraph(),
            'services' => ['Strategy', 'Product design'],
            'scope' => fake()->sentence(),
            'budget_range' => 'EGP 50k–100k',
            'timeline' => '2–4 months',
            'message' => fake()->paragraph(),
            'notes' => null,
            'status' => InquiryStatus::New,
            'priority' => InquiryPriority::Normal,
            'source' => 'website',
            'utm' => [],
        ];
    }

    public function reviewing(): static { return $this->state(['status' => InquiryStatus::Reviewing]); }
    public function qualified(): static { return $this->state(['status' => InquiryStatus::Qualified]); }
    public function highPriority(): static { return $this->state(['priority' => InquiryPriority::High]); }
}
