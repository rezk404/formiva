<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureAdminUser
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->role === UserRole::Admin, 403);

        return $next($request);
    }
}
