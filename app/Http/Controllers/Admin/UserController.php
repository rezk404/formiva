<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Requests\Admin\UserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

final class UserController extends AdminController
{
    /** @var array<string, string> */
    private const SORTS = [
        'name' => 'name',
        'role' => 'role',
        'signed_in' => 'last_login_at',
        'created' => 'created_at',
    ];

    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $filters = [
            'q' => $request->string('q')->trim()->toString(),
            'role' => $request->string('role')->toString(),
            'state' => $request->string('state')->toString(),
        ];

        $query = User::query()
            ->when($filters['q'] !== '', fn ($query) => $query->where(
                fn ($inner) => $inner->where('name', 'like', "%{$filters['q']}%")
                    ->orWhere('email', 'like', "%{$filters['q']}%"),
            ))
            ->when($filters['role'] !== '', fn ($query) => $query->where('role', $filters['role']))
            ->when($filters['state'] === 'active', fn ($query) => $query->where('is_active', true))
            ->when($filters['state'] === 'inactive', fn ($query) => $query->where('is_active', false))
            ->withCount('insights');

        [$sort, $direction] = $this->applySort(
            $query,
            self::SORTS,
            'name',
            $request->string('sort')->toString(),
            $request->string('direction')->toString() ?: 'asc',
        );

        return view('admin.users.index', [
            'users' => $query->paginate(20)->withQueryString(),
            'filters' => $filters,
            'active' => $this->activeFilters($filters),
            'sort' => $sort,
            'direction' => $direction,
            'roles' => UserRole::cases(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', User::class);

        return view('admin.users.create', [
            'account' => new User(['role' => UserRole::Editor, 'is_active' => true]),
            'roles' => UserRole::cases(),
        ]);
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $account = User::query()->create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'role' => UserRole::from((string) $request->validated('role')),
            'is_active' => $request->boolean('is_active'),
            'password' => Hash::make((string) $request->validated('password')),
        ]);

        return $this->saved('admin.users.index', "{$account->name} can now sign in.");
    }

    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        return view('admin.users.edit', [
            'account' => $user->loadCount('insights'),
            'roles' => UserRole::cases(),
        ]);
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $attributes = [
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'role' => UserRole::from((string) $request->validated('role')),
            'is_active' => $request->boolean('is_active'),
        ];

        // Guard rails an admin cannot talk themselves past: locking your own
        // account or demoting yourself takes the last admin out of the
        // workspace mid-session, and there is no way back in from the UI.
        if ($user->id === $request->user()->id) {
            $attributes['role'] = $user->role;
            $attributes['is_active'] = true;
        }

        if ($password = $request->validated('password')) {
            $attributes['password'] = Hash::make((string) $password);
        }

        $user->forceFill($attributes)->save();

        return $this->saved('admin.users.index', "{$user->name} updated.");
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        // Removing the last person who can reach settings and accounts locks
        // the workspace for everyone. Suspended admins do not count — they
        // cannot sign in to fix it either.
        $lastActiveAdmin = $user->isAdmin()
            && $user->is_active
            && ! User::query()
                ->where('role', UserRole::Admin)
                ->where('is_active', true)
                ->whereKeyNot($user->id)
                ->exists();

        if ($lastActiveAdmin) {
            return back()->withErrors([
                'user' => 'This is the only active admin. Promote someone else before removing this account.',
            ]);
        }

        $name = $user->name;
        $user->delete();

        return $this->saved('admin.users.index', "{$name} removed. Their authored entries are kept.");
    }
}
