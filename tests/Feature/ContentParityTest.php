<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Content\ContentImporter;
use App\Content\ContentRepository;
use App\Content\DatabaseContent;
use App\Content\StaticContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ContentParityTest extends TestCase
{
    use RefreshDatabase;

    public function test_both_content_sources_implement_the_public_contract(): void
    {
        $this->assertInstanceOf(ContentRepository::class, new StaticContent(resource_path('content')));
        $this->assertInstanceOf(ContentRepository::class, new DatabaseContent());
    }

    /**
     * A migrated but unseeded database — a first deploy, a restored backup
     * mid-import — must still render. The singleton values fall back to the
     * files they were seeded from rather than returning an empty array that
     * fatals deep inside a template on a missing key.
     */
    public function test_the_database_source_falls_back_to_the_files_before_it_is_seeded(): void
    {
        $database = new DatabaseContent();
        $static = new StaticContent(resource_path('content'));

        $this->assertSame($static->site(), $database->site());
        $this->assertSame($static->clients(), $database->clients());
        $this->assertSame($static->studio()['eyebrow'], $database->studio()['eyebrow']);
        $this->assertSame($static->process()['lede'], $database->process()['lede']);
        $this->assertSame($static->intake()['rules'], $database->intake()['rules']);

        // Tables the CMS owns stay genuinely empty — the files are defaults
        // for the singletons, not a second copy of the records.
        $this->assertSame([], $database->studio()['positions']);
        $this->assertSame([], $database->services());
    }

    public function test_database_import_preserves_the_public_content_contract(): void
    {
        (new ContentImporter(resource_path('content')))->import();

        $static = new StaticContent(resource_path('content'));
        $database = new DatabaseContent();

        $this->assertSame(array_column($static->projects(), 'slug'), array_column($database->projects(), 'slug'));
        $this->assertSame(array_column($static->featuredProjects(), 'slug'), array_column($database->featuredProjects(), 'slug'));
        $this->assertSame($static->project('kiln'), $database->project('kiln'));
        $this->assertSame(
            array_map(static fn (?array $project): ?string => $project['slug'] ?? null, $static->projectNeighbors('kiln')),
            array_map(static fn (?array $project): ?string => $project['slug'] ?? null, $database->projectNeighbors('kiln')),
        );
        $this->assertSame($static->insight('motion-has-a-budget'), $database->insight('motion-has-a-budget'));
        $this->assertSame($static->caseStudy(), $database->caseStudy());
        $this->assertSame($static->studio(), $database->studio());
        $this->assertEquals($static->process(), $database->process());
        $this->assertSame($static->team(), $database->team());
        $this->assertSame($static->clients(), $database->clients());
        $this->assertSame($static->intake(), $database->intake());
    }
}
