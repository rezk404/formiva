<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\TeamMemberRequest;
use App\Models\TeamMember;
use App\Support\Ordering;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class TeamMemberController extends AdminController
{
    /** @var array<string, string> */
    private const SORTS = [
        'order' => 'position',
        'name' => 'name',
        'since' => 'since_year',
        'updated' => 'updated_at',
    ];

    public function index(Request $request): View
    {
        $this->authorize('viewAny', TeamMember::class);

        $filters = [
            'q' => $request->string('q')->trim()->toString(),
            'state' => $request->string('state')->toString(),
        ];

        $query = TeamMember::query()
            ->with('photo')
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

        return view('admin.team.index', [
            'members' => $query->paginate(24)->withQueryString(),
            'filters' => $filters,
            'active' => $this->activeFilters($filters),
            'sort' => $sort,
            'direction' => $direction,
            'counts' => [
                'all' => TeamMember::query()->count(),
                'published' => TeamMember::query()->where('is_published', true)->count(),
                'hidden' => TeamMember::query()->where('is_published', false)->count(),
            ],
            'about' => $this->aboutCounts(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', TeamMember::class);

        return view('admin.team.create', [
            'member' => new TeamMember([
                'is_published' => false,
                'since_year' => (int) date('Y'),
                'plate_variant' => 'bone',
                'plate_ratio' => '3/4',
            ]),
        ]);
    }

    public function store(TeamMemberRequest $request): RedirectResponse
    {
        $member = TeamMember::query()->create([
            ...$request->validated(),
            'position' => Ordering::nextPosition(TeamMember::query()),
        ]);

        return $this->saved('admin.team.index', "{$member->name} added to the studio.");
    }

    public function edit(TeamMember $team_member): View
    {
        $this->authorize('update', $team_member);

        return view('admin.team.edit', [
            'member' => $team_member->load('photo'),
        ]);
    }

    public function update(TeamMemberRequest $request, TeamMember $team_member): RedirectResponse
    {
        $team_member->update($request->validated());

        return $this->saved('admin.team.index', "{$team_member->name} saved.");
    }

    public function destroy(TeamMember $team_member): RedirectResponse
    {
        $this->authorize('delete', $team_member);

        $name = $team_member->name;
        $team_member->delete();

        Ordering::resequence(TeamMember::query());

        return $this->saved('admin.team.index', "{$name} removed from the studio page.");
    }

    public function move(Request $request, TeamMember $team_member): RedirectResponse
    {
        $this->authorize('update', $team_member);

        $direction = $request->string('direction')->toString();
        abort_unless(in_array($direction, Ordering::directions(), true), 422);

        $moved = Ordering::move($team_member, $direction, TeamMember::query());

        return $this->savedBack($moved ? 'Order updated.' : 'Already at that end of the list.');
    }

    public function toggle(TeamMember $team_member): RedirectResponse
    {
        $this->authorize('update', $team_member);

        $team_member->update(['is_published' => ! $team_member->is_published]);

        return $this->savedBack($team_member->is_published
            ? "{$team_member->name} is now on the studio page."
            : "{$team_member->name} is hidden from the studio page.");
    }
}
