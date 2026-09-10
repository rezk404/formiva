<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\CategoryType;
use App\Models\Category;
use App\Models\Insight;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    private function editor(): User
    {
        return User::factory()->create();
    }

    public function test_the_index_lists_categories_with_their_attachment_counts(): void
    {
        $category = Category::factory()->insight()->create(['name' => 'Systems thinking']);
        Insight::factory()->count(2)->create(['category_id' => $category->id]);

        $this->actingAs($this->editor())
            ->get(route('admin.categories.index'))
            ->assertOk()
            ->assertSee('Systems thinking')
            ->assertSee('2 insights');
    }

    public function test_search_and_type_filter_narrow_the_list(): void
    {
        Category::factory()->insight()->create(['name' => 'Motion', 'slug' => 'motion']);
        Category::factory()->project()->create(['name' => 'Commerce', 'slug' => 'commerce']);

        $this->actingAs($this->editor())
            ->get(route('admin.categories.index', ['q' => 'moti']))
            ->assertOk()
            ->assertSee('Motion')
            ->assertDontSee('Commerce');

        $this->actingAs($this->editor())
            ->get(route('admin.categories.index', ['type' => CategoryType::Project->value]))
            ->assertOk()
            ->assertSee('Commerce')
            ->assertDontSee('Motion');
    }

    public function test_a_category_can_be_created_with_a_derived_slug(): void
    {
        $this->actingAs($this->editor())
            ->post(route('admin.categories.store'), [
                'name' => 'Business Systems',
                'slug' => '',
                'type' => CategoryType::Insight->value,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('categories', [
            'name' => 'Business Systems',
            'slug' => 'business-systems',
            'type' => CategoryType::Insight->value,
        ]);
    }

    public function test_slugs_are_unique_per_type_but_may_repeat_across_types(): void
    {
        Category::factory()->insight()->create(['slug' => 'systems', 'name' => 'Systems']);

        // Same type, same slug — refused.
        $this->actingAs($this->editor())
            ->post(route('admin.categories.store'), [
                'name' => 'Systems',
                'slug' => 'systems',
                'type' => CategoryType::Insight->value,
            ])
            ->assertSessionHasErrors('slug');

        // Different type — allowed, because the table keys on (type, slug).
        $this->actingAs($this->editor())
            ->post(route('admin.categories.store'), [
                'name' => 'Systems',
                'slug' => 'systems',
                'type' => CategoryType::Project->value,
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame(2, Category::query()->where('slug', 'systems')->count());
    }

    public function test_updating_a_category_ignores_its_own_slug_when_checking_uniqueness(): void
    {
        $category = Category::factory()->insight()->create(['slug' => 'craft', 'name' => 'Craft']);

        $this->actingAs($this->editor())
            ->put(route('admin.categories.update', $category), [
                'name' => 'Craft & making',
                'slug' => 'craft',
                'type' => CategoryType::Insight->value,
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('Craft & making', $category->fresh()->name);
    }

    public function test_an_unused_category_can_be_deleted(): void
    {
        $category = Category::factory()->insight()->create();

        $this->actingAs($this->editor())
            ->delete(route('admin.categories.destroy', $category))
            ->assertRedirect();

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_a_category_still_in_use_is_refused_rather_than_orphaning_content(): void
    {
        $category = Category::factory()->insight()->create();
        Insight::factory()->create(['category_id' => $category->id]);

        $this->actingAs($this->editor())
            ->delete(route('admin.categories.destroy', $category))
            ->assertSessionHasErrors('category');

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_categories_reorder_within_their_own_type(): void
    {
        $first = Category::factory()->insight()->create(['position' => 0, 'name' => 'First']);
        $second = Category::factory()->insight()->create(['position' => 1, 'name' => 'Second']);

        $this->actingAs($this->editor())
            ->post(route('admin.categories.move', $second), ['direction' => 'up'])
            ->assertRedirect();

        $this->assertSame(0, $second->fresh()->position);
        $this->assertSame(1, $first->fresh()->position);
    }

    public function test_positions_are_resequenced_after_a_delete(): void
    {
        $categories = collect(range(0, 2))->map(
            fn (int $index) => Category::factory()->insight()->create(['position' => $index]),
        );

        $this->actingAs($this->editor())
            ->delete(route('admin.categories.destroy', $categories[0]))
            ->assertRedirect();

        $this->assertSame([0, 1], Category::query()->ordered()->pluck('position')->all());
    }

    public function test_a_messy_slug_is_normalised_rather_than_rejected(): void
    {
        $this->actingAs($this->editor())
            ->post(route('admin.categories.store'), [
                'name' => 'Bad',
                'slug' => '  Not A Slug!  ',
                'type' => CategoryType::Insight->value,
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('categories', ['name' => 'Bad', 'slug' => 'not-a-slug']);
    }

    public function test_a_category_needs_a_name_and_a_valid_type(): void
    {
        $this->actingAs($this->editor())
            ->post(route('admin.categories.store'), ['name' => '', 'type' => 'nonsense'])
            ->assertSessionHasErrors(['name', 'type']);
    }
}
