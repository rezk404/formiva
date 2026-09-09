<?php

declare(strict_types=1);

namespace App\Content;

use App\Models\CaseStudy;
use App\Models\Insight;
use App\Models\IntakeOption;
use App\Models\Project;
use App\Models\Service;
use App\Models\Setting;
use App\Models\TeamMember;
use App\Models\Testimonial;
use Illuminate\Support\Collection;

final class DatabaseContent implements ContentRepository
{
    /** @var array<string, array<string, mixed>>|null */
    private ?array $projectMetadata = null;

    public function __construct(private readonly ContentPresenter $presenter = new ContentPresenter())
    {
    }

    public function site(): array
    {
        return $this->setting('site', []);
    }

    public function services(): array
    {
        return Service::query()->published()->with('items')->ordered()->get()
            ->map(static fn (Service $service): array => [
                'index' => $service->index_label,
                'slug' => $service->slug,
                'title' => $service->title,
                'form' => $service->form,
                'lede' => $service->lede,
                'why' => $service->why,
                'outcome' => $service->outcome,
                'note' => $service->note,
                'items' => $service->items->map(static fn ($item): array => [
                    'title' => $item->title,
                    'form' => $item->form,
                    'summary' => $item->summary,
                ])->values()->all(),
            ])->all();
    }

    public function projects(): array
    {
        return $this->projectQuery()->get()->map(fn (Project $project): array => $this->projectArray($project))->all();
    }

    public function featuredProjects(): array
    {
        return $this->projectQuery()->featured()->get()->map(fn (Project $project): array => $this->projectArray($project))->all();
    }

    public function project(string $slug): ?array
    {
        $project = $this->projectQuery()->where('slug', $slug)->first();

        return $project ? $this->projectArray($project) : null;
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
        $study = CaseStudy::query()->published()->with(['project.category', 'project.client', 'project.services', 'beats', 'metrics'])->first();

        if (! $study) {
            return [];
        }

        return $this->presenter->caseStudy([
            'eyebrow' => $study->eyebrow,
            'name' => $study->project?->name ?? '',
            'title' => $study->title,
            'summary' => $study->summary,
            'client' => $study->client_name,
            'duration' => $study->duration,
            'team' => $study->team_size,
            'role' => $study->role,
            'beats' => $study->beats->map(fn ($beat): array => [
                'index' => $beat->index_label,
                'label' => $beat->label,
                'heading' => $beat->heading,
                'body' => $beat->body,
                'plate' => $this->presenter->plate([
                    'seed' => $beat->plate_seed,
                    'variant' => $beat->plate_variant,
                    'ratio' => $beat->plate_ratio,
                ], 'case-study-'.$beat->id, '3/2'),
                'alt' => $beat->alt,
            ])->values()->all(),
            'metrics' => $study->metrics->map(static fn ($metric): array => [
                'value' => $metric->value,
                'label' => $metric->label,
                'note' => $metric->note,
            ])->values()->all(),
        ], $this->projectArray($study->project));
    }

    public function studio(): array
    {
        return $this->setting('studio', []);
    }

    public function process(): array
    {
        return $this->setting('process', []);
    }

    public function team(): array
    {
        $stored = $this->setting('team', null);

        if (is_array($stored) && $stored !== []) {
            return $stored;
        }

        return TeamMember::query()->published()->with('photo')->ordered()->get()->map(function (TeamMember $member): array {
            return [
                'index' => str_pad((string) $member->position, 2, '0', STR_PAD_LEFT),
                'name' => $member->name,
                'role' => $member->role,
                'bio' => $member->bio,
                'since' => (string) $member->since_year,
                'plate' => $this->presenter->plate(null, $member->slug.'-plate', '3/4'),
                'alt' => $member->photo?->alt ?? 'Portrait plate for '.$member->name.'.',
            ];
        })->all();
    }

    public function testimonials(): array
    {
        return Testimonial::query()->published()->with(['client', 'project'])->ordered()->get()->map(static fn (Testimonial $testimonial): array => [
            'index' => str_pad((string) $testimonial->position, 2, '0', STR_PAD_LEFT),
            'quote' => $testimonial->quote,
            'name' => $testimonial->author_name,
            'role' => $testimonial->author_role,
            'company' => $testimonial->company,
            'project' => $testimonial->project?->slug,
        ])->all();
    }

    public function insights(): array
    {
        return Insight::query()->published()->with(['category', 'author', 'coverMedia'])->ordered()->get()
            ->map(fn (Insight $insight): array => $this->insightArray($insight))->all();
    }

    public function insight(string $slug): ?array
    {
        $insight = Insight::query()->published()->with(['category', 'author', 'coverMedia'])->where('slug', $slug)->first();

        return $insight ? $this->insightArray($insight) : null;
    }

    public function clients(): array
    {
        return $this->setting('clients', []);
    }

    public function intake(): array
    {
        $stored = $this->setting('intake', null);

        if (is_array($stored) && $stored !== []) {
            return $stored;
        }

        return IntakeOption::query()->active()->ordered()->get()->groupBy(fn ($option): string => $option->kind->value)
            ->map(fn (Collection $options): array => $options->map(static fn ($option): array => [
                'value' => $option->value,
                'label' => $option->label,
                'group' => $option->group,
            ])->values()->all())->all();
    }

    private function projectQuery()
    {
        return Project::query()->published()->with(['category', 'client', 'services', 'coverMedia'])->ordered();
    }

    /** @return array<string, mixed> */
    private function projectArray(Project $project): array
    {
        return $this->presenter->project(array_merge([
            'id' => $project->id,
            'index' => $project->index_label,
            'slug' => $project->slug,
            'name' => $project->name,
            'title' => $project->title,
            'category' => $project->category?->name,
            'year' => (string) $project->year,
            'client' => $project->client?->name,
            'clientLogo' => $project->client?->wordmark,
            'statement' => $project->statement,
            'description' => $project->description,
            'challenge' => $project->challenge,
            'solution' => $project->solution,
            'outcome' => $project->outcome,
            'services' => $project->disciplines ?? $project->services->pluck('title')->values()->all(),
            'stack' => $project->stack ?? [],
            'result' => ['value' => $project->result_value, 'label' => $project->result_label],
            'featured' => $project->is_featured,
            'plate' => ['seed' => $project->plate_seed, 'variant' => $project->plate_variant, 'ratio' => $project->plate_ratio],
            'coverImage' => ['seed' => $project->plate_seed, 'variant' => $project->plate_variant, 'ratio' => '16/9'],
            'alt' => $project->alt,
        ], $this->projectMetadata()[$project->slug] ?? []));
    }

    /** @return array<string, mixed> */
    private function insightArray(Insight $insight): array
    {
        return $this->presenter->insight([
            'slug' => $insight->slug,
            'index' => $insight->index_label,
            'category' => $insight->category?->name,
            'title' => $insight->title,
            'dek' => $insight->dek,
            'date' => $insight->published_at?->format('Y-m-d'),
            'date_label' => $insight->published_at?->format('F Y'),
            'reading' => $insight->reading_minutes.' min',
            'body' => $insight->body,
            'plate' => ['seed' => $insight->plate_seed, 'variant' => $insight->plate_variant, 'ratio' => $insight->plate_ratio],
            'alt' => $insight->alt,
        ]);
    }

    private function setting(string $key, mixed $default): mixed
    {
        return Setting::retrieve('content', $key, $default);
    }

    /** @return array<string, array<string, mixed>> */
    private function projectMetadata(): array
    {
        return $this->projectMetadata ??= Setting::retrieve('content', 'project-meta', []);
    }
}
