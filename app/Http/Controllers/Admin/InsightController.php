<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\CategoryType;
use App\Enums\ContentStatus;
use App\Http\Requests\Admin\InsightRequest;
use App\Http\Requests\Admin\PublishRequest;
use App\Models\Category;
use App\Models\Insight;
use App\Models\User;
use App\Support\Publishing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class InsightController extends AdminController
{
    /** @var array<string, string> */
    private const SORTS = [
        'published' => 'published_at',
        'title' => 'title',
        'status' => 'status',
        'updated' => 'updated_at',
    ];

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Insight::class);

        $filters = [
            'q' => $request->string('q')->trim()->toString(),
            'status' => $request->string('status')->toString(),
            'category' => $request->string('category')->toString(),
            'author' => $request->string('author')->toString(),
        ];

        $query = Insight::query()
            ->with(['category', 'author'])
            ->search($filters['q'])
            ->status($filters['status'] !== '' ? $filters['status'] : null)
            ->when($filters['category'] !== '', fn ($query) => $query->where('category_id', (int) $filters['category']))
            ->when($filters['author'] !== '', fn ($query) => $query->where('author_id', (int) $filters['author']));

        [$sort, $direction] = $this->applySort(
            $query,
            self::SORTS,
            'published',
            $request->string('sort')->toString(),
            $request->string('direction')->toString(),
        );

        return view('admin.insights.index', [
            'insights' => $query->paginate(15)->withQueryString(),
            'filters' => $filters,
            'active' => $this->activeFilters($filters),
            'sort' => $sort,
            'direction' => $direction,
            'categories' => $this->categories(),
            'authors' => $this->authors(),
            'counts' => $this->statusCounts(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Insight::class);

        return view('admin.insights.create', [
            'insight' => new Insight([
                'status' => ContentStatus::Draft,
                'index_label' => str_pad((string) (Insight::query()->count() + 1), 2, '0', STR_PAD_LEFT),
                'plate_variant' => 'ink',
                'plate_ratio' => '16/10',
                'author_id' => $request->user()->id,
            ]),
            'categories' => $this->categories(),
            'authors' => $this->authors(),
        ]);
    }

    public function store(InsightRequest $request): RedirectResponse
    {
        $insight = Insight::query()->create([
            ...$this->attributes($request),
            ...Publishing::reconcile(Publishing::attributes($request->intent())),
        ]);

        return $this->saved(
            'admin.insights.edit',
            $insight->isLive()
                ? "“{$insight->title}” created and published."
                : "“{$insight->title}” created as a draft.",
            [$insight],
        );
    }

    public function edit(Insight $insight): View
    {
        $this->authorize('update', $insight);

        return view('admin.insights.edit', [
            'insight' => $insight,
            'categories' => $this->categories(),
            'authors' => $this->authors(),
            'transitions' => Publishing::availableFor($insight->status),
        ]);
    }

    public function update(InsightRequest $request, Insight $insight): RedirectResponse
    {
        $insight->update($this->attributes($request));

        return $this->saved('admin.insights.edit', "“{$insight->title}” saved.", [$insight]);
    }

    public function destroy(Insight $insight): RedirectResponse
    {
        $this->authorize('delete', $insight);

        $title = $insight->title;
        $insight->delete();

        return $this->saved('admin.insights.index', "“{$title}” deleted.");
    }

    public function publish(PublishRequest $request, Insight $insight): RedirectResponse
    {
        $action = $request->action();

        abort_unless(in_array($action, Publishing::availableFor($insight->status), true), 422);

        Publishing::apply($insight, $action, $request->scheduledFor());

        return $this->savedBack(sprintf('“%s” is now %s.', $insight->title, $insight->status->label()));
    }

    /**
     * The entry's content, with the two fields the editor leaves optional
     * filled in: reading time is derived from the body when blank, and an
     * empty plate seed becomes one derived from the slug, so the same entry
     * always draws the same cover.
     *
     * Publishing state is deliberately absent — see ServiceController.
     *
     * @return array<string, mixed>
     */
    private function attributes(InsightRequest $request): array
    {
        $attributes = $request->safe()->except('intent');

        $attributes['reading_minutes'] = ($attributes['reading_minutes'] ?? null)
            ?: Insight::estimateReadingMinutes((string) ($attributes['body'] ?? ''));

        $attributes['plate_seed'] = ($attributes['plate_seed'] ?? null)
            ?: 1000 + (int) (crc32(($attributes['slug'] ?? '').'-plate') % 9000);

        return $attributes;
    }

    /** @return \Illuminate\Support\Collection<int, Category> */
    private function categories()
    {
        return Category::query()->ofType(CategoryType::Insight)->ordered()->get();
    }

    /** @return \Illuminate\Support\Collection<int, User> */
    private function authors()
    {
        return User::query()->orderBy('name')->get(['id', 'name', 'role']);
    }

    /** @return array<string, int> */
    private function statusCounts(): array
    {
        $counts = Insight::query()
            ->toBase()
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status')
            ->all();

        $shape = ['all' => array_sum($counts)];

        foreach (ContentStatus::cases() as $status) {
            $shape[$status->value] = (int) ($counts[$status->value] ?? 0);
        }

        return $shape;
    }
}
