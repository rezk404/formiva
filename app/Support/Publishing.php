<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\ContentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * One description of what each publishing action means.
 *
 * Without this the same four transitions get re-implemented in the service
 * controller, the insight controller and whatever arrives next — and they
 * drift, almost always around what happens to published_at.
 */
final class Publishing
{
    public const PUBLISH = 'publish';

    public const SCHEDULE = 'schedule';

    public const DRAFT = 'draft';

    public const UNPUBLISH = 'unpublish';

    public const ARCHIVE = 'archive';

    /** @return list<string> */
    public static function actions(): array
    {
        return [self::PUBLISH, self::SCHEDULE, self::DRAFT, self::UNPUBLISH, self::ARCHIVE];
    }

    /**
     * The transitions that are meaningful from a given state. The UI renders
     * exactly this list, so an action a record cannot accept is never shown.
     *
     * @return list<string>
     */
    public static function availableFor(ContentStatus $status): array
    {
        return match ($status) {
            ContentStatus::Draft => [self::PUBLISH, self::SCHEDULE, self::ARCHIVE],
            ContentStatus::Scheduled => [self::PUBLISH, self::SCHEDULE, self::DRAFT, self::ARCHIVE],
            ContentStatus::Published => [self::SCHEDULE, self::UNPUBLISH, self::ARCHIVE],
            ContentStatus::Archived => [self::PUBLISH, self::DRAFT],
        };
    }

    public static function label(string $action): string
    {
        return match ($action) {
            self::PUBLISH => 'Publish',
            self::SCHEDULE => 'Schedule',
            self::DRAFT => 'Move to draft',
            self::UNPUBLISH => 'Unpublish',
            self::ARCHIVE => 'Archive',
            default => ucfirst($action),
        };
    }

    /** Whether an action removes the record from the public site. */
    public static function isWithdrawal(string $action): bool
    {
        return $action === self::UNPUBLISH || $action === self::ARCHIVE || $action === self::DRAFT;
    }

    /**
     * Attributes for a transition. published_at survives a re-publish when
     * it already sits in the past, so restoring an archived entry does not
     * silently re-date it and reshuffle the journal.
     *
     * @return array{status: ContentStatus, published_at: Carbon|null}
     */
    public static function attributes(string $action, ?Carbon $scheduledFor = null, ?Carbon $currentPublishedAt = null): array
    {
        return match ($action) {
            self::PUBLISH => [
                'status' => ContentStatus::Published,
                'published_at' => $currentPublishedAt !== null && $currentPublishedAt->isPast()
                    ? $currentPublishedAt
                    : Carbon::now(),
            ],
            self::SCHEDULE => [
                'status' => ContentStatus::Scheduled,
                'published_at' => $scheduledFor,
            ],
            self::ARCHIVE => [
                'status' => ContentStatus::Archived,
                'published_at' => $currentPublishedAt,
            ],
            default => [
                'status' => ContentStatus::Draft,
                'published_at' => null,
            ],
        };
    }

    /**
     * Reconciles the status/date pair coming out of an editor form, where a
     * writer can choose "Published" and leave the date empty, or choose
     * "Scheduled" with a date that has already gone.
     *
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    public static function reconcile(array $attributes): array
    {
        $status = $attributes['status'] ?? ContentStatus::Draft;
        $status = $status instanceof ContentStatus ? $status : ContentStatus::from((string) $status);

        $publishedAt = $attributes['published_at'] ?? null;

        if (! $publishedAt instanceof Carbon) {
            $publishedAt = ($publishedAt === null || $publishedAt === '')
                ? null
                : Carbon::parse((string) $publishedAt);
        }

        if ($status === ContentStatus::Published && $publishedAt === null) {
            $publishedAt = Carbon::now();
        }

        // A future date against a "published" status is a schedule the
        // writer described in the other order. Honour the date.
        if ($status === ContentStatus::Published && $publishedAt->isFuture()) {
            $status = ContentStatus::Scheduled;
        }

        // A schedule with no moment is not a schedule.
        if ($status === ContentStatus::Scheduled && $publishedAt === null) {
            $status = ContentStatus::Draft;
        }

        if ($status === ContentStatus::Draft) {
            $publishedAt = null;
        }

        $attributes['status'] = $status;
        $attributes['published_at'] = $publishedAt;

        return $attributes;
    }

    /**
     * Applies a transition to a model and persists it.
     *
     * attributes() decides what the transition means; reconcile() then holds
     * the invariants — no published record without a date, no schedule
     * without a moment. Running both means there is one place a state/date
     * pair can be wrong, not two.
     */
    public static function apply(Model $model, string $action, ?Carbon $scheduledFor = null): void
    {
        $current = $model->getAttribute('published_at');

        $model->forceFill(self::reconcile(self::attributes(
            $action,
            $scheduledFor,
            $current instanceof Carbon ? $current : null,
        )))->save();
    }
}
