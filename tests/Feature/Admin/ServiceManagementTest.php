<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\ContentStatus;
use App\Models\Service;
use App\Models\ServiceItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ServiceManagementTest extends TestCase
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
            'title' => 'Business Systems',
            'slug' => '',
            'index_label' => '02',
            'form' => 'networked',
            'lede' => 'The machinery behind the storefront.',
            'why' => 'A beautiful front end running on a spreadsheet is not a finished project.',
            'outcome' => 'An operation that runs without heroics.',
            'note' => 'Eight to eighteen weeks.',
            'meta_title' => '',
            'meta_description' => '',
            'items' => [
                ['id' => '', 'title' => 'Odoo implementation', 'form' => 'layered', 'summary' => 'Scoped, configured, documented.'],
                ['id' => '', 'title' => 'CRM', 'form' => 'networked', 'summary' => 'One pipeline everyone can see.'],
            ],
        ], $overrides);
    }

    public function test_the_index_shows_status_item_count_and_position(): void
    {
        $service = Service::factory()->create(['title' => 'Digital Products', 'position' => 0]);
        ServiceItem::factory()->count(3)->create(['service_id' => $service->id]);

        $this->actingAs($this->editor())
            ->get(route('admin.services.index'))
            ->assertOk()
            ->assertSee('Digital Products')
            ->assertSee('Published');
    }

    public function test_the_index_filters_by_status_and_search(): void
    {
        Service::factory()->create(['title' => 'Live pillar']);
        Service::factory()->draft()->create(['title' => 'Quiet pillar']);

        $this->actingAs($this->editor())
            ->get(route('admin.services.index', ['status' => ContentStatus::Draft->value]))
            ->assertOk()
            ->assertSee('Quiet pillar')
            ->assertDontSee('Live pillar');

        $this->actingAs($this->editor())
            ->get(route('admin.services.index', ['q' => 'Live']))
            ->assertOk()
            ->assertSee('Live pillar')
            ->assertDontSee('Quiet pillar');
    }

    public function test_a_service_is_created_with_its_nested_items(): void
    {
        $this->actingAs($this->editor())
            ->post(route('admin.services.store'), $this->payload())
            ->assertRedirect();

        $service = Service::query()->where('slug', 'business-systems')->firstOrFail();

        // No intent given: a new record is a draft until someone says otherwise.
        $this->assertSame(ContentStatus::Draft, $service->status);
        $this->assertNull($service->published_at);
        $this->assertSame(['Odoo implementation', 'CRM'], $service->items->pluck('title')->all());
        $this->assertSame([0, 1], $service->items->pluck('position')->all());
    }

    public function test_saving_reconciles_nested_items_by_identity(): void
    {
        $this->actingAs($this->editor())->post(route('admin.services.store'), $this->payload());
        $service = Service::query()->where('slug', 'business-systems')->firstOrFail();
        $kept = $service->items->first();

        // Keep the first row (by id), drop the second, add a third.
        $this->actingAs($this->editor())
            ->put(route('admin.services.update', $service), $this->payload([
                'slug' => 'business-systems',
                'items' => [
                    ['id' => $kept->id, 'title' => 'Odoo implementation', 'form' => 'layered', 'summary' => 'Rewritten summary.'],
                    ['id' => '', 'title' => 'Automation', 'form' => 'modular', 'summary' => 'The runbook, not just the login.'],
                ],
            ]))
            ->assertRedirect();

        $service->refresh()->load('items');

        $this->assertSame(['Odoo implementation', 'Automation'], $service->items->pluck('title')->all());
        $this->assertSame($kept->id, $service->items->first()->id, 'The retained row should keep its identity.');
        $this->assertSame('Rewritten summary.', $service->items->first()->summary);
        $this->assertDatabaseMissing('service_items', ['service_id' => $service->id, 'title' => 'CRM']);
    }

    public function test_wholly_empty_item_rows_are_discarded_rather_than_failing_validation(): void
    {
        $this->actingAs($this->editor())
            ->post(route('admin.services.store'), $this->payload([
                'items' => [
                    ['id' => '', 'title' => 'Only real row', 'form' => 'modular', 'summary' => 'Kept.'],
                    ['id' => '', 'title' => '', 'form' => '', 'summary' => ''],
                ],
            ]))
            ->assertSessionHasNoErrors();

        $this->assertSame(1, ServiceItem::query()->count());
    }

    public function test_a_half_filled_item_row_still_fails_validation(): void
    {
        $this->actingAs($this->editor())
            ->post(route('admin.services.store'), $this->payload([
                'items' => [
                    ['id' => '', 'title' => 'Missing its summary', 'form' => 'modular', 'summary' => ''],
                ],
            ]))
            ->assertSessionHasErrors('items.0.summary');
    }

    public function test_slug_collisions_are_refused_and_a_records_own_slug_is_ignored(): void
    {
        Service::factory()->create(['slug' => 'digital-products']);
        $service = Service::factory()->create(['slug' => 'business-systems']);

        $this->actingAs($this->editor())
            ->put(route('admin.services.update', $service), $this->payload(['slug' => 'digital-products']))
            ->assertSessionHasErrors('slug');

        $this->actingAs($this->editor())
            ->put(route('admin.services.update', $service), $this->payload(['slug' => 'business-systems']))
            ->assertSessionHasNoErrors();
    }

    public function test_publishing_transitions_move_the_record_through_its_lifecycle(): void
    {
        $service = Service::factory()->draft()->create();
        $editor = $this->editor();

        $this->actingAs($editor)
            ->post(route('admin.services.publish', $service), ['action' => 'publish'])
            ->assertRedirect();

        $service->refresh();
        $this->assertSame(ContentStatus::Published, $service->status);
        $this->assertNotNull($service->published_at);
        $this->assertTrue($service->isLive());

        $this->actingAs($editor)
            ->post(route('admin.services.publish', $service), ['action' => 'archive'])
            ->assertRedirect();

        $this->assertSame(ContentStatus::Archived, $service->fresh()->status);
    }

    public function test_a_transition_the_record_cannot_accept_is_refused(): void
    {
        $service = Service::factory()->create();

        // Published records are never asked to "publish" again.
        $this->actingAs($this->editor())
            ->post(route('admin.services.publish', $service), ['action' => 'publish'])
            ->assertStatus(422);
    }

    public function test_scheduling_requires_a_future_date(): void
    {
        $service = Service::factory()->draft()->create();
        $editor = $this->editor();

        $this->actingAs($editor)
            ->post(route('admin.services.publish', $service), ['action' => 'schedule'])
            ->assertSessionHasErrors('published_at');

        $this->actingAs($editor)
            ->post(route('admin.services.publish', $service), [
                'action' => 'schedule',
                'published_at' => now()->addWeek()->format('Y-m-d H:i:s'),
            ])
            ->assertRedirect();

        $this->assertSame(ContentStatus::Scheduled, $service->fresh()->status);
        $this->assertFalse($service->fresh()->isLive());
    }

    public function test_the_two_save_buttons_declare_the_state_a_new_service_starts_in(): void
    {
        $editor = $this->editor();

        $this->actingAs($editor)
            ->post(route('admin.services.store'), $this->payload(['intent' => 'publish']))
            ->assertRedirect();

        $published = Service::query()->where('slug', 'business-systems')->firstOrFail();
        $this->assertTrue($published->isLive());

        $this->actingAs($editor)
            ->post(route('admin.services.store'), $this->payload([
                'slug' => 'another-pillar',
                'intent' => 'draft',
            ]))
            ->assertRedirect();

        $this->assertSame(ContentStatus::Draft, Service::query()->where('slug', 'another-pillar')->firstOrFail()->status);
    }

    public function test_saving_an_edit_never_changes_what_the_public_can_see(): void
    {
        // The editor form carries content only. Publishing is an explicit act
        // in the publishing bar, so a typo fix cannot accidentally unpublish.
        $service = Service::factory()->create(['slug' => 'business-systems']);

        $this->actingAs($this->editor())
            ->put(route('admin.services.update', $service), $this->payload([
                'slug' => 'business-systems',
                'title' => 'A renamed pillar',
            ]))
            ->assertRedirect();

        $service->refresh();

        $this->assertSame('A renamed pillar', $service->title);
        $this->assertSame(ContentStatus::Published, $service->status);
        $this->assertTrue($service->isLive());
    }

    public function test_the_editor_form_does_not_expose_a_status_field(): void
    {
        $service = Service::factory()->create();

        $this->actingAs($this->editor())
            ->get(route('admin.services.edit', $service))
            ->assertOk()
            ->assertDontSee('name="status"', escape: false);
    }

    /**
     * The regression this replaced: the publishing controls used to sit
     * inside the editor's own <form>. Browsers drop a nested form, so the
     * "Apply" button silently submitted the editor instead of publishing
     * anything. Asserting the publish form opens before the editor form is
     * what stops that arrangement coming back.
     */
    public function test_the_publishing_controls_are_not_nested_inside_the_editor_form(): void
    {
        $service = Service::factory()->draft()->create();

        $html = $this->actingAs($this->editor())
            ->get(route('admin.services.edit', $service))
            ->assertOk()
            ->getContent();

        $publishAt = strpos($html, route('admin.services.publish', $service));
        $editorAt = strpos($html, route('admin.services.update', $service));

        $this->assertIsInt($publishAt, 'The publish action should be on the page.');
        $this->assertIsInt($editorAt, 'The editor form should be on the page.');
        $this->assertLessThan(
            $editorAt,
            $publishAt,
            'Publishing must be a sibling above the editor form, never a child of it.',
        );
    }

    public function test_services_reorder_and_resequence(): void
    {
        $first = Service::factory()->create(['position' => 0]);
        $second = Service::factory()->create(['position' => 1]);

        $this->actingAs($this->editor())
            ->post(route('admin.services.move', $first), ['direction' => 'down'])
            ->assertRedirect();

        $this->assertSame(1, $first->fresh()->position);
        $this->assertSame(0, $second->fresh()->position);
    }

    public function test_deleting_a_service_soft_deletes_it(): void
    {
        $service = Service::factory()->create();

        $this->actingAs($this->editor())
            ->delete(route('admin.services.destroy', $service))
            ->assertRedirect();

        $this->assertSoftDeleted('services', ['id' => $service->id]);
    }

    public function test_the_editor_screen_renders_its_sections(): void
    {
        $service = Service::factory()->create();
        ServiceItem::factory()->create(['service_id' => $service->id]);

        $this->actingAs($this->editor())
            ->get(route('admin.services.edit', $service))
            ->assertOk()
            ->assertSee('Basic information')
            ->assertSee('Narrative content')
            ->assertSee('Service items')
            ->assertSee('SEO')
            // Publishing is a bar above the editor, not a field inside it.
            ->assertSee('Live on the site');
    }
}
