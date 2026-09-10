<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Content\DatabaseContent;
use App\Enums\SettingType;
use App\Models\Setting;
use App\Models\StudioPosition;
use App\Models\StudioStat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * About — the studio story, its positions and its numbers.
 *
 * Split across two screens because they change on different rhythms, but
 * they are one area: the sub-navigation is asserted here too, since an area
 * that does not show its own shape is the problem this replaced.
 */
final class AboutManagementTest extends TestCase
{
    use RefreshDatabase;

    private function editor(): User
    {
        return User::factory()->create();
    }

    /** @return array<string, mixed> */
    private function overviewPayload(array $overrides = []): array
    {
        return array_merge([
            'eyebrow' => 'The studio',
            'headline' => ['We build digital systems', 'that outlive their launch.'],
            'story' => ['FORMIVA started in 2016 with one rule.', 'Nineteen of us now.'],
            'positions' => [
                ['id' => '', 'index_label' => '01', 'title' => 'We prototype before we present.', 'body' => 'If it cannot be built in a week, it is a wish.'],
                ['id' => '', 'index_label' => '02', 'title' => 'We hand over the keys.', 'body' => 'Documented, instrumented, in use.'],
            ],
        ], $overrides);
    }

    /** @return array<string, mixed> */
    private function statsPayload(array $overrides = []): array
    {
        return array_merge([
            'stats' => [
                ['id' => '', 'value' => '48', 'suffix' => '+', 'label' => 'Products launched', 'note' => 'Since 2016'],
                ['id' => '', 'value' => '19', 'suffix' => '', 'label' => 'People', 'note' => 'Cairo and remote'],
            ],
        ], $overrides);
    }

    public function test_the_overview_shows_the_chapter_and_its_local_navigation(): void
    {
        Setting::put('content', 'studio', [
            'eyebrow' => 'The studio',
            'headline' => ['One', 'Two'],
            'story' => ['A paragraph.'],
        ], SettingType::Json);

        StudioPosition::query()->create(['index_label' => '01', 'title' => 'A position', 'body' => 'Its reason.', 'position' => 0]);

        $this->actingAs($this->editor())
            ->get(route('admin.about.overview'))
            ->assertOk()
            ->assertSee('A position')
            ->assertSee('A paragraph.')
            ->assertSee('Opening')
            ->assertSee('Positions')
            // The five screens of the About area, on every one of them.
            ->assertSee('Overview')
            ->assertSee('Team')
            ->assertSee('Process')
            ->assertSee('Testimonials')
            ->assertSee('Stats');
    }

    public function test_saving_the_overview_writes_framing_and_positions_together(): void
    {
        $this->actingAs($this->editor())
            ->put(route('admin.about.overview.update'), $this->overviewPayload())
            ->assertRedirect(route('admin.about.overview'));

        $this->assertSame(2, StudioPosition::query()->count());

        $studio = (new DatabaseContent())->studio();

        $this->assertSame('The studio', $studio['eyebrow']);
        $this->assertSame(['We build digital systems', 'that outlive their launch.'], $studio['headline']);
        $this->assertCount(2, $studio['story']);
        $this->assertSame(['index', 'title', 'body'], array_keys($studio['positions'][0]));
        $this->assertSame('We prototype before we present.', $studio['positions'][0]['title']);
    }

    public function test_position_rows_keep_their_identity_and_removed_rows_are_deleted(): void
    {
        $this->actingAs($this->editor())->put(route('admin.about.overview.update'), $this->overviewPayload());

        $kept = StudioPosition::query()->orderBy('position')->first();

        $this->actingAs($this->editor())
            ->put(route('admin.about.overview.update'), $this->overviewPayload([
                'positions' => [
                    ['id' => $kept->id, 'index_label' => '01', 'title' => 'Rewritten position', 'body' => 'New reason.'],
                ],
            ]))
            ->assertRedirect();

        $this->assertSame(1, StudioPosition::query()->count());
        $this->assertSame($kept->id, StudioPosition::query()->first()->id);
        $this->assertSame('Rewritten position', StudioPosition::query()->first()->title);
    }

    public function test_the_headline_must_be_exactly_two_lines(): void
    {
        $this->actingAs($this->editor())
            ->put(route('admin.about.overview.update'), $this->overviewPayload(['headline' => ['Only one line']]))
            ->assertSessionHasErrors('headline');
    }

    public function test_the_chapter_cannot_be_emptied_of_positions(): void
    {
        $this->actingAs($this->editor())
            ->put(route('admin.about.overview.update'), $this->overviewPayload(['positions' => []]))
            ->assertSessionHasErrors('positions');
    }

    public function test_statistics_are_their_own_screen_and_persist(): void
    {
        $this->actingAs($this->editor())
            ->get(route('admin.about.stats'))
            ->assertOk()
            ->assertSee('The numbers');

        $this->actingAs($this->editor())
            ->put(route('admin.about.stats.update'), $this->statsPayload())
            ->assertRedirect(route('admin.about.stats'));

        $this->assertSame(2, StudioStat::query()->count());

        $stats = (new DatabaseContent())->studio()['stats'];

        $this->assertSame(['value', 'suffix', 'label', 'note'], array_keys($stats[0]));
        $this->assertSame('48', $stats[0]['value']);
    }

    public function test_a_blank_suffix_is_accepted_because_not_every_statistic_has_one(): void
    {
        $this->actingAs($this->editor())
            ->put(route('admin.about.stats.update'), $this->statsPayload())
            ->assertSessionHasNoErrors();

        $this->assertSame('', StudioStat::query()->orderByDesc('position')->first()->suffix);
    }

    public function test_the_statistics_row_cannot_be_emptied(): void
    {
        $this->actingAs($this->editor())
            ->put(route('admin.about.stats.update'), ['stats' => []])
            ->assertSessionHasErrors('stats');
    }

    public function test_the_about_area_lives_under_one_url_prefix(): void
    {
        // The tables are unchanged; the grouping is in the interface. These
        // assertions are what stop the area quietly fragmenting again.
        $this->assertStringContainsString('/admin/about', route('admin.about.overview', absolute: false));
        $this->assertStringContainsString('/admin/about', route('admin.about.stats', absolute: false));
        $this->assertStringContainsString('/admin/about', route('admin.team.index', absolute: false));
        $this->assertStringContainsString('/admin/about', route('admin.process.index', absolute: false));
        $this->assertStringContainsString('/admin/about', route('admin.testimonials.index', absolute: false));
    }
}
