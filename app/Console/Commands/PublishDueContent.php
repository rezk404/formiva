<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Content\CachedContent;
use App\Content\ContentRepository;
use App\Enums\ContentStatus;
use App\Models\Insight;
use App\Models\Service;
use App\Models\Project;
use App\Enums\ProjectStatus;
use Illuminate\Console\Command;

/**
 * Promotes scheduled content whose moment has arrived.
 *
 * The public scopes deliberately do not treat "scheduled" as live — a
 * visitor must never see something before its date, and a query that
 * inferred publication from a timestamp alone would make an archived entry
 * with an old date reappear. Publication stays an explicit state; this
 * command is what moves a record into it, on a schedule.
 */
final class PublishDueContent extends Command
{
    protected $signature = 'formiva:publish-due {--dry-run : List what would be published without writing}';

    protected $description = 'Publish scheduled projects, insights and services whose publish date has passed';

    public function handle(): int
    {
        $insights = Insight::query()->due()->get();
        $services = Service::query()->due()->get();
        $projects = Project::query()->due()->get();

        if ($insights->isEmpty() && $services->isEmpty() && $projects->isEmpty()) {
            $this->info('Nothing is due.');

            return self::SUCCESS;
        }

        foreach ($insights as $insight) {
            $this->line(sprintf('Insight  %s  (%s)', $insight->slug, $insight->published_at->toDateTimeString()));

            if (! $this->option('dry-run')) {
                $insight->forceFill(['status' => ContentStatus::Published])->save();
            }
        }

        foreach ($services as $service) {
            $this->line(sprintf('Service  %s  (%s)', $service->slug, $service->published_at->toDateTimeString()));

            if (! $this->option('dry-run')) {
                $service->forceFill(['status' => ContentStatus::Published])->save();
            }
        }

        foreach ($projects as $project) {
            $this->line(sprintf('Project  %s  (%s)', $project->slug, $project->published_at->toDateTimeString()));

            if (! $this->option('dry-run')) {
                $project->forceFill(['status' => ProjectStatus::Published])->save();
            }
        }

        $total = $insights->count() + $services->count() + $projects->count();

        if (! $this->option('dry-run')) {
            // Nothing in an HTTP request caused this, so nothing in the admin
            // will have invalidated the public cache. Without this, scheduled
            // content is promoted in the database and stays invisible on the
            // site until the TTL expires.
            $content = app(ContentRepository::class);

            if ($content instanceof CachedContent) {
                $content->flush();
            }
        }

        $this->info($this->option('dry-run')
            ? "{$total} item(s) would be published."
            : "{$total} item(s) published.");

        return self::SUCCESS;
    }
}
