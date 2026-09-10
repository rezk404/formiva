<?php

declare(strict_types=1);

namespace App\Content;

use App\Enums\IntakeOptionKind;
use App\Models\CaseStudy;
use App\Models\Insight;
use App\Models\IntakeOption;
use App\Models\ProcessStage;
use App\Models\Project;
use App\Models\Service;
use App\Models\Setting;
use App\Models\StudioPosition;
use App\Models\StudioStat;
use App\Models\TeamMember;
use App\Models\Testimonial;
use Illuminate\Support\Collection;
use Throwable;

/**
 * The database implementation of the public content contract.
 *
 * Every method here returns exactly the array shape StaticContent returns
 * for the same call — tests/Feature/ContentParityTest holds the two side by
 * side and will not let them drift. That equality is the whole reason the
 * public templates never had to learn where content comes from.
 *
 * Two kinds of value live in `settings`:
 *
 *   - Chapter framing that has no table of its own (the studio eyebrow and
 *     headline, the process lede, the intake qualification rules).
 *   - Records the CMS does not yet own (client wordmarks, per-project
 *     gallery metadata).
 *
 * Everything else resolves from its own table, which is what makes the CMS
 * the source of truth rather than a second copy of it.
 */
final class DatabaseContent implements ContentRepository
{
    /** @var array<string, array<string, mixed>>|null */
    private ?array $projectMetadata = null;

    /** @var array<string, mixed>|null */
    private ?array $settings = null;

    private ?StaticContent $files = null;

    public function __construct(private readonly ContentPresenter $presenter = new ContentPresenter())
    {
    }

    public function site(): array
    {
        return $this->singleton('site', 'site');
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

    /** @return array<string, mixed> */
    public function previewProject(Project $project): array
    {
        return $this->projectArray($project->load(['category', 'client', 'services', 'coverMedia', 'galleryMedia']));
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
        $study = CaseStudy::query()->published()->with(['project.category', 'project.client', 'project.services', 'project.coverMedia', 'project.galleryMedia', 'beats', 'metrics'])->first();

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

    /**
     * Framing from settings, substance from the two studio tables. The
     * CMS edits positions and statistics as records; the eyebrow, headline
     * and story are prose with no repetition and stay in the singleton.
     */
    public function studio(): array
    {
        $framing = $this->singleton('studio', 'studio');

        return [
            'eyebrow' => (string) ($framing['eyebrow'] ?? 'The studio'),
            'headline' => array_values((array) ($framing['headline'] ?? [])),
            'story' => array_values((array) ($framing['story'] ?? [])),
            'positions' => StudioPosition::query()->ordered()->get()
                ->map(static fn (StudioPosition $position): array => [
                    'index' => $position->index_label,
                    'title' => $position->title,
                    'body' => $position->body,
                ])->values()->all(),
            'stats' => StudioStat::query()->ordered()->get()
                ->map(static fn (StudioStat $stat): array => [
                    'value' => $stat->value,
                    'suffix' => $stat->suffix,
                    'label' => $stat->label,
                    'note' => $stat->note,
                ])->values()->all(),
        ];
    }

    public function process(): array
    {
        $framing = $this->singleton('process', 'process');

        return [
            'eyebrow' => (string) ($framing['eyebrow'] ?? 'The system'),
            'headline' => array_values((array) ($framing['headline'] ?? [])),
            'lede' => (string) ($framing['lede'] ?? ''),
            'stages' => ProcessStage::query()->ordered()->get()
                ->map(static function (ProcessStage $stage): array {
                    $shape = [
                        'index' => $stage->index_label,
                        'title' => $stage->title,
                        'window' => $stage->window,
                        'body' => $stage->body,
                        'output' => $stage->output,
                        'span' => [$stage->span_start, $stage->span_end],
                        'weight' => $stage->weight,
                    ];

                    // Absent rather than false when it does not apply — the
                    // static file has always expressed it that way, and the
                    // template asks with a null-coalesce.
                    if ($stage->overlap) {
                        $shape['overlap'] = true;
                    }

                    return $shape;
                })->values()->all(),
        ];
    }

    public function team(): array
    {
        return TeamMember::query()->published()->with('photo')->ordered()->get()->values()
            ->map(function (TeamMember $member, int $ordinal): array {
                return [
                    // Numbered by display order rather than by the stored
                    // position, so deleting the second person does not leave
                    // the studio page counting 01, 03, 04.
                    'index' => str_pad((string) ($ordinal + 1), 2, '0', STR_PAD_LEFT),
                    'name' => $member->name,
                    'role' => $member->role,
                    'bio' => $member->bio,
                    'since' => (string) $member->since_year,
                    'plate' => $this->presenter->plate([
                        'seed' => $member->plate_seed,
                        'variant' => $member->plate_variant,
                        'ratio' => $member->plate_ratio,
                    ], $member->slug.'-plate', '3/4'),
                    'alt' => (string) ($member->alt ?: ($member->photo?->alt ?? 'Portrait plate for '.$member->name.'.')),
                ];
            })->all();
    }

    public function testimonials(): array
    {
        return Testimonial::query()->published()->with(['client', 'project'])->ordered()->get()->values()
            ->map(static fn (Testimonial $testimonial, int $ordinal): array => [
                'index' => str_pad((string) ($ordinal + 1), 2, '0', STR_PAD_LEFT),
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
        return $this->singleton('clients', 'clients');
    }

    /**
     * The intake form and its estimator read one nested array. Options are
     * rows; the qualification rules that map a project group to a fit, a
     * complexity and a timeline hint are prose-like configuration and stay
     * in the singleton beside them.
     */
    public function intake(): array
    {
        $options = IntakeOption::query()->active()->ordered()->get()->groupBy(
            static fn (IntakeOption $option): string => $option->kind->value,
        );

        $labels = static function (Collection $options): array {
            return $options->pluck('label')->values()->all();
        };

        return [
            'projectTypes' => $this->kind($options, IntakeOptionKind::ProjectType)
                ->map(static fn (IntakeOption $option): array => [
                    'value' => $option->value,
                    'label' => $option->label,
                    'group' => (string) $option->group,
                ])->values()->all(),
            'budgets' => $this->kind($options, IntakeOptionKind::Budget)
                ->groupBy(static fn (IntakeOption $option): string => (string) $option->group)
                ->map($labels)
                ->all(),
            'timelines' => $labels($this->kind($options, IntakeOptionKind::Timeline)),
            'companySizes' => $labels($this->kind($options, IntakeOptionKind::CompanySize)),
            'services' => $labels($this->kind($options, IntakeOptionKind::Service)),
            'rules' => (array) ($this->singleton('intake', 'intake')['rules'] ?? []),
        ];
    }

    /**
     * @param  Collection<string, Collection<int, IntakeOption>>  $grouped
     * @return Collection<int, IntakeOption>
     */
    private function kind(Collection $grouped, IntakeOptionKind $kind): Collection
    {
        /** @var Collection<int, IntakeOption> $options */
        $options = $grouped->get($kind->value, new Collection());

        return $options;
    }

    private function projectQuery()
    {
        return Project::query()->published()->with(['category', 'client', 'services', 'coverMedia', 'galleryMedia'])->ordered();
    }

    /** @return array<string, mixed> */
    private function projectArray(Project $project): array
    {
        $gallery = $project->galleryMedia->map(static fn ($media): array => [
            'url' => $media->url(),
            'alt' => $media->alt,
        ])->filter(static fn (array $image): bool => $image['url'] !== null)->values()->all();

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
            'gallery' => $gallery,
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

    /**
     * Every value in the `content` settings group, in one query.
     *
     * A page render asks for the site, the studio framing, the process
     * framing, the intake rules, the client wordmarks and the project
     * metadata — six round trips for six rows in the same group. They are
     * read once and memoised for the life of the request instead.
     *
     * @return array<string, mixed>
     */
    private function settings(): array
    {
        return $this->settings ??= Setting::query()
            ->where('group', 'content')
            ->get()
            ->mapWithKeys(static fn (Setting $setting): array => [$setting->key => $setting->typedValue()])
            ->all();
    }

    private function setting(string $key, mixed $default): mixed
    {
        return $this->settings()[$key] ?? $default;
    }

    /**
     * A settings singleton, falling back to the file it was seeded from.
     *
     * The rule is uniform and applies to every value in this group: the
     * files are the defaults, the database overrides them. It is what lets a
     * database that has been migrated but not yet seeded — a first deploy,
     * a restored backup mid-import — render the site instead of fataling on
     * a missing array key deep inside a template.
     *
     * @return array<string, mixed>
     */
    private function singleton(string $key, string $file): array
    {
        $stored = $this->setting($key, null);

        if (is_array($stored) && $stored !== []) {
            return $stored;
        }

        try {
            return match ($file) {
                'site' => $this->files()->site(),
                'clients' => $this->files()->clients(),
                'studio' => $this->files()->studio(),
                'process' => $this->files()->process(),
                'intake' => ['rules' => $this->files()->intake()['rules'] ?? []],
                default => [],
            };
        } catch (Throwable) {
            // The files are optional at runtime; a deployment that ships
            // without them simply has no defaults to fall back to.
            return [];
        }
    }

    private function files(): StaticContent
    {
        return $this->files ??= new StaticContent(resource_path('content'));
    }

    /** @return array<string, array<string, mixed>> */
    private function projectMetadata(): array
    {
        return $this->projectMetadata ??= (array) $this->setting('project-meta', []);
    }
}
