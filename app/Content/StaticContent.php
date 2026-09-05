<?php

declare(strict_types=1);

namespace App\Content;

use RuntimeException;

/**
 * File-backed implementation of the content contract.
 *
 * Reads plain PHP arrays from resources/content. Each file is required once
 * per request and memoised — repeated calls inside a single render are free,
 * which matters because the home page asks for projects three times (index,
 * case study lookup, and the footer).
 */
final class StaticContent implements ContentRepository
{
    /** @var array<string, array> */
    private array $memo = [];

    public function __construct(private readonly string $path)
    {
    }

    public function site(): array
    {
        return $this->load('site');
    }

    public function services(): array
    {
        return $this->load('services');
    }

    public function projects(): array
    {
        return array_map($this->normalizeProject(...), $this->load('projects'));
    }

    public function project(string $slug): ?array
    {
        foreach ($this->projects() as $project) {
            if (($project['slug'] ?? null) === $slug) {
                return $project;
            }
        }

        return null;
    }

    public function featuredProjects(): array
    {
        return array_values(array_filter($this->projects(), static fn (array $project): bool => (bool) ($project['featured'] ?? false)));
    }

    public function projectNeighbors(string $slug): array
    {
        $projects = $this->projects();
        $index = array_search($slug, array_column($projects, 'slug'), true);

        if ($index === false || count($projects) < 2) {
            return ['previous' => null, 'next' => null];
        }

        $last = count($projects) - 1;

        return [
            'previous' => $projects[$index === 0 ? $last : $index - 1],
            'next' => $projects[$index === $last ? 0 : $index + 1],
        ];
    }

    public function caseStudy(): array
    {
        $study = $this->load('case-study');

        // Fold in the project record so the chapter can show category, year
        // and stack without the narrative file duplicating them.
        $study['project'] = $this->project($study['project'] ?? '') ?? [];

        // The chapter slices these without checking; guarantee they are lists.
        $study['beats'] = array_values((array) ($study['beats'] ?? []));
        $study['metrics'] = array_values((array) ($study['metrics'] ?? []));
        $study['eyebrow'] ??= 'Featured case';
        $study['client'] ??= '';
        $study['duration'] ??= '';
        $study['title'] ??= '';
        $study['summary'] ??= '';

        return $study;
    }

    public function studio(): array
    {
        return $this->load('studio');
    }

    public function process(): array
    {
        return $this->load('process');
    }

    public function team(): array
    {
        return $this->load('team');
    }

    public function testimonials(): array
    {
        return $this->load('testimonials');
    }

    public function insights(): array
    {
        return array_map($this->normalizeInsight(...), $this->load('insights'));
    }

    public function insight(string $slug): ?array
    {
        foreach ($this->insights() as $insight) {
            if (($insight['slug'] ?? null) === $slug) {
                return $insight;
            }
        }

        return null;
    }

    public function clients(): array
    {
        return $this->load('clients');
    }

    public function intake(): array
    {
        return $this->load('intake');
    }

    /* ── Shape guarantees ────────────────────────────────────────────────────
       Views render whatever the repository hands them and never test for a
       missing key, which is only safe if the shape is guaranteed here. That
       matters more for the CMS than it does for these files: a database row
       with one null column must not be able to take a page down, and the
       fix belongs behind the contract rather than in a dozen templates. */

    /** A deterministic plate seed for a record that has no artwork of its own. */
    private function seedFrom(string $key): int
    {
        return 1000 + (int) (crc32($key) % 9000);
    }

    /**
     * @param  array<string, mixed>|null  $plate
     * @return array{seed: int, variant: string, ratio: string}
     */
    private function normalizePlate(?array $plate, string $key, string $ratio = '4/3'): array
    {
        return [
            'seed' => (int) ($plate['seed'] ?? $this->seedFrom($key)),
            'variant' => (string) ($plate['variant'] ?? 'ink'),
            'ratio' => (string) ($plate['ratio'] ?? $ratio),
        ];
    }

    /**
     * @param  array<string, mixed>  $project
     * @return array<string, mixed>
     */
    private function normalizeProject(array $project): array
    {
        $slug = (string) ($project['slug'] ?? '');
        $name = (string) ($project['name'] ?? $project['title'] ?? 'Untitled project');

        $plate = $this->normalizePlate($project['plate'] ?? null, $slug.'-plate');
        $cover = $this->normalizePlate($project['coverImage'] ?? $project['plate'] ?? null, $slug.'-cover', '16/9');

        $gallery = array_values(array_filter(
            (array) ($project['gallery'] ?? []),
            static fn ($image): bool => is_array($image)
        ));

        return [
            'id' => $project['id'] ?? $slug,
            'slug' => $slug,
            'index' => (string) ($project['index'] ?? '—'),
            'name' => $name,
            'title' => (string) ($project['title'] ?? $name),
            'category' => (string) ($project['category'] ?? 'Project'),
            'year' => (string) ($project['year'] ?? ''),
            'client' => (string) ($project['client'] ?? ''),
            'clientLogo' => (string) ($project['clientLogo'] ?? strtoupper($name)),
            'statement' => (string) ($project['statement'] ?? $project['description'] ?? ''),
            'description' => (string) ($project['description'] ?? $project['statement'] ?? ''),
            'challenge' => (string) ($project['challenge'] ?? ''),
            'solution' => (string) ($project['solution'] ?? ''),
            'outcome' => (string) ($project['outcome'] ?? ''),
            'services' => array_values((array) ($project['services'] ?? [])),
            'stack' => array_values((array) ($project['stack'] ?? [])),
            // An empty result is rendered as nothing rather than as "0" — see
            // the `@if` guards in the project templates.
            'result' => [
                'value' => (string) ($project['result']['value'] ?? ''),
                'label' => (string) ($project['result']['label'] ?? ''),
            ],
            'featured' => (bool) ($project['featured'] ?? false),
            'plate' => $plate,
            'coverImage' => $cover,
            'gallery' => array_map(
                fn (array $image, int $i): array => $this->normalizePlate($image, $slug.'-gallery-'.$i),
                $gallery,
                array_keys($gallery)
            ),
            'alt' => (string) ($project['alt'] ?? $name.' — generated project plate.'),
        ];
    }

    /**
     * @param  array<string, mixed>  $insight
     * @return array<string, mixed>
     */
    private function normalizeInsight(array $insight): array
    {
        $slug = (string) ($insight['slug'] ?? '');
        $title = (string) ($insight['title'] ?? 'Untitled note');

        return [
            'slug' => $slug,
            'index' => (string) ($insight['index'] ?? '—'),
            'category' => (string) ($insight['category'] ?? 'Notes'),
            'title' => $title,
            'dek' => (string) ($insight['dek'] ?? ''),
            'date' => (string) ($insight['date'] ?? ''),
            'date_label' => (string) ($insight['date_label'] ?? ''),
            'reading' => (string) ($insight['reading'] ?? ''),
            'body' => (string) ($insight['body'] ?? ''),
            'plate' => $this->normalizePlate($insight['plate'] ?? null, $slug.'-plate', '16/10'),
            'alt' => (string) ($insight['alt'] ?? $title.' — generated cover plate.'),
        ];
    }

    /**
     * @throws RuntimeException when a content file is missing — failing loudly
     *                          in development beats rendering an empty chapter.
     */
    private function load(string $name): array
    {
        if (isset($this->memo[$name])) {
            return $this->memo[$name];
        }

        $file = $this->path.DIRECTORY_SEPARATOR.$name.'.php';

        if (! is_file($file)) {
            throw new RuntimeException("Missing content file: {$name}.php");
        }

        $data = require $file;

        if (! is_array($data)) {
            throw new RuntimeException("Content file {$name}.php must return an array.");
        }

        return $this->memo[$name] = $data;
    }
}
