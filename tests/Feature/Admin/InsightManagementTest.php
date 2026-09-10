<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\ContentStatus;
use App\Models\Category;
use App\Models\Insight;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class InsightManagementTest extends TestCase
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
            'title' => 'ERP is an org-chart problem',
            'slug' => '',
            'index_label' => '01',
            'dek' => 'Most implementations fail in the first meeting.',
            'body' => str_repeat('The software is rarely why a rollout stalls. ', 30),
            'reading_minutes' => '',
            'category_id' => '',
            'author_id' => '',
            'plate_seed' => '',
            'plate_variant' => 'ink',
            'plate_ratio' => '16/10',
            'alt' => 'Interlocking rectilinear bands forming a load-bearing frame.',
            'meta_title' => '',
            'meta_description' => '',
        ], $overrides);
    }

    public function test_the_index_shows_category_author_status_and_dates(): void
    {
        $author = User::factory()->create(['name' => 'Amara Osei']);
        $category = Category::factory()->insight()->create(['name' => 'Systems']);

        Insight::factory()->create([
            'title' => 'Tokens are a contract',
            'category_id' => $category->id,
            'author_id' => $author->id,
        ]);

        $this->actingAs($this->editor())
            ->get(route('admin.insights.index'))
            ->assertOk()
            ->assertSee('Tokens are a contract')
            ->assertSee('Systems')
            ->assertSee('Amara Osei')
            ->assertSee('Published');
    }

    public function test_the_index_filters_by_status_category_and_author(): void
    {
        $category = Category::factory()->insight()->create(['name' => 'Motion']);
        $author = User::factory()->create(['name' => 'Casper Lund']);

        Insight::factory()->create(['title' => 'Wanted entry', 'category_id' => $category->id, 'author_id' => $author->id]);
        Insight::factory()->draft()->create(['title' => 'Other entry']);

        $editor = $this->editor();

        $this->actingAs($editor)
            ->get(route('admin.insights.index', ['status' => ContentStatus::Draft->value]))
            ->assertOk()
            ->assertSee('Other entry')
            ->assertDontSee('Wanted entry');

        $this->actingAs($editor)
            ->get(route('admin.insights.index', ['category' => $category->id]))
            ->assertOk()
            ->assertSee('Wanted entry')
            ->assertDontSee('Other entry');

        $this->actingAs($editor)
            ->get(route('admin.insights.index', ['author' => $author->id]))
            ->assertOk()
            ->assertSee('Wanted entry')
            ->assertDontSee('Other entry');
    }

    public function test_search_matches_title_and_standfirst(): void
    {
        Insight::factory()->create(['title' => 'Motion has a budget', 'dek' => 'Every animation spends attention.']);
        Insight::factory()->create(['title' => 'Against the hero video', 'dek' => 'Four megabytes.']);

        $this->actingAs($this->editor())
            ->get(route('admin.insights.index', ['q' => 'budget']))
            ->assertOk()
            ->assertSee('Motion has a budget')
            ->assertDontSee('Against the hero video');
    }

    public function test_an_entry_is_created_with_derived_reading_time_and_plate_seed(): void
    {
        $this->actingAs($this->editor())
            ->post(route('admin.insights.store'), $this->payload())
            ->assertRedirect();

        $insight = Insight::query()->where('slug', 'erp-is-an-org-chart-problem')->firstOrFail();

        $this->assertGreaterThan(0, $insight->reading_minutes);
        $this->assertGreaterThanOrEqual(1000, $insight->plate_seed);
        $this->assertLessThanOrEqual(9999, $insight->plate_seed);
        $this->assertSame(ContentStatus::Draft, $insight->status);
    }

    public function test_the_body_is_stored_verbatim_so_the_public_entry_renders_unchanged(): void
    {
        $body = 'Map the disagreement before you map the modules. Nothing here is markup.';

        $this->actingAs($this->editor())
            ->post(route('admin.insights.store'), $this->payload(['body' => $body]))
            ->assertRedirect();

        $this->assertSame($body, Insight::query()->firstOrFail()->body);
    }

    public function test_the_category_must_be_an_insight_category(): void
    {
        $projectCategory = Category::factory()->project()->create();

        $this->actingAs($this->editor())
            ->post(route('admin.insights.store'), $this->payload(['category_id' => $projectCategory->id]))
            ->assertSessionHasErrors('category_id');
    }

    public function test_slug_uniqueness_holds_on_create_and_ignores_self_on_update(): void
    {
        Insight::factory()->create(['slug' => 'taken']);
        $insight = Insight::factory()->create(['slug' => 'mine']);

        $this->actingAs($this->editor())
            ->post(route('admin.insights.store'), $this->payload(['slug' => 'taken']))
            ->assertSessionHasErrors('slug');

        $this->actingAs($this->editor())
            ->put(route('admin.insights.update', $insight), $this->payload(['slug' => 'mine']))
            ->assertSessionHasNoErrors();
    }

    public function test_required_fields_are_enforced(): void
    {
        $this->actingAs($this->editor())
            ->post(route('admin.insights.store'), [])
            ->assertSessionHasErrors(['title', 'index_label', 'dek', 'body', 'alt', 'plate_variant', 'plate_ratio']);
    }

    public function test_the_two_save_buttons_declare_the_state_a_new_entry_starts_in(): void
    {
        $editor = $this->editor();

        $this->actingAs($editor)
            ->post(route('admin.insights.store'), $this->payload(['intent' => 'publish']))
            ->assertRedirect();

        $this->assertTrue(Insight::query()->firstOrFail()->isLive());

        $this->actingAs($editor)
            ->post(route('admin.insights.store'), $this->payload(['slug' => 'a-second-note', 'intent' => 'draft']))
            ->assertRedirect();

        $this->assertSame(
            ContentStatus::Draft,
            Insight::query()->where('slug', 'a-second-note')->firstOrFail()->status,
        );
    }

    public function test_saving_an_edit_never_changes_what_the_public_can_see(): void
    {
        $insight = Insight::factory()->create(['slug' => 'mine']);

        $this->actingAs($this->editor())
            ->put(route('admin.insights.update', $insight), $this->payload([
                'slug' => 'mine',
                'title' => 'A corrected headline',
            ]))
            ->assertRedirect();

        $insight->refresh();

        $this->assertSame('A corrected headline', $insight->title);
        $this->assertSame(ContentStatus::Published, $insight->status);
        $this->assertTrue($insight->isLive());
    }

    public function test_publishing_scheduling_and_archiving_work_end_to_end(): void
    {
        $insight = Insight::factory()->draft()->create();
        $editor = $this->editor();

        $this->actingAs($editor)
            ->post(route('admin.insights.publish', $insight), [
                'action' => 'schedule',
                'published_at' => now()->addDays(3)->format('Y-m-d H:i:s'),
            ])
            ->assertRedirect();

        $this->assertSame(ContentStatus::Scheduled, $insight->fresh()->status);
        $this->assertSame(1, Insight::query()->scheduled()->count());
        $this->assertSame(0, Insight::query()->published()->count());

        $this->actingAs($editor)
            ->post(route('admin.insights.publish', $insight), ['action' => 'publish'])
            ->assertRedirect();

        $this->assertSame(1, Insight::query()->published()->count());

        $this->actingAs($editor)
            ->post(route('admin.insights.publish', $insight), ['action' => 'archive'])
            ->assertRedirect();

        $this->assertSame(ContentStatus::Archived, $insight->fresh()->status);
        $this->assertSame(0, Insight::query()->published()->count());
    }

    public function test_deleting_an_entry_soft_deletes_it(): void
    {
        $insight = Insight::factory()->create();

        $this->actingAs($this->editor())
            ->delete(route('admin.insights.destroy', $insight))
            ->assertRedirect();

        $this->assertSoftDeleted('insights', ['id' => $insight->id]);
    }

    public function test_pagination_keeps_the_query_string(): void
    {
        Insight::factory()->count(20)->draft()->create();

        $this->actingAs($this->editor())
            ->get(route('admin.insights.index', ['status' => ContentStatus::Draft->value, 'page' => 2]))
            ->assertOk()
            ->assertSee('status='.ContentStatus::Draft->value, escape: false);
    }

    public function test_the_editor_screen_renders_its_sections(): void
    {
        $insight = Insight::factory()->create();

        $this->actingAs($this->editor())
            ->get(route('admin.insights.edit', $insight))
            ->assertOk()
            ->assertSee('Basic information')
            ->assertSee('Article content')
            ->assertSee('Classification')
            ->assertSee('SEO')
            // Publishing is a bar above the editor, not a field inside it.
            ->assertSee('Live on the site');
    }
}
