<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

abstract class AdminResourcePolicy
{
    public function before(User $user): ?bool
    {
        if (! $user->is_active) {
            return false;
        }

        return $user->isAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->isEditor();
    }

    public function view(User $user, mixed $model): bool
    {
        return $user->isEditor();
    }

    public function create(User $user): bool
    {
        return $user->isEditor();
    }

    public function update(User $user, mixed $model): bool
    {
        return $user->isEditor();
    }

    public function delete(User $user, mixed $model): bool
    {
        return $user->isEditor();
    }
}
