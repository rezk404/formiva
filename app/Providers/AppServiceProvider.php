<?php

declare(strict_types=1);

namespace App\Providers;

use App\Content\ContentRepository;
use App\Content\StaticContent;
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
        //
    }
}
