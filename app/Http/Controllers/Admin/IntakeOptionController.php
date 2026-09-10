<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\IntakeOptionKind;
use App\Http\Requests\Admin\IntakeOptionRequest;
use App\Models\IntakeOption;
use App\Support\Ordering;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * The nine-step project intake, from the answers' side.
 *
 * Every option the visitor can choose on /start-a-project is a row here.
 * The public form reads them through ContentRepository — nothing about this
 * list is hard-coded in Blade — so an option deactivated here disappears
 * from the form on the next request.
 */
final class IntakeOptionController extends AdminController
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', IntakeOption::class);

        $filters = [
            'q' => $request->string('q')->trim()->toString(),
            'kind' => $request->string('kind')->toString(),
            'state' => $request->string('state')->toString(),
        ];

        $options = IntakeOption::query()
            ->search($filters['q'])
            ->when($filters['kind'] !== '', fn ($query) => $query->where('kind', $filters['kind']))
            ->when($filters['state'] === 'active', fn ($query) => $query->where('is_active', true))
            ->when($filters['state'] === 'inactive', fn ($query) => $query->where('is_active', false))
            ->ordered()
            ->get();

        return view('admin.intake.index', [
            // Grouped rather than paginated: the whole point of this screen
            // is seeing a step's options next to each other in the order the
            // visitor meets them, and the largest step has fourteen rows.
            'groups' => $options->groupBy(static fn (IntakeOption $option): string => $option->kind->value),
            'kinds' => IntakeOptionKind::cases(),
            'filters' => $filters,
            'active' => $this->activeFilters($filters),
            'counts' => [
                'all' => IntakeOption::query()->count(),
                'active' => IntakeOption::query()->where('is_active', true)->count(),
                'inactive' => IntakeOption::query()->where('is_active', false)->count(),
            ],
            'projectGroups' => $this->projectGroups(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', IntakeOption::class);

        $kind = IntakeOptionKind::tryFrom($request->string('kind')->toString()) ?? IntakeOptionKind::ProjectType;

        return view('admin.intake.create', [
            'option' => new IntakeOption([
                'kind' => $kind,
                'group' => $kind->usesGroups() ? '' : IntakeOption::SHARED_GROUP,
                'is_active' => true,
            ]),
            'kinds' => IntakeOptionKind::cases(),
            'projectGroups' => $this->projectGroups(),
        ]);
    }

    public function store(IntakeOptionRequest $request): RedirectResponse
    {
        $kind = IntakeOptionKind::from((string) $request->validated('kind'));

        $option = IntakeOption::query()->create([
            'kind' => $kind,
            'group' => $request->validated('group'),
            'value' => $request->storedValue(),
            'label' => $request->validated('label'),
            'is_active' => $request->boolean('is_active'),
            'position' => Ordering::nextPosition(IntakeOption::query()->where('kind', $kind)),
        ]);

        return $this->saved('admin.intake.index', "“{$option->label}” added to {$kind->label()}.");
    }

    public function edit(IntakeOption $intake_option): View
    {
        $this->authorize('update', $intake_option);

        return view('admin.intake.edit', [
            'option' => $intake_option,
            'kinds' => IntakeOptionKind::cases(),
            'projectGroups' => $this->projectGroups(),
        ]);
    }

    public function update(IntakeOptionRequest $request, IntakeOption $intake_option): RedirectResponse
    {
        $intake_option->update([
            'kind' => IntakeOptionKind::from((string) $request->validated('kind')),
            'group' => $request->validated('group'),
            'value' => $request->storedValue(),
            'label' => $request->validated('label'),
            'is_active' => $request->boolean('is_active'),
        ]);

        return $this->saved('admin.intake.index', "“{$intake_option->label}” saved.");
    }

    public function destroy(IntakeOption $intake_option): RedirectResponse
    {
        $this->authorize('delete', $intake_option);

        $kind = $intake_option->kind;
        $label = $intake_option->label;

        // Budgets are keyed to a project type. Removing the last range for a
        // group leaves that step with nothing to offer, which the public
        // form cannot recover from — deactivate the project type instead.
        if ($kind === IntakeOptionKind::Budget) {
            $remaining = IntakeOption::query()
                ->where('kind', $kind)
                ->where('group', $intake_option->group)
                ->whereKeyNot($intake_option->id)
                ->count();

            if ($remaining === 0) {
                return back()->withErrors([
                    'option' => "“{$label}” is the last budget range offered for the {$intake_option->group} group. Add a replacement before removing it.",
                ]);
            }
        }

        $intake_option->delete();

        Ordering::resequence(IntakeOption::query()->where('kind', $kind));

        return $this->saved('admin.intake.index', "“{$label}” deleted.");
    }

    public function move(Request $request, IntakeOption $intake_option): RedirectResponse
    {
        $this->authorize('update', $intake_option);

        $direction = $request->string('direction')->toString();
        abort_unless(in_array($direction, Ordering::directions(), true), 422);

        $moved = Ordering::move($intake_option, $direction, IntakeOption::query()->where('kind', $intake_option->kind));

        return $this->savedBack($moved ? 'Order updated.' : 'Already at that end of the step.');
    }

    public function toggle(IntakeOption $intake_option): RedirectResponse
    {
        $this->authorize('update', $intake_option);

        $intake_option->update(['is_active' => ! $intake_option->is_active]);

        return $this->savedBack($intake_option->is_active
            ? "“{$intake_option->label}” is offered again."
            : "“{$intake_option->label}” is no longer offered.");
    }

    /**
     * The project-type groups budgets can be keyed to. Read from the project
     * type rows themselves so the two lists cannot drift apart.
     *
     * @return list<string>
     */
    private function projectGroups(): array
    {
        return IntakeOption::query()
            ->where('kind', IntakeOptionKind::ProjectType)
            ->ordered()
            ->pluck('group')
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
