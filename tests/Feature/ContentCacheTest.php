<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Content\CachedContent;
use App\Content\ContentRepository;
use App\Content\DatabaseContent;
use App\Models\Service;
use App\Models\User;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The cache in front of the public content.
 *
 * The bug this pins: flush() used to increment a counter that did not exist
 * yet, and increment() on a missing key lands on 1 — which was also the
 * default the cache keys already used. The very first save after a deploy
 * therefore invalidated nothing, and the edit stayed invisible for the rest
 * of the TTL. Exactly the "I saved it and the site did not change" report.
 */
final class ContentCacheTest extends TestCase
{
    use RefreshDatabase;

    private function cached(): CachedContent
    {
        return new CachedContent(new DatabaseContent(), app(CacheRepository::class), 3600);
    }

    public function test_the_very_first_flush_invalidates_the_cache(): void
    {
        Service::factory()->create(['title' => 'Original title']);

        $content = $this->cached();

        $this->assertSame('Original title', $content->services()[0]['title']);

        Service::query()->first()->update(['title' => 'Renamed in the workspace']);

        // Still the cached answer — this is the cache doing its job.
        $this->assertSame('Original title', $content->services()[0]['title']);

        $content->flush();

        $this->assertSame('Renamed in the workspace', $content->services()[0]['title']);
    }

    public function test_flushing_repeatedly_keeps_working(): void
    {
        Service::factory()->create(['title' => 'One']);
        $content = $this->cached();

        foreach (['Two', 'Three', 'Four'] as $title) {
            $content->services();
            Service::query()->first()->update(['title' => $title]);
            $content->flush();

            $this->assertSame($title, $content->services()[0]['title']);
        }
    }

    public function test_an_admin_save_invalidates_the_public_cache(): void
    {
        config(['formiva.content_cache.enabled' => true]);

        $service = Service::factory()->create(['title' => 'Before the edit']);

        $this->app->singleton(
            ContentRepository::class,
            fn (): ContentRepository => new CachedContent(new DatabaseContent(), app(CacheRepository::class), 3600),
        );

        // Warm it.
        $this->assertSame('Before the edit', app(ContentRepository::class)->services()[0]['title']);

        $this->actingAs(User::factory()->create())
            ->post(route('admin.services.publish', $service), ['action' => 'unpublish'])
            ->assertRedirect();

        $this->assertSame([], app(ContentRepository::class)->services());
    }
}
