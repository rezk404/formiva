<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Client;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TestimonialManagementTest extends TestCase
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
            'quote' => 'They killed our favourite idea in week two, and they were right.',
            'author_name' => 'Marta Feld',
            'author_role' => 'VP Product',
            'company' => 'Kiln',
            'client_id' => '',
            'project_id' => '',
            'is_published' => '1',
        ], $overrides);
    }

    public function test_the_index_makes_publication_state_obvious(): void
    {
        Testimonial::factory()->create(['author_name' => 'Live voice']);
        Testimonial::factory()->hidden()->create(['author_name' => 'Quiet voice']);

        $this->actingAs($this->editor())
            ->get(route('admin.testimonials.index'))
            ->assertOk()
            ->assertSee('Live voice')
            ->assertSee('Quiet voice')
            ->assertSee('Hidden');
    }

    public function test_the_publication_filter_narrows_the_list(): void
    {
        Testimonial::factory()->create(['author_name' => 'Live voice']);
        Testimonial::factory()->hidden()->create(['author_name' => 'Quiet voice']);

        $this->actingAs($this->editor())
            ->get(route('admin.testimonials.index', ['state' => 'hidden']))
            ->assertOk()
            ->assertSee('Quiet voice')
            ->assertDontSee('Live voice');
    }

    public function test_search_matches_quote_author_and_company(): void
    {
        Testimonial::factory()->create(['author_name' => 'Marta Feld', 'company' => 'Kiln']);
        Testimonial::factory()->create(['author_name' => 'Jonas Weir', 'company' => 'Meridian']);

        $this->actingAs($this->editor())
            ->get(route('admin.testimonials.index', ['q' => 'Kiln']))
            ->assertOk()
            ->assertSee('Marta Feld')
            ->assertDontSee('Jonas Weir');
    }

    public function test_a_quote_can_be_created_and_linked_to_a_client(): void
    {
        $client = Client::factory()->create();

        $this->actingAs($this->editor())
            ->post(route('admin.testimonials.store'), $this->payload(['client_id' => $client->id]))
            ->assertRedirect();

        $this->assertDatabaseHas('testimonials', [
            'author_name' => 'Marta Feld',
            'client_id' => $client->id,
            'is_published' => true,
            'position' => 0,
        ]);
    }

    public function test_an_unknown_relationship_is_refused(): void
    {
        $this->actingAs($this->editor())
            ->post(route('admin.testimonials.store'), $this->payload(['project_id' => 9999]))
            ->assertSessionHasErrors('project_id');
    }

    public function test_required_fields_are_enforced(): void
    {
        $this->actingAs($this->editor())
            ->post(route('admin.testimonials.store'), [])
            ->assertSessionHasErrors(['quote', 'author_name', 'author_role', 'company']);
    }

    public function test_publication_can_be_toggled_from_the_list(): void
    {
        $testimonial = Testimonial::factory()->hidden()->create();

        $this->actingAs($this->editor())
            ->post(route('admin.testimonials.toggle', $testimonial))
            ->assertRedirect();

        $this->assertTrue($testimonial->fresh()->is_published);
    }

    public function test_quotes_reorder(): void
    {
        $first = Testimonial::factory()->create(['position' => 0]);
        $second = Testimonial::factory()->create(['position' => 1]);

        $this->actingAs($this->editor())
            ->post(route('admin.testimonials.move', $second), ['direction' => 'up'])
            ->assertRedirect();

        $this->assertSame(0, $second->fresh()->position);
        $this->assertSame(1, $first->fresh()->position);
    }

    public function test_deleting_a_quote_soft_deletes_and_resequences(): void
    {
        $quotes = collect(range(0, 2))->map(fn (int $i) => Testimonial::factory()->create(['position' => $i]));

        $this->actingAs($this->editor())
            ->delete(route('admin.testimonials.destroy', $quotes[0]))
            ->assertRedirect();

        $this->assertSoftDeleted('testimonials', ['id' => $quotes[0]->id]);
        $this->assertSame([0, 1], Testimonial::query()->ordered()->pluck('position')->all());
    }
}
