<?php

declare(strict_types=1);

namespace App\Providers;

use App\Content\ContentRepository;
use App\Content\CachedContent;
use App\Content\DatabaseContent;
use App\Content\StaticContent;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ContentRepository::class, function (): ContentRepository {
            $source = match (config('formiva.content_source', 'static')) {
                'database' => new DatabaseContent(),
                default => new StaticContent(resource_path('content')),
            };

            if (! config('formiva.content_cache.enabled', false)) {
                return $source;
            }

            return new CachedContent(
                $source,
                app(CacheRepository::class),
                (int) config('formiva.content_cache.ttl', 3600),
            );
        });
    }

    public function boot(): void
    {
        RateLimiter::for('admin-login', function (Request $request): Limit {
            return Limit::perMinute(5)->by(strtolower((string) $request->input('email')).'|'.$request->ip());
        });

        /*
         | The layout needs site settings on every render — including on the
         | error pages, which no controller of ours ever reaches. Binding it
         | once here means a 404 renders with the real navigation and footer
         | instead of Laravel's unbranded default, and controllers stop
         | repeating the same line in every method.
         |
         | Explicitly passed data still wins: this only fills the gap.
         */
        View::composer('layouts.app', function ($view): void {
            if (! array_key_exists('site', $view->getData())) {
                $view->with('site', app(ContentRepository::class)->site());
            }
        });
    }
}
