<?php

declare(strict_types=1);

namespace App\Content;

use App\Enums\CaseStudyStatus;
use App\Enums\CategoryType;
use App\Enums\ClientStatus;
use App\Enums\ContentStatus;
use App\Enums\IntakeOptionKind;
use App\Enums\ProjectStage;
use App\Enums\ProjectStatus;
use App\Enums\SettingType;
use App\Models\CaseStudy;
use App\Models\CaseStudyBeat;
use App\Models\CaseStudyMetric;
use App\Models\Category;
use App\Models\Client;
use App\Models\Insight;
use App\Models\IntakeOption;
use App\Models\ProcessStage;
use App\Models\Project;
use App\Models\Service;
use App\Models\ServiceItem;
use App\Models\Setting;
use App\Models\StudioPosition;
use App\Models\StudioStat;
use App\Models\TeamMember;
use App\Models\Testimonial;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Lifts resources/content into the database.
 *
 * Idempotent by design — every write is an updateOrCreate keyed on the
 * record's natural identity, so running it twice is the same as running it
 * once, and running it over a live database refreshes the imported rows
 * without touching anything the CMS has since added.
 *
 * Settings hold only what has no table: the studio and process framing
 * prose, the intake qualification rules, client wordmarks and the
 * per-project gallery metadata. Anything with a table is imported into it,
 * because the table is what the CMS edits and DatabaseContent reads.
 */
final class ContentImporter
{
    public function __construct(private readonly string $path)
    {
    }

    public function import(): void
    {
        DB::transaction(function (): void {
            $site = $this->load('site');
            $services = $this->load('services');
            $projects = $this->load('projects');
            $caseStudy = $this->load('case-study');
            $insights = $this->load('insights');
            $testimonials = $this->load('testimonials');
            $team = $this->load('team');
            $studio = $this->load('studio');
            $process = $this->load('process');
            $clients = $this->load('clients');
            $intake = $this->load('intake');

            Setting::put('content', 'site', $site, SettingType::Json);
            Setting::put('content', 'clients', $clients, SettingType::Json);

            // Framing only. Positions, stats, stages and options each have a
            // table, and duplicating them here would give the CMS two places
            // to disagree with itself.
            Setting::put('content', 'studio', [
                'eyebrow' => $studio['eyebrow'] ?? 'The studio',
                'headline' => array_values((array) ($studio['headline'] ?? [])),
                'story' => array_values((array) ($studio['story'] ?? [])),
            ], SettingType::Json);

            Setting::put('content', 'process', [
                'eyebrow' => $process['eyebrow'] ?? 'The system',
                'headline' => array_values((array) ($process['headline'] ?? [])),
                'lede' => $process['lede'] ?? '',
            ], SettingType::Json);

            Setting::put('content', 'intake', [
                'rules' => (array) ($intake['rules'] ?? []),
            ], SettingType::Json);

            Setting::put('content', 'project-meta', collect($projects)->mapWithKeys(static fn (array $project): array => [
                $project['slug'] => [
                    'clientLogo' => $project['clientLogo'] ?? null,
                    'gallery' => $project['gallery'] ?? [],
                ],
            ])->all(), SettingType::Json);

            $this->importServices($services);
            $this->importClients($clients);
            $this->importProjects($projects);
            $this->importCaseStudy($caseStudy);
            $this->importInsights($insights);
            $this->importTestimonials($testimonials);
            $this->importTeam($team);
            $this->importStudio($studio);
            $this->importProcess($process);
            $this->importIntake($intake);
        });
    }

    private function importServices(array $records): void
    {
        foreach ($records as $position => $record) {
            $service = Service::query()->updateOrCreate(
                ['slug' => $record['slug']],
                [
                    'index_label' => $record['index'], 'title' => $record['title'], 'form' => $record['form'],
                    'lede' => $record['lede'], 'why' => $record['why'], 'outcome' => $record['outcome'],
                    'note' => $record['note'], 'position' => $position, 'status' => ContentStatus::Published,
                ],
            );

            foreach ($record['items'] as $itemPosition => $item) {
                ServiceItem::query()->updateOrCreate(
                    ['service_id' => $service->id, 'title' => $item['title']],
                    ['form' => $item['form'], 'summary' => $item['summary'], 'position' => $itemPosition],
                );
            }
        }
    }

    private function importClients(array $content): void
    {
        foreach ($content['names'] as $position => $record) {
            Client::query()->updateOrCreate(
                ['slug' => Str::slug($record['name'])],
                [
                    'name' => $record['name'], 'wordmark' => $record['name'], 'sector' => $record['sector'], 'country' => 'Egypt',
                    'status' => ClientStatus::Active, 'is_featured' => true,
                ],
            );
        }
    }

    private function importProjects(array $records): void
    {
        foreach ($records as $position => $record) {
            $category = Category::query()->updateOrCreate(
                ['type' => CategoryType::Project, 'slug' => Str::slug($record['category'])],
                ['name' => $record['category'], 'position' => $position],
            );
            $client = Client::query()->where('slug', Str::slug($record['client']))->first()
                ?? Client::query()->updateOrCreate(
                    ['slug' => Str::slug($record['client'])],
                    ['name' => $record['client'], 'wordmark' => $record['client'], 'sector' => $record['category'], 'country' => 'Egypt', 'status' => ClientStatus::Active],
                );

            $project = Project::query()->updateOrCreate(
                ['slug' => $record['slug']],
                [
                    'index_label' => $record['index'], 'name' => $record['name'], 'title' => $record['title'],
                    'category_id' => $category->id, 'client_id' => $client?->id, 'year' => (int) $record['year'],
                    'statement' => $record['statement'], 'description' => $record['description'],
                    'challenge' => $record['challenge'], 'solution' => $record['solution'], 'outcome' => $record['outcome'],
                    'result_value' => $record['result']['value'], 'result_label' => $record['result']['label'],
                    'disciplines' => $record['services'], 'stack' => $record['stack'],
                    'plate_seed' => $record['plate']['seed'], 'plate_variant' => $record['plate']['variant'],
                    'plate_ratio' => $record['plate']['ratio'], 'alt' => $record['alt'],
                    'is_featured' => $record['featured'], 'status' => ProjectStatus::Published,
                    'stage' => ProjectStage::Completed, 'position' => $position,
                ],
            );

            $project->services()->sync(Service::query()->whereIn('title', $record['services'])->pluck('id'));
        }
    }

    private function importCaseStudy(array $record): void
    {
        $project = Project::query()->where('slug', $record['project'])->firstOrFail();
        $study = CaseStudy::query()->updateOrCreate(
            ['project_id' => $project->id],
            [
                'eyebrow' => $record['eyebrow'], 'title' => $record['title'], 'summary' => $record['summary'],
                'client_name' => $record['client'], 'duration' => $record['duration'], 'team_size' => $record['team'],
                'role' => $record['role'], 'status' => CaseStudyStatus::Published,
            ],
        );

        foreach ($record['beats'] as $position => $beat) {
            CaseStudyBeat::query()->updateOrCreate(
                ['case_study_id' => $study->id, 'position' => $position],
                [
                    'index_label' => $beat['index'], 'label' => $beat['label'], 'heading' => $beat['heading'],
                    'body' => $beat['body'], 'plate_seed' => $beat['plate']['seed'], 'plate_variant' => $beat['plate']['variant'],
                    'plate_ratio' => $beat['plate']['ratio'], 'alt' => $beat['alt'],
                ],
            );
        }

        foreach ($record['metrics'] as $position => $metric) {
            CaseStudyMetric::query()->updateOrCreate(
                ['case_study_id' => $study->id, 'position' => $position], $metric,
            );
        }
    }

    private function importInsights(array $records): void
    {
        foreach ($records as $position => $record) {
            $category = Category::query()->updateOrCreate(
                ['type' => CategoryType::Insight, 'slug' => Str::slug($record['category'])],
                ['name' => $record['category'], 'position' => $position],
            );
            Insight::query()->updateOrCreate(
                ['slug' => $record['slug']],
                [
                    'index_label' => $record['index'], 'category_id' => $category->id, 'title' => $record['title'],
                    'dek' => $record['dek'], 'body' => $record['body'], 'reading_minutes' => (int) filter_var($record['reading'], FILTER_SANITIZE_NUMBER_INT),
                    'plate_seed' => $record['plate']['seed'], 'plate_variant' => $record['plate']['variant'], 'plate_ratio' => $record['plate']['ratio'],
                    'alt' => $record['alt'], 'status' => ContentStatus::Published, 'published_at' => $record['date'],
                ],
            );
        }
    }

    private function importTestimonials(array $records): void
    {
        foreach ($records as $position => $record) {
            Testimonial::query()->updateOrCreate(
                ['position' => $position],
                [
                    'quote' => $record['quote'], 'author_name' => $record['name'], 'author_role' => $record['role'],
                    'company' => $record['company'], 'client_id' => Client::query()->where('slug', Str::slug($record['company']))->value('id'),
                    'project_id' => Project::query()->where('slug', $record['project'])->value('id'), 'is_published' => true,
                ],
            );
        }
    }

    private function importTeam(array $records): void
    {
        foreach ($records as $position => $record) {
            TeamMember::query()->updateOrCreate(
                ['slug' => Str::slug($record['name'])],
                [
                    'name' => $record['name'], 'role' => $record['role'], 'bio' => $record['bio'],
                    'since_year' => (int) $record['since'], 'position' => $position, 'is_published' => true,
                    'plate_seed' => $record['plate']['seed'] ?? null,
                    'plate_variant' => $record['plate']['variant'] ?? null,
                    'plate_ratio' => $record['plate']['ratio'] ?? null,
                    'alt' => $record['alt'] ?? null,
                ],
            );
        }
    }

    private function importStudio(array $content): void
    {
        foreach ($content['positions'] as $position => $record) {
            StudioPosition::query()->updateOrCreate(['position' => $position], [
                'index_label' => $record['index'], 'title' => $record['title'], 'body' => $record['body'],
            ]);
        }
        foreach ($content['stats'] as $position => $record) {
            StudioStat::query()->updateOrCreate(['position' => $position], $record);
        }
    }

    private function importProcess(array $content): void
    {
        foreach ($content['stages'] as $position => $record) {
            ProcessStage::query()->updateOrCreate(['position' => $position], [
                'index_label' => $record['index'], 'title' => $record['title'], 'window' => $record['window'],
                'body' => $record['body'], 'output' => $record['output'], 'span_start' => $record['span'][0],
                'span_end' => $record['span'][1], 'weight' => $record['weight'],
                'overlap' => (bool) ($record['overlap'] ?? false),
            ]);
        }
    }

    /**
     * Intake options, flattened into rows the CMS can reorder.
     *
     * Budgets keep their project-type group — that mapping is the whole
     * point of the step — and namespace their stored value by it, because
     * the same range is offered under several groups while (kind, value)
     * stays unique. Positions run as one sequence per kind so display order
     * survives the round trip exactly.
     */
    private function importIntake(array $content): void
    {
        $budgets = [];

        foreach ((array) ($content['budgets'] ?? []) as $group => $ranges) {
            foreach ((array) $ranges as $range) {
                $budgets[] = ['value' => $range, 'label' => $range, 'group' => (string) $group];
            }
        }

        $flat = static fn (array $values): array => array_map(
            static fn (string $value): array => ['value' => $value, 'label' => $value, 'group' => IntakeOption::SHARED_GROUP],
            array_values($values),
        );

        $groups = [
            IntakeOptionKind::ProjectType->value => array_values((array) ($content['projectTypes'] ?? [])),
            IntakeOptionKind::Budget->value => $budgets,
            IntakeOptionKind::Timeline->value => $flat((array) ($content['timelines'] ?? [])),
            IntakeOptionKind::CompanySize->value => $flat((array) ($content['companySizes'] ?? [])),
            IntakeOptionKind::Service->value => array_map(
                static fn (string $value): array => ['value' => Str::slug($value), 'label' => $value, 'group' => IntakeOption::SHARED_GROUP],
                array_values((array) ($content['services'] ?? [])),
            ),
        ];

        foreach ($groups as $kind => $options) {
            $enum = IntakeOptionKind::from($kind);

            foreach ($options as $position => $option) {
                IntakeOption::query()->updateOrCreate(
                    [
                        'kind' => $enum,
                        'value' => IntakeOption::qualifiedValue($enum, (string) $option['group'], (string) $option['value']),
                    ],
                    [
                        'label' => $option['label'],
                        'group' => $option['group'],
                        'position' => $position,
                        'is_active' => true,
                    ],
                );
            }
        }
    }

    private function load(string $name): array
    {
        $data = require $this->path.DIRECTORY_SEPARATOR.$name.'.php';

        return is_array($data) ? $data : [];
    }
}
