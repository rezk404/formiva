<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

final class EnsureAdminGuest
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            if (! $request->user()->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            } else {
                return redirect()->route('admin.dashboard');
            }
        }

        return $next($request);
    }
}
