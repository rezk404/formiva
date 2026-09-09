<?php

declare(strict_types=1);

namespace App\Content;

final class ContentPresenter
{
    /** @return array<string, mixed> */
    public function project(array $project): array
    {
        $slug = (string) ($project['slug'] ?? '');
        $name = (string) ($project['name'] ?? $project['title'] ?? 'Untitled project');
        $plate = $this->plate($project['plate'] ?? null, $slug.'-plate');
        $cover = $this->plate($project['coverImage'] ?? $project['plate'] ?? null, $slug.'-cover', '16/9');
        $gallery = array_values(array_filter((array) ($project['gallery'] ?? []), static fn ($image): bool => is_array($image)));

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
            'result' => [
                'value' => (string) ($project['result']['value'] ?? ''),
                'label' => (string) ($project['result']['label'] ?? ''),
            ],
            'featured' => (bool) ($project['featured'] ?? false),
            'plate' => $plate,
            'coverImage' => $cover,
            'gallery' => array_map(
                fn (array $image, int $index): array => $this->plate($image, $slug.'-gallery-'.$index),
                $gallery,
                array_keys($gallery),
            ),
            'alt' => (string) ($project['alt'] ?? $name.' — generated project plate.'),
        ];
    }

    /** @return array<string, mixed> */
    public function insight(array $insight): array
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
            'plate' => $this->plate($insight['plate'] ?? null, $slug.'-plate', '16/10'),
            'alt' => (string) ($insight['alt'] ?? $title.' — generated cover plate.'),
        ];
    }

    /** @return array<string, mixed> */
    public function caseStudy(array $study, ?array $project = null): array
    {
        $study = ['project' => $project ?? []] + $study;
        $study['beats'] = array_values((array) ($study['beats'] ?? []));
        $study['metrics'] = array_values((array) ($study['metrics'] ?? []));
        $study['eyebrow'] ??= 'Featured case';
        $study['client'] ??= '';
        $study['duration'] ??= '';
        $study['title'] ??= '';
        $study['summary'] ??= '';

        return $study;
    }

    /** @return array{seed: int, variant: string, ratio: string} */
    public function plate(?array $plate, string $key, string $ratio = '4/3'): array
    {
        return [
            'seed' => (int) ($plate['seed'] ?? $this->seedFrom($key)),
            'variant' => (string) ($plate['variant'] ?? 'ink'),
            'ratio' => (string) ($plate['ratio'] ?? $ratio),
        ];
    }

    private function seedFrom(string $key): int
    {
        return 1000 + (int) (crc32($key) % 9000);
    }
}
