<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\TestimonialRequest;
use App\Models\Client;
use App\Models\Project;
use App\Models\Testimonial;
use App\Support\Ordering;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class TestimonialController extends AdminController
{
    /** @var array<string, string> */
    private const SORTS = [
        'order' => 'position',
        'author' => 'author_name',
        'company' => 'company',
        'updated' => 'updated_at',
    ];

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Testimonial::class);

        $filters = [
            'q' => $request->string('q')->trim()->toString(),
            'state' => $request->string('state')->toString(),
        ];

        $query = Testimonial::query()
            ->with(['client', 'project'])
            ->search($filters['q'])
            ->when($filters['state'] === 'published', fn ($query) => $query->where('is_published', true))
            ->when($filters['state'] === 'hidden', fn ($query) => $query->where('is_published', false));

        [$sort, $direction] = $this->applySort(
            $query,
            self::SORTS,
            'order',
            $request->string('sort')->toString(),
            $request->string('direction')->toString() ?: 'asc',
        );

        return view('admin.testimonials.index', [
            'testimonials' => $query->paginate(15)->withQueryString(),
            'filters' => $filters,
            'active' => $this->activeFilters($filters),
            'sort' => $sort,
            'direction' => $direction,
            'counts' => [
                'all' => Testimonial::query()->count(),
                'published' => Testimonial::query()->where('is_published', true)->count(),
                'hidden' => Testimonial::query()->where('is_published', false)->count(),
            ],
            'about' => $this->aboutCounts(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Testimonial::class);

        return view('admin.testimonials.create', [
            'testimonial' => new Testimonial(['is_published' => false]),
            'clients' => $this->clients(),
            'projects' => $this->projects(),
        ]);
    }

    public function store(TestimonialRequest $request): RedirectResponse
    {
        $testimonial = Testimonial::query()->create([
            ...$request->validated(),
            'position' => Ordering::nextPosition(Testimonial::query()),
        ]);

        return $this->saved('admin.testimonials.index', "Quote from {$testimonial->author_name} added.");
    }

    public function edit(Testimonial $testimonial): View
    {
        $this->authorize('update', $testimonial);

        return view('admin.testimonials.edit', [
            'testimonial' => $testimonial,
            'clients' => $this->clients(),
            'projects' => $this->projects(),
        ]);
    }

    public function update(TestimonialRequest $request, Testimonial $testimonial): RedirectResponse
    {
        $testimonial->update($request->validated());

        return $this->saved('admin.testimonials.index', "Quote from {$testimonial->author_name} saved.");
    }

    public function destroy(Testimonial $testimonial): RedirectResponse
    {
        $this->authorize('delete', $testimonial);

        $name = $testimonial->author_name;
        $testimonial->delete();

        Ordering::resequence(Testimonial::query());

        return $this->saved('admin.testimonials.index', "Quote from {$name} deleted.");
    }

    public function move(Request $request, Testimonial $testimonial): RedirectResponse
    {
        $this->authorize('update', $testimonial);

        $direction = $request->string('direction')->toString();
        abort_unless(in_array($direction, Ordering::directions(), true), 422);

        $moved = Ordering::move($testimonial, $direction, Testimonial::query());

        return $this->savedBack($moved ? 'Order updated.' : 'Already at that end of the list.');
    }

    /**
     * The homepage shows the first published quote and nothing else, so the
     * publication toggle is the single most consequential control on this
     * screen and gets its own one-click action.
     */
    public function toggle(Testimonial $testimonial): RedirectResponse
    {
        $this->authorize('update', $testimonial);

        $testimonial->update(['is_published' => ! $testimonial->is_published]);

        return $this->savedBack($testimonial->is_published
            ? "Quote from {$testimonial->author_name} is now live."
            : "Quote from {$testimonial->author_name} is hidden.");
    }

    /** @return \Illuminate\Support\Collection<int, Client> */
    private function clients()
    {
        return Client::query()->orderBy('name')->get(['id', 'name']);
    }

    /** @return \Illuminate\Support\Collection<int, Project> */
    private function projects()
    {
        return Project::query()->orderBy('name')->get(['id', 'name', 'slug']);
    }
}
