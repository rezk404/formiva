<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Enums\ContentStatus;
use Illuminate\Database\Eloquent\Builder;

/**
 * The publishing half of a content model.
 *
 * Services and insights share one lifecycle — draft, scheduled, published,
 * archived — and one definition of "live". Keeping that definition in a
 * single place is what stops a list screen, a public query and a dashboard
 * count quietly disagreeing about what a visitor can see.
 *
 * @property ContentStatus $status
 * @property \Illuminate\Support\Carbon|null $published_at
 */
trait Publishable
{
    /**
     * Live to the public: published, and either undated or past its date.
     *
     * @param  Builder<static>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('status', ContentStatus::Published)
            ->where(function (Builder $query): void {
                $query->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    /** @param  Builder<static>  $query */
    public function scopeScheduled(Builder $query): void
    {
        $query->where('status', ContentStatus::Scheduled)
            ->where('published_at', '>', now());
    }

    /**
     * Scheduled and past its moment — the queue formiva:publish-due
     * promotes. Kept separate from scopeScheduled so neither has to explain
     * the other's date comparison.
     *
     * @param  Builder<static>  $query
     */
    public function scopeDue(Builder $query): void
    {
        $query->where('status', ContentStatus::Scheduled)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /** @param  Builder<static>  $query */
    public function scopeStatus(Builder $query, ContentStatus|string|null $status): void
    {
        if ($status === null || $status === '') {
            return;
        }

        $query->where('status', $status instanceof ContentStatus ? $status : ContentStatus::from($status));
    }

    public function isLive(): bool
    {
        return $this->status === ContentStatus::Published
            && ($this->published_at === null || $this->published_at->isPast());
    }
}
