<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

final class UserPolicy
{
    /**
     * Admins get everything except deletion, which falls through to the
     * method below so an admin cannot delete the account they are signed in
     * with — the one destructive action here that has no undo and locks the
     * person doing it out of the workspace mid-request.
     *
     * Editors keep exactly what they had: their own record, nothing else.
     */
    public function before(User $user, string $ability): ?bool
    {
        if (! $user->is_active) {
            return false;
        }

        if (! $user->isAdmin()) {
            return in_array($ability, ['view', 'update'], true) ? null : false;
        }

        return $ability === 'delete' ? null : true;
    }

    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, User $model): bool
    {
        return $user->id === $model->id;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, User $model): bool
    {
        return $user->id === $model->id;
    }

    public function delete(User $user, User $model): bool
    {
        return $user->isAdmin() && $user->id !== $model->id;
    }
}
