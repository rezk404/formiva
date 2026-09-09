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
