<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Content\ContentRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Smoke coverage for the whole public surface.
 *
 * These are deliberately shallow and fast: their job is to catch a page
 * that has stopped rendering at all — a renamed content key, a missing
 * route, a Blade typo — which is the failure this project is most prone to
 * while the content layer is still moving.
 */
final class PublicPagesTest extends TestCase
{
    /** @return array<string, array{string}> */
    public static function staticRoutes(): array
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

    #[DataProvider('staticRoutes')]
    public function test_static_pages_render(string $path): void
    {
        $this->get($path)->assertOk();
    }

    public function test_every_project_detail_page_renders(): void
    {
        $projects = app(ContentRepository::class)->projects();

        $this->assertNotEmpty($projects, 'There should be at least one project to render.');

        foreach ($projects as $project) {
            $this->get('/work/'.$project['slug'])
                ->assertOk()
                ->assertSee($project['name'], escape: false);
        }
    }

    public function test_every_insight_page_renders(): void
    {
        $insights = app(ContentRepository::class)->insights();

        $this->assertNotEmpty($insights, 'There should be at least one insight to render.');

        foreach ($insights as $insight) {
            $this->get('/insights/'.$insight['slug'])->assertOk();
        }
    }

    public function test_unknown_slugs_are_not_found(): void
    {
        $this->get('/work/no-such-project')->assertNotFound();
        $this->get('/insights/no-such-note')->assertNotFound();
        $this->get('/no-such-page')->assertNotFound();
    }

    /** The 404 should still be the site, with a real way back out of it. */
    public function test_not_found_page_is_branded_and_offers_a_route_out(): void
    {
        $response = $this->get('/work/no-such-project');

        $response->assertNotFound();
        $response->assertSee('FORMIVA', escape: false);
        $response->assertSee(route('contact'), escape: false);
    }

    /**
     * The 3D chunk is loaded on demand and only where a chapter clears a
     * ground for it. If this attribute disappears from the homepage the
     * frame silently stops loading; if it appears anywhere else, that page
     * starts paying for a canvas nobody can see.
     */
    public function test_only_the_homepage_asks_for_the_frame(): void
    {
        $this->get('/')->assertSee('data-world-visible', escape: false);

        foreach (['/work', '/services', '/studio', '/insights', '/start-a-project'] as $path) {
            $this->get($path)->assertDontSee('data-world-visible', escape: false);
        }
    }

    public function test_primary_navigation_points_at_real_routes(): void
    {
        $response = $this->get('/');

        foreach ([route('work.index'), route('services.index'), route('studio'), route('insights.index'), route('contact')] as $target) {
            $response->assertSee($target, escape: false);
        }
    }
}
