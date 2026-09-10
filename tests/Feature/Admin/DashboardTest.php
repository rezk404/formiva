<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\SettingType;
use App\Models\Client;
use App\Models\Insight;
use App\Models\Project;
use App\Models\Service;
use App\Models\Setting;
use App\Models\StudioPosition;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_workspace_counts_come_from_the_database(): void
    {
        $user = User::factory()->admin()->create();
        Project::factory()->count(2)->create();
        Client::factory()->create();
        Insight::factory()->count(3)->create();
        Service::factory()->create();
        TeamMember::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee($user->name)
            ->assertSee('Projects')
            ->assertSee('Clients')
            ->assertSee('Insights')
            ->assertSee('Services')
            ->assertSee('Studio');
    }

    public function test_content_health_reports_the_publishing_split(): void
    {
        User::factory()->admin()->create();
        Insight::factory()->count(2)->create();
        Insight::factory()->draft()->create();
        Insight::factory()->scheduled()->create();

        $response = $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.dashboard'))
            ->assertOk();

        $counts = $response->viewData('health')['Insights']['counts'];

        $this->assertSame(4, $counts['total']);
        $this->assertSame(2, $counts['published']);
        $this->assertSame(1, $counts['draft']);
        $this->assertSame(1, $counts['scheduled']);
        $this->assertSame(0, $counts['archived']);
    }

    /**
     * A workspace that has been set up and has nothing outstanding shows an
     * empty panel. "Set up" is part of the fixture on purpose: an unsaved
     * site record and a studio with no positions are themselves real signals,
     * and the panel is supposed to say so.
     */
    public function test_the_attention_panel_is_empty_when_nothing_needs_a_decision(): void
    {
        $admin = User::factory()->admin()->create();

        StudioPosition::query()->create(['index_label' => '01', 'title' => 'A position', 'body' => 'Its reason.', 'position' => 0]);
        Setting::put('content', 'site', ['brand' => ['name' => 'FORMIVA']], SettingType::Json);

        $clean = $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();

        $this->assertSame([], $clean->viewData('attention'));
        $clean->assertSee('Nothing is waiting');
    }

    public function test_the_attention_panel_reports_drafts_and_overdue_schedules(): void
    {
        $admin = User::factory()->admin()->create();

        StudioPosition::query()->create(['index_label' => '01', 'title' => 'A position', 'body' => 'Its reason.', 'position' => 0]);
        Setting::put('content', 'site', ['brand' => ['name' => 'FORMIVA']], SettingType::Json);

        Insight::factory()->draft()->create();
        Insight::factory()->due()->create();

        $titles = array_column(
            $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk()->viewData('attention'),
            'title',
        );

        $this->assertContains('1 draft waiting', $titles);
        $this->assertContains('1 scheduled item past due', $titles);
    }

    public function test_a_studio_with_no_positions_is_itself_a_signal(): void
    {
        Setting::put('content', 'site', ['brand' => ['name' => 'FORMIVA']], SettingType::Json);

        $titles = array_column(
            $this->actingAs(User::factory()->admin()->create())
                ->get(route('admin.dashboard'))
                ->assertOk()
                ->viewData('attention'),
            'title',
        );

        $this->assertContains('The studio has no positions', $titles);
    }

    public function test_quick_actions_are_filtered_by_what_the_role_can_do(): void
    {
        $editorActions = $this->actingAs(User::factory()->create())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->viewData('actions');

        $adminActions = $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->viewData('actions');

        $this->assertNotContains('Site settings', array_column($editorActions, 'label'));
        $this->assertContains('Site settings', array_column($adminActions, 'label'));
        $this->assertContains('Write an insight', array_column($editorActions, 'label'));
        $this->assertContains('Edit About', array_column($editorActions, 'label'));
    }

    public function test_recent_content_lists_what_was_touched_last(): void
    {
        $admin = User::factory()->admin()->create();
        Insight::factory()->create(['title' => 'Older entry', 'updated_at' => now()->subWeek()]);
        Insight::factory()->create(['title' => 'Newest entry', 'updated_at' => now()]);

        // One merged timeline: insights and services in the order they were
        // touched, not three lists stacked.
        $recent = $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->viewData('recent');

        $this->assertSame('Newest entry', $recent->first()['title']);
        $this->assertSame('Insight', $recent->first()['kind']);
    }

    public function test_recent_activity_merges_services_into_the_same_timeline(): void
    {
        $admin = User::factory()->admin()->create();
        Insight::factory()->create(['title' => 'An older note', 'updated_at' => now()->subWeek()]);
        Service::factory()->create(['title' => 'A newer pillar', 'updated_at' => now()]);

        $recent = $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->viewData('recent');

        $this->assertSame('A newer pillar', $recent->first()['title']);
        $this->assertSame('Service', $recent->first()['kind']);
    }
}
