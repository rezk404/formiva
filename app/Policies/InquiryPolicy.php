<?php

declare(strict_types=1);
namespace App\Policies;
final class InquiryPolicy extends AdminResourcePolicy
{
    public function create(\App\Models\User $user): bool { return false; }
    public function update(\App\Models\User $user, mixed $model): bool { return $user->isAdmin(); }
    public function delete(\App\Models\User $user, mixed $model): bool { return $user->isAdmin(); }
}
