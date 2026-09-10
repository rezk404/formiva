<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ContentStatus;
use App\Models\Insight;
use App\Models\Service;
use App\Support\Publishing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * The publishing rules, tested away from any screen.
 *
 * The important guarantee is negative: a visitor must never see something
 * before its moment, whatever route the record took to get there.
 */
final class PublishingLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_valid_transitions_are_offered_for_each_state(): void
    {
        $this->assertSame(
            [Publishing::PUBLISH, Publishing::SCHEDULE, Publishing::ARCHIVE],
            Publishing::availableFor(ContentStatus::Draft),
        );

        $this->assertNotContains(Publishing::PUBLISH, Publishing::availableFor(ContentStatus::Published));
        $this->assertNotContains(Publishing::UNPUBLISH, Publishing::availableFor(ContentStatus::Archived));
    }

    public function test_republishing_an_archived_entry_keeps_its_original_date(): void
    {
        $dated = Carbon::parse('2026-02-01 09:00:00');
        $insight = Insight::factory()->archived()->create(['published_at' => $dated]);

        Publishing::apply($insight, Publishing::PUBLISH);

        $this->assertSame(ContentStatus::Published, $insight->fresh()->status);
        $this->assertTrue($insight->fresh()->published_at->equalTo($dated), 'The journal should not reshuffle on restore.');
    }

    public function test_a_draft_never_appears_in_the_published_scope(): void
    {
        Insight::factory()->draft()->create();
        Service::factory()->draft()->create();

        $this->assertSame(0, Insight::query()->published()->count());
        $this->assertSame(0, Service::query()->published()->count());
    }

    public function test_scheduled_content_stays_hidden_until_it_is_promoted(): void
    {
        $insight = Insight::factory()->scheduled()->create();

        $this->assertSame(0, Insight::query()->published()->count());
        $this->assertSame(1, Insight::query()->scheduled()->count());
        $this->assertFalse($insight->isLive());
    }

    public function test_a_published_record_with_a_future_date_is_still_hidden(): void
    {
        // The belt to the scheduling braces: even mislabelled, the date wins
        // in the query the public site actually runs.
        Insight::factory()->create([
            'status' => ContentStatus::Published,
            'published_at' => now()->addMonth(),
        ]);

        $this->assertSame(0, Insight::query()->published()->count());
    }

    public function test_the_publish_due_command_promotes_only_what_is_due(): void
    {
        $due = Insight::factory()->due()->create();
        $later = Insight::factory()->scheduled()->create();
        $dueService = Service::factory()->due()->create();

        $this->artisan('formiva:publish-due')->assertSuccessful();

        $this->assertSame(ContentStatus::Published, $due->fresh()->status);
        $this->assertSame(ContentStatus::Scheduled, $later->fresh()->status);
        $this->assertSame(ContentStatus::Published, $dueService->fresh()->status);
        $this->assertSame(1, Insight::query()->published()->count());
    }

    public function test_the_publish_due_command_can_be_run_as_a_dry_run(): void
    {
        $due = Insight::factory()->due()->create();

        $this->artisan('formiva:publish-due', ['--dry-run' => true])->assertSuccessful();

        $this->assertSame(ContentStatus::Scheduled, $due->fresh()->status);
    }

    public function test_the_command_says_so_when_nothing_is_due(): void
    {
        $this->artisan('formiva:publish-due')
            ->expectsOutputToContain('Nothing is due.')
            ->assertSuccessful();
    }

    public function test_reconcile_turns_a_future_publish_date_into_a_schedule(): void
    {
        $attributes = Publishing::reconcile([
            'status' => ContentStatus::Published,
            'published_at' => now()->addWeek()->toDateTimeString(),
        ]);

        $this->assertSame(ContentStatus::Scheduled, $attributes['status']);
    }

    public function test_reconcile_drops_the_date_from_a_draft(): void
    {
        $attributes = Publishing::reconcile([
            'status' => ContentStatus::Draft,
            'published_at' => now()->toDateTimeString(),
        ]);

        $this->assertNull($attributes['published_at']);
    }

    public function test_reconcile_dates_a_publish_that_arrived_without_one(): void
    {
        $attributes = Publishing::reconcile([
            'status' => ContentStatus::Published,
            'published_at' => null,
        ]);

        $this->assertSame(ContentStatus::Published, $attributes['status']);
        $this->assertNotNull($attributes['published_at']);
    }

    public function test_a_schedule_with_no_moment_falls_back_to_draft(): void
    {
        $attributes = Publishing::reconcile([
            'status' => ContentStatus::Scheduled,
            'published_at' => null,
        ]);

        $this->assertSame(ContentStatus::Draft, $attributes['status']);
    }
}
