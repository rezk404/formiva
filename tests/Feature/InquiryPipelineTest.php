<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\InquiryStatus;
use App\Mail\InquiryReceived;
use App\Mail\InquirySubmitted;
use App\Models\Inquiry;
use App\Models\User;
use App\Services\InquiryPipeline;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

final class InquiryPipelineTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_intake_creates_a_new_inquiry_and_sends_notifications(): void
    {
        Mail::fake();

        $response = $this->postJson(route('inquiries.store'), $this->payload(['services' => ['Strategy', 'CRM / automation']]));

        $response->assertCreated()->assertJsonPath('ok', true)->assertJsonStructure(['reference']);
        $inquiry = Inquiry::query()->firstOrFail();
        $this->assertSame(InquiryStatus::New, $inquiry->status);
        $this->assertNull($inquiry->client_id);
        $this->assertNull($inquiry->project_id);
        $this->assertSame(['Strategy', 'CRM / automation'], $inquiry->services);
        $this->assertMatchesRegularExpression('/^FM-\d{4,}$/', $inquiry->reference);
        Mail::assertSent(InquiryReceived::class);
        Mail::assertSent(InquirySubmitted::class);
    }

    public function test_invalid_intake_is_rejected_and_honeypot_is_not_accepted(): void
    {
        $this->postJson(route('inquiries.store'), ['name' => '', 'website' => 'bot'])->assertStatus(422)->assertJsonValidationErrors(['name', 'website']);
        $this->assertDatabaseCount('inquiries', 0);
    }

    public function test_normal_browser_submission_redirects_back_with_reference(): void
    {
        $response = $this->post(route('inquiries.store'), $this->payload());

        $response->assertRedirect(route('contact'))->assertSessionHas('inquiry_success');
        $this->assertDatabaseCount('inquiries', 1);
    }

    public function test_admin_can_review_assign_note_and_convert_without_creating_a_project(): void
    {
        $admin = User::factory()->admin()->create();
        $inquiry = Inquiry::factory()->create();
        $pipeline = app(InquiryPipeline::class);

        $pipeline->transition($inquiry, InquiryStatus::Reviewing, actor: $admin);
        $pipeline->transition($inquiry, InquiryStatus::Qualified, actor: $admin);
        $pipeline->addNote($inquiry, $admin, 'Strong fit for the systems team.');
        $client = $pipeline->convert($inquiry, $admin);

        $inquiry->refresh();
        $this->assertSame(InquiryStatus::Converted, $inquiry->status);
        $this->assertSame($client->id, $inquiry->client_id);
        $this->assertNotNull($inquiry->converted_at);
        $this->assertDatabaseCount('projects', 0);
        $this->assertDatabaseHas('activity_logs', ['subject_id' => $inquiry->id, 'event' => 'converted']);
        $this->assertDatabaseHas('inquiry_notes', ['inquiry_id' => $inquiry->id, 'user_id' => $admin->id]);
    }

    public function test_admin_inquiry_index_and_detail_are_authorized_and_searchable(): void
    {
        $admin = User::factory()->admin()->create();
        Inquiry::factory()->create(['reference' => 'FM-9090', 'company' => 'Northstar']);
        Inquiry::factory()->create(['reference' => 'FM-8080', 'company' => 'Other']);

        $this->actingAs($admin)->get(route('admin.inquiries.index', ['q' => 'Northstar']))->assertOk()->assertSee('FM-9090')->assertDontSee('FM-8080');
        $inquiry = Inquiry::query()->where('reference', 'FM-9090')->firstOrFail();
        $this->actingAs($admin)->get(route('admin.inquiries.show', $inquiry))->assertOk()->assertSee('Northstar');
    }

    public function test_inquiry_statuses_can_render_via_shared_admin_status_component(): void
    {
        foreach (InquiryStatus::cases() as $status) {
            $markup = Blade::render('<x-admin.status :status="$status" />', ['status' => $status]);

            $this->assertStringContainsString($status->label(), $markup);
            $this->assertStringContainsString('admin-badge', $markup);
            $this->assertStringContainsString($status->tone(), $markup);
        }
    }

    /** @return array<string, mixed> */
    private function payload(array $overrides = []): array
    {
        return array_merge(['name' => 'Ada Lovelace', 'company' => 'Northstar', 'email' => 'ada@example.com', 'phone' => '', 'country' => 'Egypt', 'type' => 'corporate-website', 'industry' => 'Technology', 'company_size' => '11–50 people', 'problem' => 'A clear operational problem.', 'services' => ['Strategy'], 'scope' => 'A new platform.', 'budget_choice' => 'EGP 50k–100k', 'timeline' => '2–4 months', 'message' => 'The full project context.', 'notes' => 'No rush.'], $overrides);
    }
}
