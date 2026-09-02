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
        return $this->load('projects');
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
        return $this->load('insights');
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
