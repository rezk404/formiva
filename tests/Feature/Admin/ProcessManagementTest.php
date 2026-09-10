<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Content\DatabaseContent;
use App\Enums\SettingType;
use App\Models\ProcessStage;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ProcessManagementTest extends TestCase
{
    use RefreshDatabase;

    private function editor(): User
    {
        return User::factory()->create();
    }

    private function stage(array $overrides = []): ProcessStage
    {
        return ProcessStage::query()->create(array_merge([
            'index_label' => '01',
            'title' => 'Discover',
            'window' => 'Weeks 1 — 2',
            'body' => 'Two weeks of asking the questions nobody has had time for.',
            'output' => 'Findings, not a report',
            'span_start' => 0,
            'span_end' => 12,
            'weight' => 0.55,
            'overlap' => false,
            'position' => 0,
        ], $overrides));
    }

    /** @return array<string, mixed> */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'index_label' => '03',
            'title' => 'Design',
            'window' => 'Weeks 4 — 11',
            'body' => 'Art direction and system develop in parallel.',
            'output' => 'Direction, system, tokens',
            'span_start' => '22',
            'span_end' => '55',
            'weight' => '1.00',
            'overlap' => '1',
        ], $overrides);
    }

    public function test_the_index_reads_as_a_sequence(): void
    {
        $this->stage(['title' => 'Discover', 'position' => 0]);
        $this->stage(['index_label' => '02', 'title' => 'Strategise', 'position' => 1, 'span_start' => 12, 'span_end' => 22]);

        $this->actingAs($this->editor())
            ->get(route('admin.process.index'))
            ->assertOk()
            ->assertSee('Discover')
            ->assertSee('Strategise')
            ->assertSee('Findings, not a report')
            ->assertSee('Weeks 1 — 2');
    }

    public function test_a_stage_can_be_added_and_reaches_the_public_shape(): void
    {
        $this->actingAs($this->editor())
            ->post(route('admin.process.store'), $this->payload())
            ->assertRedirect();

        $stages = (new DatabaseContent())->process()['stages'];

        $this->assertCount(1, $stages);
        $this->assertSame('Design', $stages[0]['title']);
        $this->assertSame([22, 55], $stages[0]['span']);
        $this->assertSame(1.0, $stages[0]['weight']);
        $this->assertTrue($stages[0]['overlap']);
    }

    public function test_a_non_overlapping_stage_omits_the_key_entirely(): void
    {
        $this->actingAs($this->editor())
            ->post(route('admin.process.store'), $this->payload(['overlap' => '0']))
            ->assertRedirect();

        $stages = (new DatabaseContent())->process()['stages'];

        $this->assertArrayNotHasKey('overlap', $stages[0]);
    }

    public function test_a_stage_cannot_finish_before_it_starts(): void
    {
        $this->actingAs($this->editor())
            ->post(route('admin.process.store'), $this->payload(['span_start' => '60', 'span_end' => '20']))
            ->assertSessionHasErrors('span_end');
    }

    public function test_required_fields_are_enforced(): void
    {
        $this->actingAs($this->editor())
            ->post(route('admin.process.store'), [])
            ->assertSessionHasErrors(['index_label', 'title', 'window', 'body', 'output', 'span_start', 'span_end', 'weight']);
    }

    public function test_stages_reorder_and_the_public_sequence_follows(): void
    {
        $first = $this->stage(['title' => 'First', 'position' => 0]);
        $second = $this->stage(['title' => 'Second', 'position' => 1, 'index_label' => '02']);

        $this->actingAs($this->editor())
            ->post(route('admin.process.move', $second), ['direction' => 'up'])
            ->assertRedirect();

        $titles = array_column((new DatabaseContent())->process()['stages'], 'title');

        $this->assertSame(['Second', 'First'], $titles);
        $this->assertSame(0, $second->fresh()->position);
        $this->assertSame(1, $first->fresh()->position);
    }

    public function test_the_last_stage_cannot_be_deleted(): void
    {
        $stage = $this->stage();

        $this->actingAs($this->editor())
            ->delete(route('admin.process.destroy', $stage))
            ->assertSessionHasErrors('stage');

        $this->assertDatabaseHas('process_stages', ['id' => $stage->id]);
    }

    public function test_a_stage_can_be_deleted_when_others_remain(): void
    {
        $first = $this->stage(['position' => 0]);
        $second = $this->stage(['position' => 1, 'index_label' => '02']);

        $this->actingAs($this->editor())
            ->delete(route('admin.process.destroy', $first))
            ->assertRedirect();

        $this->assertDatabaseMissing('process_stages', ['id' => $first->id]);
        $this->assertSame(0, $second->fresh()->position);
    }

    public function test_the_chapter_framing_is_saved_to_settings(): void
    {
        $this->actingAs($this->editor())
            ->put(route('admin.process.framing'), [
                'eyebrow' => 'The system',
                'headline' => ['Six stages.', 'One continuous line.'],
                'lede' => 'Engineering starts in week three, not after sign-off.',
            ])
            ->assertRedirect();

        $stored = Setting::retrieve('content', 'process', []);

        $this->assertSame('The system', $stored['eyebrow']);
        $this->assertSame(['Six stages.', 'One continuous line.'], $stored['headline']);
    }

    public function test_the_framing_headline_must_be_two_lines(): void
    {
        Setting::put('content', 'process', ['eyebrow' => 'x', 'headline' => ['a', 'b'], 'lede' => 'y'], SettingType::Json);

        $this->actingAs($this->editor())
            ->put(route('admin.process.framing'), [
                'eyebrow' => 'The system',
                'headline' => ['Only one'],
                'lede' => 'A lede.',
            ])
            ->assertSessionHasErrors('headline');
    }
}
