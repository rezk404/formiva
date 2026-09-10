<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TeamManagementTest extends TestCase
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
            'name' => 'Inês Varela',
            'slug' => '',
            'role' => 'Founder, Creative Director',
            'bio' => 'Trained as an architect, and still designs like one.',
            'since_year' => '2016',
            'email' => 'ines@formiva.test',
            'plate_seed' => '',
            'plate_variant' => '',
            'plate_ratio' => '',
            'alt' => '',
            'is_published' => '1',
        ], $overrides);
    }

    public function test_the_index_renders_people_as_a_grid_with_a_placeholder_portrait(): void
    {
        TeamMember::factory()->create(['name' => 'Tobias Renn', 'role' => 'Technical Director']);

        $this->actingAs($this->editor())
            ->get(route('admin.team.index'))
            ->assertOk()
            ->assertSee('Tobias Renn')
            ->assertSee('Technical Director')
            ->assertSee('admin-person__plate', escape: false);
    }

    public function test_the_published_filter_and_search_narrow_the_list(): void
    {
        TeamMember::factory()->create(['name' => 'Listed person']);
        TeamMember::factory()->hidden()->create(['name' => 'Hidden person']);

        $editor = $this->editor();

        $this->actingAs($editor)
            ->get(route('admin.team.index', ['state' => 'hidden']))
            ->assertOk()
            ->assertSee('Hidden person')
            ->assertDontSee('Listed person');

        $this->actingAs($editor)
            ->get(route('admin.team.index', ['q' => 'Listed']))
            ->assertOk()
            ->assertSee('Listed person')
            ->assertDontSee('Hidden person');
    }

    public function test_a_person_can_be_added_with_a_derived_slug(): void
    {
        $this->actingAs($this->editor())
            ->post(route('admin.team.store'), $this->payload())
            ->assertRedirect();

        $this->assertDatabaseHas('team_members', [
            'slug' => 'ines-varela',
            'role' => 'Founder, Creative Director',
            'is_published' => true,
            'position' => 0,
        ]);
    }

    public function test_an_empty_plate_is_allowed_and_the_public_shape_still_resolves(): void
    {
        $this->actingAs($this->editor())
            ->post(route('admin.team.store'), $this->payload())
            ->assertSessionHasNoErrors();

        $member = TeamMember::query()->firstOrFail();

        $this->assertNull($member->plate_seed);

        // DatabaseContent fills the gap deterministically from the slug.
        $shape = (new \App\Content\DatabaseContent())->team()[0];

        $this->assertIsInt($shape['plate']['seed']);
        $this->assertSame('ink', $shape['plate']['variant']);
        $this->assertSame('3/4', $shape['plate']['ratio']);
        $this->assertStringContainsString('Inês Varela', $shape['alt']);
    }

    public function test_slug_uniqueness_is_enforced_and_self_ignored(): void
    {
        TeamMember::factory()->create(['slug' => 'taken']);
        $member = TeamMember::factory()->create(['slug' => 'mine']);

        $this->actingAs($this->editor())
            ->post(route('admin.team.store'), $this->payload(['slug' => 'taken']))
            ->assertSessionHasErrors('slug');

        $this->actingAs($this->editor())
            ->put(route('admin.team.update', $member), $this->payload(['slug' => 'mine']))
            ->assertSessionHasNoErrors();
    }

    public function test_required_fields_are_enforced(): void
    {
        $this->actingAs($this->editor())
            ->post(route('admin.team.store'), [])
            ->assertSessionHasErrors(['name', 'role', 'bio', 'since_year']);
    }

    public function test_visibility_can_be_toggled(): void
    {
        $member = TeamMember::factory()->hidden()->create();

        $this->actingAs($this->editor())
            ->post(route('admin.team.toggle', $member))
            ->assertRedirect();

        $this->assertTrue($member->fresh()->is_published);
    }

    public function test_people_reorder_and_the_public_index_follows(): void
    {
        $first = TeamMember::factory()->create(['position' => 0, 'name' => 'First']);
        $second = TeamMember::factory()->create(['position' => 1, 'name' => 'Second']);

        $this->actingAs($this->editor())
            ->post(route('admin.team.move', $second), ['direction' => 'up'])
            ->assertRedirect();

        $team = (new \App\Content\DatabaseContent())->team();

        $this->assertSame('Second', $team[0]['name']);
        $this->assertSame('01', $team[0]['index']);
        $this->assertSame('First', $team[1]['name']);
        $this->assertSame('02', $team[1]['index']);
        $this->assertSame($second->id, $second->fresh()->id);
    }

    public function test_deleting_a_person_soft_deletes_and_resequences(): void
    {
        $people = collect(range(0, 2))->map(fn (int $i) => TeamMember::factory()->create(['position' => $i]));

        $this->actingAs($this->editor())
            ->delete(route('admin.team.destroy', $people[1]))
            ->assertRedirect();

        $this->assertSoftDeleted('team_members', ['id' => $people[1]->id]);
        $this->assertSame([0, 1], TeamMember::query()->ordered()->pluck('position')->all());
    }
}
