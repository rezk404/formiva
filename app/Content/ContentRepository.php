<?php

declare(strict_types=1);

namespace App\Content;

/**
 * The single seam between content and presentation.
 *
 * Nothing in resources/views reaches for a file, a config key or a model.
 * Views receive arrays from this contract and render them. That is the whole
 * arrangement, and it is what lets the CMS arrive later without the frontend
 * being touched:
 *
 *   now    → StaticContent      (resources/content/*.php)
 *   later  → DatabaseContent    (Eloquent, same array shapes)
 *
 * Swapping the binding in AppServiceProvider is the entire migration.
 */
interface ContentRepository
{
    /** Brand, navigation, metadata, contact, social, legal. */
    public function site(): array;

    /** The capability index. */
    public function services(): array;

    /** Selected work, in display order. */
    public function projects(): array;

    /** Homepage subset; later maps directly to a featured database scope. */
    public function featuredProjects(): array;

    /** A single project by slug, or null. */
    public function project(string $slug): ?array;

    /** Adjacent records in display order, wrapping at either end. */
    public function projectNeighbors(string $slug): array;

    /** The featured case study narrative. */
    public function caseStudy(): array;

    /** Studio story, positions and statistics. */
    public function studio(): array;

    /** Process stages. */
    public function process(): array;

    /** People. */
    public function team(): array;

    /** Client quotes. */
    public function testimonials(): array;

    /** Journal entries, newest first. */
    public function insights(): array;

    /** One journal entry by slug, or null. */
    public function insight(string $slug): ?array;

    /** Client wordmarks. */
    public function clients(): array;

    /** Project intake options and qualification rules. */
    public function intake(): array;
}
