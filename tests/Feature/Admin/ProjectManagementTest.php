<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\ProjectStage;
use App\Enums\ProjectStatus;
use App\Models\Category;
use App\Models\Client;
use App\Models\Media;
use App\Models\Project;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

final class ProjectManagementTest extends TestCase
{
    use RefreshDatabase;

    private function editor(): User
    {
        return User::factory()->create();
    }

    /** @return array<string, mixed> */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Northstar Platform',
            'slug' => '',
            'index_label' => '01',
            'title' => 'Northstar Platform — FORMIVA',
            'category_id' => Category::factory()->create()->id,
            'client_id' => Client::factory()->create()->id,
            'year' => 2026,
            'statement' => 'A system for turning complex operations into clear next steps.',
            'description' => 'A considered digital product for a growing team.',
            'challenge' => 'The old process hid the signal in too many places.',
            'solution' => 'We made the work legible and connected.',
            'outcome' => 'The team can move with confidence.',
            'result_value' => '+42%',
            'result_label' => 'faster decisions',
            'disciplines' => 'Strategy, Product design',
            'stack' => 'Laravel, Vue',
            'stage' => ProjectStage::Completed->value,
            'started_at' => '2025-04-01',
            'completed_at' => '2026-01-01',
            'alt' => 'Northstar platform interface in use.',
            'plate_seed' => 2480,
            'plate_variant' => 'ink',
            'plate_ratio' => '4/3',
            'meta_title' => 'Northstar Platform',
            'meta_description' => 'A digital product built for clearer operations.',
            'services' => [],
            'gallery_media' => [],
            'intent' => 'draft',
        ], $overrides);
    }

    public function test_guests_cannot_access_projects(): void
    {
        $this->get(route('admin.projects.index'))->assertRedirect(route('admin.login'));
    }

    public function test_inactive_users_cannot_access_or_publish_projects(): void
    {
        $user = User::factory()->create(['is_active' => false]);
        $project = Project::factory()->draft()->create();

        $this->actingAs($user)->get(route('admin.projects.index'))->assertRedirect(route('admin.login'))->assertSessionHasErrors('email');
        $this->actingAs($user)->post(route('admin.projects.publish', $project), ['action' => 'publish'])->assertRedirect(route('admin.login'))->assertSessionHasErrors('email');
    }

    public function test_invalid_project_transition_is_rejected(): void
    {
        $project = Project::factory()->create(['status' => ProjectStatus::Published]);

        $this->actingAs($this->editor())
            ->post(route('admin.projects.publish', $project), ['action' => 'publish'])
            ->assertStatus(422);
    }

    public function test_index_filters_projects_by_status_stage_client_and_featured(): void
    {
        $client = Client::factory()->create(['name' => 'Acme Studio']);
        Project::factory()->create(['name' => 'Featured completed', 'client_id' => $client->id, 'is_featured' => true, 'stage' => ProjectStage::Completed]);
        Project::factory()->create(['name' => 'Draft scoping', 'status' => ProjectStatus::Draft, 'stage' => ProjectStage::Scoping, 'is_featured' => false]);

        $this->actingAs($this->editor())
            ->get(route('admin.projects.index', ['status' => 'published', 'stage' => 'completed', 'client' => $client->id, 'featured' => 'yes']))
            ->assertOk()
            ->assertSee('Featured completed')
            ->assertDontSee('Draft scoping');
    }

    public function test_project_is_created_with_relations_and_draft_intent(): void
    {
        $service = Service::factory()->create();
        $media = Media::factory()->create();

        $this->actingAs($this->editor())
            ->post(route('admin.projects.store'), $this->payload([
                'services' => [$service->id],
                'gallery_media' => [$media->id],
            ]))
            ->assertRedirect();

        $project = Project::query()->where('slug', 'northstar-platform')->firstOrFail();
        $this->assertSame(ProjectStatus::Draft, $project->status);
        $this->assertTrue($project->services()->whereKey($service)->exists());
        $this->assertTrue($project->galleryMedia()->whereKey($media)->exists());
        $this->assertSame(['Strategy', 'Product design'], $project->disciplines);
    }

    public function test_project_update_persists_content_and_does_not_change_publication_state(): void
    {
        $project = Project::factory()->create(['status' => ProjectStatus::Published, 'published_at' => now()->subDay()]);

        $this->actingAs($this->editor())
            ->put(route('admin.projects.update', $project), $this->payload([
                'name' => 'Updated Northstar',
                'slug' => 'updated-northstar',
                'category_id' => $project->category_id,
                'client_id' => $project->client_id,
            ]))
            ->assertRedirect();

        $project->refresh();
        $this->assertSame('Updated Northstar', $project->name);
        $this->assertSame(ProjectStatus::Published, $project->status);
        $this->assertNotNull($project->published_at);
    }

    public function test_project_publishing_and_scheduling_follow_lifecycle(): void
    {
        $project = Project::factory()->draft()->create();
        $future = Carbon::now()->addWeek()->format('Y-m-d H:i:s');

        $this->actingAs($this->editor())
            ->post(route('admin.projects.publish', $project), ['action' => 'schedule', 'published_at' => $future])
            ->assertRedirect();

        $this->assertSame(ProjectStatus::Scheduled, $project->fresh()->status);
        $this->assertTrue($project->fresh()->published_at->isFuture());

        $this->actingAs($this->editor())
            ->post(route('admin.projects.publish', $project), ['action' => 'publish'])
            ->assertRedirect();

        $this->assertSame(ProjectStatus::Published, $project->fresh()->status);
    }

    public function test_project_order_and_featured_toggle_are_authorized_actions(): void
    {
        $first = Project::factory()->create(['position' => 0, 'is_featured' => false]);
        $second = Project::factory()->create(['position' => 1, 'is_featured' => false]);
        $editor = $this->editor();

        $this->actingAs($editor)->post(route('admin.projects.move', $second), ['direction' => 'up'])->assertRedirect();
        $this->assertSame(0, $second->fresh()->position);
        $this->assertSame(1, $first->fresh()->position);

        $this->actingAs($editor)->post(route('admin.projects.featured', $second))->assertRedirect();
        $this->assertTrue($second->fresh()->is_featured);
    }

    public function test_preview_is_available_to_editors_but_public_route_hides_draft(): void
    {
        $project = Project::factory()->draft()->create();
        $editor = $this->editor();

        $this->actingAs($editor)->get(route('admin.projects.preview', $project))->assertOk()->assertSee($project->name);
        $this->get(route('projects.show', $project->slug))->assertNotFound();
    }

    public function test_published_database_project_reaches_public_work_and_detail_pages(): void
    {
        config(['formiva.content_source' => 'database']);
        app()->instance(\App\Content\ContentRepository::class, new \App\Content\DatabaseContent());
        $project = Project::factory()->create(['name' => 'Public Northstar', 'slug' => 'public-northstar', 'status' => ProjectStatus::Published]);

        $content = new \App\Content\DatabaseContent();
        $projects = $content->projects();
        $resolved = $content->project($project->slug);
        $controller = new \App\Http\Controllers\HomeController($content);
        $detail = $controller->project($project->slug);

        $this->assertSame('Public Northstar', $resolved['name']);
        $this->assertTrue(collect($projects)->contains('slug', $project->slug));
        $this->assertStringContainsString('Public Northstar', $detail->render());
    }
}
