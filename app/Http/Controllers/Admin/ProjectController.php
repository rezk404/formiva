<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Content\DatabaseContent;
use App\Enums\CategoryType;
use App\Enums\ProjectStage;
use App\Enums\ProjectStatus;
use App\Http\Requests\Admin\ProjectRequest;
use App\Http\Requests\Admin\PublishRequest;
use App\Models\Category;
use App\Models\Client;
use App\Models\Media;
use App\Models\Project;
use App\Models\Service;
use App\Support\Ordering;
use App\Support\Publishing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class ProjectController extends AdminController
{
    private const SORTS = [
        'position' => 'position',
        'name' => 'name',
        'year' => 'year',
        'status' => 'status',
        'updated' => 'updated_at',
    ];

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Project::class);

        $filters = [
            'q' => $request->string('q')->trim()->toString(),
            'status' => $request->string('status')->toString(),
            'stage' => $request->string('stage')->toString(),
            'category' => $request->string('category')->toString(),
            'client' => $request->string('client')->toString(),
            'featured' => $request->string('featured')->toString(),
        ];

        $query = Project::query()->with(['category', 'client', 'coverMedia', 'caseStudy'])
            ->when($filters['q'] !== '', fn ($query) => $query->where(function ($query) use ($filters): void {
                $query->where('name', 'like', '%'.$filters['q'].'%')
                    ->orWhere('title', 'like', '%'.$filters['q'].'%')
                    ->orWhere('slug', 'like', '%'.$filters['q'].'%');
            }))
            ->when($filters['status'] !== '', fn ($query) => $query->where('status', $filters['status']))
            ->when($filters['stage'] !== '', fn ($query) => $query->where('stage', $filters['stage']))
            ->when($filters['category'] !== '', fn ($query) => $query->where('category_id', (int) $filters['category']))
            ->when($filters['client'] !== '', fn ($query) => $query->where('client_id', (int) $filters['client']))
            ->when($filters['featured'] !== '', fn ($query) => $query->where('is_featured', $filters['featured'] === 'yes'));

        [$sort, $direction] = $this->applySort($query, self::SORTS, 'position', $request->string('sort')->toString(), $request->string('direction')->toString() ?: 'asc');

        return view('admin.projects.index', [
            'projects' => $query->paginate(15)->withQueryString(),
            'filters' => $filters,
            'active' => $this->activeFilters($filters),
            'sort' => $sort,
            'direction' => $direction,
            'categories' => Category::query()->ofType(CategoryType::Project)->ordered()->get(),
            'clients' => Client::query()->orderBy('name')->get(['id', 'name']),
            'counts' => $this->statusCounts(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Project::class);

        return view('admin.projects.create', $this->formData(new Project([
            'status' => ProjectStatus::Draft,
            'stage' => ProjectStage::Scoping,
            'index_label' => str_pad((string) (Project::query()->count() + 1), 2, '0', STR_PAD_LEFT),
            'year' => now()->year,
            'plate_seed' => 1000 + (int) (crc32((string) now()->timestamp) % 9000),
            'plate_variant' => 'ink',
            'plate_ratio' => '4/3',
            'created_by' => $request->user()->id,
        ])));
    }

    public function store(ProjectRequest $request): RedirectResponse
    {
        $project = DB::transaction(function () use ($request): Project {
            $project = Project::query()->create([
                ...$request->content(),
                'created_by' => $request->user()->id,
                'position' => Ordering::nextPosition(Project::query()),
                ...Publishing::reconcile(Publishing::attributes($request->intent(), statusClass: ProjectStatus::class), ProjectStatus::class),
            ]);
            $this->syncGallery($project, $request->galleryMedia());
            $project->services()->sync($request->services());

            return $project;
        });

        return $this->saved('admin.projects.edit', $project->isLive() ? "“{$project->name}” created and published." : "“{$project->name}” created as a draft.", [$project]);
    }

    public function edit(Project $project): View
    {
        $this->authorize('update', $project);

        return view('admin.projects.edit', $this->formData($project->load(['category', 'client', 'services', 'caseStudy', 'galleryMedia'])) + [
            'transitions' => Publishing::availableFor($project->status),
        ]);
    }

    public function update(ProjectRequest $request, Project $project): RedirectResponse
    {
        DB::transaction(function () use ($request, $project): void {
            $project->update($request->content());
            $this->syncGallery($project, $request->galleryMedia());
            $project->services()->sync($request->services());
        });

        return $this->saved('admin.projects.edit', "“{$project->name}” saved.", [$project]);
    }

    public function destroy(Project $project): RedirectResponse
    {
        $this->authorize('delete', $project);
        $name = $project->name;
        $project->delete();
        Ordering::resequence(Project::query());

        return $this->saved('admin.projects.index', "“{$name}” deleted.");
    }

    public function move(Request $request, Project $project): RedirectResponse
    {
        $this->authorize('update', $project);
        $direction = $request->string('direction')->toString();
        abort_unless(in_array($direction, Ordering::directions(), true), 422);

        return $this->savedBack(Ordering::move($project, $direction, Project::query()) ? 'Order updated.' : 'Already at that end of the list.');
    }

    public function toggleFeatured(Project $project): RedirectResponse
    {
        $this->authorize('update', $project);
        $project->update(['is_featured' => ! $project->is_featured]);

        return $this->savedBack($project->is_featured ? "“{$project->name}” is featured on the homepage." : "“{$project->name}” was removed from featured work.");
    }

    public function publish(PublishRequest $request, Project $project): RedirectResponse
    {
        $action = $request->action();
        abort_unless(in_array($action, Publishing::availableFor($project->status), true), 422);
        Publishing::apply($project, $action, $request->scheduledFor());

        return $this->savedBack(sprintf('“%s” is now %s.', $project->name, $project->status->value));
    }

    public function preview(Project $project): View
    {
        $this->authorize('view', $project);
        $content = new DatabaseContent();
        $projectData = $content->previewProject($project);

        return view('pages.project', [
            'project' => $projectData,
            'neighbors' => ['previous' => null, 'next' => null],
            'site' => $content->site(),
            'preview' => true,
        ]);
    }

    /** @return array<string, mixed> */
    private function formData(Project $project): array
    {
        return [
            'project' => $project,
            'categories' => Category::query()->ofType(CategoryType::Project)->ordered()->get(),
            'clients' => Client::query()->withCount('projects')->orderBy('name')->get(),
            'services' => Service::query()->orderBy('position')->get(),
            'media' => Media::query()->latest()->limit(60)->get(),
            'gallery' => $project->exists ? $project->galleryMedia()->get() : collect(),
        ];
    }

    /** @param list<int> $mediaIds */
    private function syncGallery(Project $project, array $mediaIds): void
    {
        $payload = [];
        foreach ($mediaIds as $position => $mediaId) {
            $payload[$mediaId] = ['collection' => 'gallery', 'position' => $position];
        }
        $project->media()->wherePivot('collection', 'gallery')->detach();
        if ($payload !== []) {
            $project->media()->attach($payload);
        }
    }

    /** @return array<string, mixed> */
    private function statusCounts(): array
    {
        $counts = Project::query()->toBase()->selectRaw('status, count(*) as aggregate')->groupBy('status')->pluck('aggregate', 'status')->all();
        $result = ['all' => array_sum($counts)];
        foreach (ProjectStatus::cases() as $status) {
            $result[$status->value] = (int) ($counts[$status->value] ?? 0);
        }

        return $result;
    }
}
