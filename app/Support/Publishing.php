<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\ContentStatus;
use BackedEnum;
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
    public static function availableFor(BackedEnum $status): array
    {
        return match ($status->value) {
            'draft' => [self::PUBLISH, self::SCHEDULE, self::ARCHIVE],
            'scheduled' => [self::PUBLISH, self::SCHEDULE, self::DRAFT, self::ARCHIVE],
            'published' => [self::SCHEDULE, self::UNPUBLISH, self::ARCHIVE],
            'archived' => [self::PUBLISH, self::DRAFT],
            default => [],
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
    public static function attributes(
        string $action,
        ?Carbon $scheduledFor = null,
        ?Carbon $currentPublishedAt = null,
        string $statusClass = ContentStatus::class,
    ): array
    {
        $status = static fn (string $value): BackedEnum => $statusClass::from($value);

        return match ($action) {
            self::PUBLISH => [
                'status' => $status('published'),
                'published_at' => $currentPublishedAt !== null && $currentPublishedAt->isPast()
                    ? $currentPublishedAt
                    : Carbon::now(),
            ],
            self::SCHEDULE => [
                'status' => $status('scheduled'),
                'published_at' => $scheduledFor,
            ],
            self::ARCHIVE => [
                'status' => $status('archived'),
                'published_at' => $currentPublishedAt,
            ],
            default => [
                'status' => $status('draft'),
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
    public static function reconcile(array $attributes, string $statusClass = ContentStatus::class): array
    {
        $status = $attributes['status'] ?? $statusClass::from('draft');
        $status = $status instanceof BackedEnum ? $status : $statusClass::from((string) $status);

        $publishedAt = $attributes['published_at'] ?? null;

        if (! $publishedAt instanceof Carbon) {
            $publishedAt = ($publishedAt === null || $publishedAt === '')
                ? null
                : Carbon::parse((string) $publishedAt);
        }

        if ($status->value === 'published' && $publishedAt === null) {
            $publishedAt = Carbon::now();
        }

        // A future date against a "published" status is a schedule the
        // writer described in the other order. Honour the date.
        if ($status->value === 'published' && $publishedAt?->isFuture()) {
            $status = $statusClass::from('scheduled');
        }

        // A schedule with no moment is not a schedule.
        if ($status->value === 'scheduled' && $publishedAt === null) {
            $status = $statusClass::from('draft');
        }

        if ($status->value === 'draft') {
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
        $status = $model->getAttribute('status');
        $statusClass = $status instanceof BackedEnum ? $status::class : ContentStatus::class;

        $model->forceFill(self::reconcile(self::attributes(
            $action,
            $scheduledFor,
            $current instanceof Carbon ? $current : null,
            $statusClass,
        ), $statusClass))->save();
    }
}
