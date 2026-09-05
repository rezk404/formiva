<?php

declare(strict_types=1);

namespace App\Providers;

use App\Content\ContentRepository;
use App\Content\StaticContent;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // The one line that changes when the CMS lands. Point the contract at
        // a DatabaseContent implementation and every view keeps working.
        $this->app->singleton(ContentRepository::class, function (): ContentRepository {
            return new StaticContent(resource_path('content'));
        });
    }

    public function boot(): void
    {
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
