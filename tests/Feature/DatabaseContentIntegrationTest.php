<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Content\ContentImporter;
use App\Content\ContentRepository;
use App\Content\DatabaseContent;
use App\Enums\CategoryType;
use App\Models\Category;
use App\Models\Insight;
use App\Models\Service;
use App\Models\TeamMember;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * The full path the phase is really about:
 *
 *   admin CMS → database → DatabaseContent → ContentRepository → public views
 *
 * Nothing here touches a template. The public site is asked to render with
 * the database bound as its content source, and then asked whether an edit
 * made through the workspace actually reached the page.
 */
final class DatabaseContentIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        (new ContentImporter(resource_path('content')))->import();

        // The one line that switches the site over. In an environment it is
        // FORMIVA_CONTENT=database; here it is a rebind, so the default
        // configuration the rest of the suite relies on is left alone.
        $this->app->singleton(ContentRepository::class, fn (): ContentRepository => new DatabaseContent());
    }

    /** @return array<string, array{string}> */
    public static function publicRoutes(): array
    {
        return [
            'home' => ['/'],
            'work index' => ['/work'],
            'services' => ['/services'],
            'studio' => ['/studio'],
            'insights index' => ['/insights'],
            'start a project' => ['/start-a-project'],
        ];
    }

    #[DataProvider('publicRoutes')]
    public function test_every_public_page_renders_from_the_database(string $path): void
    {
        $this->get($path)->assertOk();
    }

    public function test_project_and_insight_detail_pages_render_from_the_database(): void
    {
        $content = app(ContentRepository::class);

        foreach ($content->projects() as $project) {
            $this->get('/work/'.$project['slug'])->assertOk();
        }

        foreach ($content->insights() as $insight) {
            $this->get('/insights/'.$insight['slug'])->assertOk();
        }
    }

    public function test_an_insight_published_through_the_cms_appears_on_the_public_journal(): void
    {
        $editor = User::factory()->create();
        $category = Category::query()->where('type', CategoryType::Insight)->firstOrFail();

        $this->actingAs($editor)->post(route('admin.insights.store'), [
            'title' => 'A note written in the workspace',
            'slug' => 'written-in-the-workspace',
            'index_label' => '99',
            'dek' => 'It should reach the journal without a template change.',
            'body' => 'The body renders as plain prose, exactly as typed.',
            'reading_minutes' => '4',
            'category_id' => $category->id,
            'author_id' => $editor->id,
            'plate_seed' => '4242',
            'plate_variant' => 'ink',
            'plate_ratio' => '16/10',
            'alt' => 'Stacked bands in warm neutral tones.',
            'intent' => 'draft',
        ])->assertRedirect();

        // A draft is not on the site.
        $this->get('/insights/written-in-the-workspace')->assertNotFound();
        $this->get('/insights')->assertDontSee('A note written in the workspace');

        $insight = Insight::query()->where('slug', 'written-in-the-workspace')->firstOrFail();

        $this->actingAs($editor)
            ->post(route('admin.insights.publish', $insight), ['action' => 'publish'])
            ->assertRedirect();

        $this->get('/insights/written-in-the-workspace')
            ->assertOk()
            ->assertSee('A note written in the workspace')
            ->assertSee('The body renders as plain prose, exactly as typed.');

        $this->get('/insights')->assertSee('A note written in the workspace');
    }

    public function test_a_scheduled_insight_is_not_reachable_before_its_moment(): void
    {
        $insight = Insight::factory()->scheduled()->create(['slug' => 'not-yet']);

        $this->get('/insights/not-yet')->assertNotFound();
        $this->assertNull(app(ContentRepository::class)->insight($insight->slug));
    }

    public function test_a_service_created_in_the_cms_appears_and_disappears_with_its_state(): void
    {
        $editor = User::factory()->create();

        $this->actingAs($editor)->post(route('admin.services.store'), [
            'title' => 'Systems Archaeology',
            'slug' => 'systems-archaeology',
            'index_label' => '04',
            'form' => 'layered',
            'lede' => 'Reading what the last team left behind.',
            'why' => 'Nobody documents the workaround that became the process.',
            'outcome' => 'A map of the system as it actually runs.',
            'note' => 'Four to eight weeks.',
            'intent' => 'publish',
            'items' => [
                ['id' => '', 'title' => 'Discovery audit', 'form' => 'layered', 'summary' => 'Every integration, written down once.'],
            ],
        ])->assertRedirect();

        $service = Service::query()->where('slug', 'systems-archaeology')->firstOrFail();

        $this->get('/services')
            ->assertOk()
            ->assertSee('Systems Archaeology')
            ->assertSee('Reading what the last team left behind.');

        $this->actingAs($editor)
            ->post(route('admin.services.publish', $service), ['action' => 'unpublish'])
            ->assertRedirect();

        $this->get('/services')
            ->assertOk()
            ->assertDontSee('Systems Archaeology')
            ->assertDontSee('Reading what the last team left behind.');
    }

    public function test_hiding_a_team_member_removes_them_from_the_studio_page(): void
    {
        $member = TeamMember::query()->published()->ordered()->firstOrFail();

        $this->get('/studio')->assertSee($member->name);

        $this->actingAs(User::factory()->create())
            ->post(route('admin.team.toggle', $member))
            ->assertRedirect();

        $this->get('/studio')->assertDontSee($member->name);
    }

    /**
     * The CMS can empty a chapter the files never could. The homepage used to
     * read $testimonials[0] and $caseStudy['title'] unguarded, so unpublishing
     * the last quote — an ordinary editorial act — returned a 500.
     */
    public function test_the_homepage_survives_an_emptied_proof_chapter(): void
    {
        Testimonial::query()->update(['is_published' => false]);

        $this->get('/')->assertOk()->assertDontSee('Proof');
    }

    public function test_the_homepage_survives_an_unpublished_case_study(): void
    {
        \App\Models\CaseStudy::query()->delete();

        $this->get('/')->assertOk();
    }

    public function test_the_studio_page_survives_an_emptied_team(): void
    {
        TeamMember::query()->update(['is_published' => false]);

        $this->get('/studio')->assertOk();
    }

    public function test_the_public_team_shape_is_numbered_by_display_order(): void
    {
        $team = app(ContentRepository::class)->team();

        $this->assertNotEmpty($team);
        $this->assertSame('01', $team[0]['index']);
        $this->assertSame(['index', 'name', 'role', 'bio', 'since', 'plate', 'alt'], array_keys($team[0]));
    }

    public function test_hiding_a_testimonial_removes_it_from_the_homepage_proof(): void
    {
        $testimonial = Testimonial::query()->published()->ordered()->firstOrFail();

        $this->get('/')->assertSee($testimonial->author_name);

        $this->actingAs(User::factory()->create())
            ->post(route('admin.testimonials.toggle', $testimonial))
            ->assertRedirect();

        $this->assertNotSame(
            $testimonial->author_name,
            app(ContentRepository::class)->testimonials()[0]['name'] ?? null,
        );
    }

    public function test_the_intake_form_reads_its_options_from_the_database(): void
    {
        $intake = app(ContentRepository::class)->intake();

        $this->get('/start-a-project')
            ->assertOk()
            ->assertSee($intake['projectTypes'][0]['label'])
            ->assertSee($intake['timelines'][0]);
    }

    public function test_editing_site_settings_changes_what_the_layout_renders(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->put(route('admin.settings.update'), [
                'brand_name' => 'FORMIVA',
                'brand_etymology' => 'FORM + VIVA',
                'brand_tagline' => 'Digital products. Business systems. One studio.',
                'brand_discipline' => 'Digital product studio',
                'brand_founded' => '2016',
                'meta_title' => 'A brand new default title',
                'meta_description' => 'A brand new default description.',
                'meta_keywords' => 'studio',
                'meta_locale' => 'en_GB',
                'meta_theme_color' => '#0D0D0C',
                'contact_email' => 'hello@formiva.studio',
                'contact_new_business' => 'new@formiva.studio',
                'contact_phone' => '+20 2 2461 0114',
                'contact_street' => 'Smart Village, Building B2',
                'contact_city' => 'Giza',
                'contact_postcode' => '12577',
                'contact_country' => 'Egypt',
                'contact_timezone' => 'EET',
                'social' => [['label' => 'Instagram', 'handle' => '@formiva', 'url' => 'https://instagram.com']],
                'legal_entity' => 'Formiva Studio',
                'legal_links' => [['label' => 'Privacy', 'url' => '#']],
            ])
            ->assertRedirect();

        $this->get('/')->assertOk()->assertSee('A brand new default title');
    }
}
