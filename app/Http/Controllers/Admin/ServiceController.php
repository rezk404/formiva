<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\ContentStatus;
use App\Http\Requests\Admin\PublishRequest;
use App\Http\Requests\Admin\ServiceRequest;
use App\Models\Service;
use App\Models\ServiceItem;
use App\Support\Ordering;
use App\Support\Publishing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class ServiceController extends AdminController
{
    /** @var array<string, string> */
    private const SORTS = [
        'order' => 'position',
        'title' => 'title',
        'status' => 'status',
        'updated' => 'updated_at',
    ];

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Service::class);

        $filters = [
            'q' => $request->string('q')->trim()->toString(),
            'status' => $request->string('status')->toString(),
        ];

        $query = Service::query()
            ->withCount('items')
            ->search($filters['q'])
            ->status($filters['status'] !== '' ? $filters['status'] : null);

        [$sort, $direction] = $this->applySort(
            $query,
            self::SORTS,
            'order',
            $request->string('sort')->toString(),
            $request->string('direction')->toString() ?: 'asc',
        );

        return view('admin.services.index', [
            'services' => $query->paginate(15)->withQueryString(),
            'filters' => $filters,
            'active' => $this->activeFilters($filters),
            'sort' => $sort,
            'direction' => $direction,
            'counts' => $this->statusCounts(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Service::class);

        return view('admin.services.create', [
            'service' => new Service([
                'status' => ContentStatus::Draft,
                'form' => 'modular',
                'index_label' => str_pad((string) (Service::query()->count() + 1), 2, '0', STR_PAD_LEFT),
            ]),
            'items' => collect(),
        ]);
    }

    public function store(ServiceRequest $request): RedirectResponse
    {
        $service = DB::transaction(function () use ($request): Service {
            $service = Service::query()->create([
                ...$request->safe()->except(['items', 'intent']),
                ...Publishing::reconcile(Publishing::attributes($request->intent())),
                'position' => Ordering::nextPosition(Service::query()),
            ]);

            $this->syncItems($service, $request->items());

            return $service;
        });

        return $this->saved(
            'admin.services.edit',
            $service->isLive()
                ? "“{$service->title}” created and published."
                : "“{$service->title}” created as a draft.",
            [$service],
        );
    }

    public function edit(Service $service): View
    {
        $this->authorize('update', $service);

        return view('admin.services.edit', [
            'service' => $service,
            'items' => $service->items()->get(),
            'transitions' => Publishing::availableFor($service->status),
        ]);
    }

    public function update(ServiceRequest $request, Service $service): RedirectResponse
    {
        // Content only. Saving an edit never changes what the public can see:
        // a published service stays published, a draft stays a draft, and the
        // move between them is an explicit act in the publishing bar.
        DB::transaction(function () use ($request, $service): void {
            $service->update($request->safe()->except(['items', 'intent']));

            $this->syncItems($service, $request->items());
        });

        return $this->saved('admin.services.edit', "“{$service->title}” saved.", [$service]);
    }

    public function destroy(Service $service): RedirectResponse
    {
        $this->authorize('delete', $service);

        $title = $service->title;

        // Soft delete: service_items cascade on a hard delete only, so the
        // capability rows stay attached and the record can be restored from
        // the database if the removal turns out to be a mistake.
        $service->delete();

        Ordering::resequence(Service::query());

        return $this->saved('admin.services.index', "“{$title}” deleted.");
    }

    public function move(Request $request, Service $service): RedirectResponse
    {
        $this->authorize('update', $service);

        $direction = $request->string('direction')->toString();
        abort_unless(in_array($direction, Ordering::directions(), true), 422);

        $moved = Ordering::move($service, $direction, Service::query());

        return $this->savedBack($moved ? 'Order updated.' : 'Already at that end of the list.');
    }

    public function publish(PublishRequest $request, Service $service): RedirectResponse
    {
        $action = $request->action();

        abort_unless(in_array($action, Publishing::availableFor($service->status), true), 422);

        Publishing::apply($service, $action, $request->scheduledFor());

        return $this->savedBack(sprintf('“%s” is now %s.', $service->title, $service->status->label()));
    }

    /**
     * Reconciles the nested capability rows with what is stored.
     *
     * Rows carrying an id that belongs to this service are updated in place
     * so nothing loses its identity on save; anything no longer submitted is
     * removed. Position follows submitted order, which is what the move
     * controls in the editor actually change.
     *
     * @param  list<array<string, mixed>>  $items
     */
    private function syncItems(Service $service, array $items): void
    {
        $existing = $service->items()->pluck('id')->all();
        $kept = [];

        foreach ($items as $position => $item) {
            $id = isset($item['id']) ? (int) $item['id'] : 0;

            $attributes = [
                'title' => $item['title'],
                'form' => $item['form'],
                'summary' => $item['summary'],
                'position' => $position,
            ];

            if ($id > 0 && in_array($id, $existing, true)) {
                ServiceItem::query()->whereKey($id)->update($attributes);
                $kept[] = $id;

                continue;
            }

            $kept[] = $service->items()->create($attributes)->id;
        }

        ServiceItem::query()
            ->where('service_id', $service->id)
            ->whereNotIn('id', $kept === [] ? [0] : $kept)
            ->delete();
    }

    /** @return array<string, int> */
    private function statusCounts(): array
    {
        // toBase() keeps the soft-delete scope but drops attribute casting,
        // so the grouped keys come back as plain strings and can be used as
        // array keys.
        $counts = Service::query()
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
