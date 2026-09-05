<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Content\StaticContent;
use PHPUnit\Framework\TestCase;

/**
 * The content contract.
 *
 * Views render what the repository hands them and never test for a missing
 * key. That is only safe because the repository guarantees a shape — and it
 * is the same guarantee the eventual DatabaseContent will have to honour,
 * where a nullable column is a much easier mistake to make than a missing
 * array key. These tests pin the contract rather than the current data.
 */
final class ContentShapeTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'formiva-content-'.uniqid();
        mkdir($this->dir);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->dir.DIRECTORY_SEPARATOR.'*.php') ?: [] as $file) {
            unlink($file);
        }

        if (is_dir($this->dir)) {
            rmdir($this->dir);
        }
    }

    private function write(string $name, string $php): void
    {
        file_put_contents($this->dir.DIRECTORY_SEPARATOR.$name.'.php', "<?php return {$php};");
    }

    /**
     * The important case: a record carrying nothing but a slug still comes
     * back renderable. This is what stops one absent optional field from
     * taking a whole page down.
     */
    public function test_a_sparse_project_is_filled_in_rather_than_left_broken(): void
    {
        $this->write('projects', "[['slug' => 'bare']]");

        $project = (new StaticContent($this->dir))->project('bare');

        $this->assertNotNull($project);

        foreach (['index', 'name', 'title', 'category', 'year', 'client', 'clientLogo', 'statement', 'description', 'challenge', 'solution', 'outcome', 'alt'] as $key) {
            $this->assertArrayHasKey($key, $project);
            $this->assertIsString($project[$key], "{$key} should always be a string");
        }

        foreach (['services', 'stack', 'gallery'] as $key) {
            $this->assertIsArray($project[$key], "{$key} should always be an array");
        }

        // Nested shapes the templates reach into without checking.
        $this->assertSame(['value', 'label'], array_keys($project['result']));
        $this->assertIsInt($project['plate']['seed']);
        $this->assertIsString($project['plate']['variant']);
        $this->assertIsString($project['plate']['ratio']);
        $this->assertIsInt($project['coverImage']['seed']);
        $this->assertFalse($project['featured']);
    }

    public function test_supplied_project_values_are_preserved(): void
    {
        $this->write('projects', "[['slug' => 'kiln', 'name' => 'Kiln', 'featured' => true, 'result' => ['value' => '+38%', 'label' => 'Revenue'], 'plate' => ['seed' => 1847, 'variant' => 'bone', 'ratio' => '3/2']]]");

        $project = (new StaticContent($this->dir))->project('kiln');

        $this->assertSame('Kiln', $project['name']);
        $this->assertSame('+38%', $project['result']['value']);
        $this->assertSame(1847, $project['plate']['seed']);
        $this->assertSame('bone', $project['plate']['variant']);
        $this->assertTrue($project['featured']);
    }

    public function test_plate_seeds_are_deterministic_for_the_same_record(): void
    {
        $this->write('projects', "[['slug' => 'stable']]");

        $first = (new StaticContent($this->dir))->project('stable');
        $second = (new StaticContent($this->dir))->project('stable');

        $this->assertSame($first['plate']['seed'], $second['plate']['seed'], 'Generated artwork must not change between requests.');
    }

    public function test_a_sparse_insight_is_filled_in(): void
    {
        $this->write('insights', "[['slug' => 'bare']]");

        $insight = (new StaticContent($this->dir))->insight('bare');

        $this->assertNotNull($insight);

        foreach (['index', 'category', 'title', 'dek', 'date_label', 'reading', 'body', 'alt'] as $key) {
            $this->assertIsString($insight[$key], "{$key} should always be a string");
        }

        $this->assertIsInt($insight['plate']['seed']);
    }

    public function test_featured_projects_are_a_subset_of_projects(): void
    {
        $this->write('projects', "[['slug' => 'a', 'featured' => true], ['slug' => 'b']]");

        $content = new StaticContent($this->dir);

        $this->assertCount(2, $content->projects());
        $this->assertCount(1, $content->featuredProjects());
        $this->assertSame('a', $content->featuredProjects()[0]['slug']);
    }

    public function test_neighbours_wrap_and_degrade_safely(): void
    {
        $this->write('projects', "[['slug' => 'a'], ['slug' => 'b']]");
        $content = new StaticContent($this->dir);

        $this->assertSame('b', $content->projectNeighbors('a')['previous']['slug']);
        $this->assertSame('b', $content->projectNeighbors('a')['next']['slug']);

        $this->write('projects', "[['slug' => 'only']]");
        $single = new StaticContent($this->dir);

        // One project has no neighbours; the template hides the nav entirely.
        $this->assertNull($single->projectNeighbors('only')['previous']);
        $this->assertNull($single->projectNeighbors('only')['next']);
    }

    public function test_a_missing_content_file_fails_loudly(): void
    {
        $this->expectException(\RuntimeException::class);

        (new StaticContent($this->dir))->projects();
    }
}
