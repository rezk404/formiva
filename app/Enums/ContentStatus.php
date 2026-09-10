<?php

declare(strict_types=1);

namespace App\Enums;

enum ContentStatus: string
{
    case Draft = 'draft';
    case Scheduled = 'scheduled';
    case Published = 'published';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Scheduled => 'Scheduled',
            self::Published => 'Published',
            self::Archived => 'Archived',
        };
    }

    /**
     * The badge tone used by <x-admin.badge>. Kept on the enum so a status
     * never picks up a different colour in one screen than in another.
     */
    public function tone(): string
    {
        return match ($this) {
            self::Draft => 'neutral',
            self::Scheduled => 'info',
            self::Published => 'positive',
            self::Archived => 'muted',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Draft => 'Not visible on the public site.',
            self::Scheduled => 'Goes live automatically at its publish date.',
            self::Published => 'Live on the public site.',
            self::Archived => 'Retired. Kept for the record, hidden publicly.',
        };
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }
}
