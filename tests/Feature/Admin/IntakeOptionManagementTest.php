<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Content\ContentImporter;
use App\Content\DatabaseContent;
use App\Enums\IntakeOptionKind;
use App\Models\IntakeOption;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class IntakeOptionManagementTest extends TestCase
{
    use RefreshDatabase;

    private function editor(): User
    {
        return User::factory()->create();
    }

    public function test_the_index_groups_options_by_the_step_they_are_asked_on(): void
    {
        (new ContentImporter(resource_path('content')))->import();

        $this->actingAs($this->editor())
            ->get(route('admin.intake.index'))
            ->assertOk()
            ->assertSee('Project types')
            ->assertSee('Budget ranges')
            ->assertSee('Timelines')
            ->assertSee('Corporate website');
    }

    public function test_an_option_is_created_at_the_end_of_its_own_step(): void
    {
        IntakeOption::query()->create([
            'kind' => IntakeOptionKind::Timeline, 'group' => IntakeOption::SHARED_GROUP,
            'value' => 'asap', 'label' => 'ASAP', 'position' => 0, 'is_active' => true,
        ]);

        $this->actingAs($this->editor())
            ->post(route('admin.intake.store'), [
                'kind' => IntakeOptionKind::Timeline->value,
                'label' => 'Flexible',
                'value' => '',
                'group' => '',
                'is_active' => '1',
            ])
            ->assertRedirect();

        $option = IntakeOption::query()->where('label', 'Flexible')->firstOrFail();

        $this->assertSame('flexible', $option->value);
        $this->assertSame(IntakeOption::SHARED_GROUP, $option->group);
        $this->assertSame(1, $option->position);
    }

    public function test_the_same_budget_range_may_be_offered_under_two_project_groups(): void
    {
        $editor = $this->editor();

        foreach (['web', 'experience'] as $group) {
            $this->actingAs($editor)
                ->post(route('admin.intake.store'), [
                    'kind' => IntakeOptionKind::Budget->value,
                    'label' => 'EGP 20k–50k',
                    'value' => 'EGP 20k–50k',
                    'group' => $group,
                    'is_active' => '1',
                ])
                ->assertSessionHasNoErrors();
        }

        $this->assertSame(2, IntakeOption::query()->where('kind', IntakeOptionKind::Budget)->count());
        $this->assertSame(
            ['experience:EGP 20k–50k', 'web:EGP 20k–50k'],
            IntakeOption::query()->where('kind', IntakeOptionKind::Budget)->orderBy('value')->pluck('value')->all(),
        );
    }

    public function test_a_duplicate_value_within_one_step_and_group_is_refused(): void
    {
        IntakeOption::query()->create([
            'kind' => IntakeOptionKind::Timeline, 'group' => IntakeOption::SHARED_GROUP,
            'value' => 'asap', 'label' => 'ASAP', 'position' => 0, 'is_active' => true,
        ]);

        $this->actingAs($this->editor())
            ->post(route('admin.intake.store'), [
                'kind' => IntakeOptionKind::Timeline->value,
                'label' => 'ASAP',
                'value' => 'asap',
                'group' => '',
                'is_active' => '1',
            ])
            ->assertSessionHasErrors('value');
    }

    public function test_the_public_intake_shape_is_rebuilt_from_the_rows(): void
    {
        (new ContentImporter(resource_path('content')))->import();

        $intake = (new DatabaseContent())->intake();

        $this->assertSame(
            ['projectTypes', 'budgets', 'timelines', 'companySizes', 'services', 'rules'],
            array_keys($intake),
        );
        $this->assertSame(['value', 'label', 'group'], array_keys($intake['projectTypes'][0]));
        $this->assertArrayHasKey('web', $intake['budgets']);
        $this->assertIsString($intake['budgets']['web'][0]);
        $this->assertIsString($intake['timelines'][0]);
        $this->assertArrayHasKey('fit', $intake['rules']);
    }

    public function test_withdrawing_an_option_removes_it_from_the_public_form(): void
    {
        (new ContentImporter(resource_path('content')))->import();

        $option = IntakeOption::query()
            ->where('kind', IntakeOptionKind::Timeline)
            ->orderBy('position')
            ->firstOrFail();

        $this->assertContains($option->label, (new DatabaseContent())->intake()['timelines']);

        $this->actingAs($this->editor())
            ->post(route('admin.intake.toggle', $option))
            ->assertRedirect();

        $this->assertFalse($option->fresh()->is_active);
        $this->assertNotContains($option->label, (new DatabaseContent())->intake()['timelines']);
    }

    public function test_options_reorder_within_their_step(): void
    {
        $first = IntakeOption::query()->create([
            'kind' => IntakeOptionKind::Timeline, 'group' => IntakeOption::SHARED_GROUP,
            'value' => 'a', 'label' => 'First', 'position' => 0, 'is_active' => true,
        ]);
        $second = IntakeOption::query()->create([
            'kind' => IntakeOptionKind::Timeline, 'group' => IntakeOption::SHARED_GROUP,
            'value' => 'b', 'label' => 'Second', 'position' => 1, 'is_active' => true,
        ]);

        $this->actingAs($this->editor())
            ->post(route('admin.intake.move', $second), ['direction' => 'up'])
            ->assertRedirect();

        $this->assertSame(['Second', 'First'], (new DatabaseContent())->intake()['timelines']);
        $this->assertSame(0, $second->fresh()->position);
        $this->assertSame(1, $first->fresh()->position);
    }

    public function test_the_last_budget_range_for_a_group_cannot_be_deleted(): void
    {
        $only = IntakeOption::query()->create([
            'kind' => IntakeOptionKind::Budget, 'group' => 'web',
            'value' => 'web:EGP 20k–50k', 'label' => 'EGP 20k–50k', 'position' => 0, 'is_active' => true,
        ]);

        $this->actingAs($this->editor())
            ->delete(route('admin.intake.destroy', $only))
            ->assertSessionHasErrors('option');

        $this->assertDatabaseHas('intake_options', ['id' => $only->id]);
    }

    public function test_an_option_can_be_deleted_when_a_replacement_exists(): void
    {
        $first = IntakeOption::query()->create([
            'kind' => IntakeOptionKind::Budget, 'group' => 'web',
            'value' => 'web:a', 'label' => 'A', 'position' => 0, 'is_active' => true,
        ]);
        IntakeOption::query()->create([
            'kind' => IntakeOptionKind::Budget, 'group' => 'web',
            'value' => 'web:b', 'label' => 'B', 'position' => 1, 'is_active' => true,
        ]);

        $this->actingAs($this->editor())
            ->delete(route('admin.intake.destroy', $first))
            ->assertRedirect();

        $this->assertDatabaseMissing('intake_options', ['id' => $first->id]);
    }

    public function test_required_fields_are_enforced(): void
    {
        $this->actingAs($this->editor())
            ->post(route('admin.intake.store'), [])
            ->assertSessionHasErrors(['kind', 'label']);
    }
}
