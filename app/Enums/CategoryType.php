<?php

declare(strict_types=1);

namespace App\Enums;

enum CategoryType: string
{
    case Project = 'project';
    case Insight = 'insight';

    public function label(): string
    {
        return match ($this) {
            self::Project => 'Project',
            self::Insight => 'Insight',
        };
    }

    /** What a category of this type is actually used for, in plain language. */
    public function purpose(): string
    {
        return match ($this) {
            self::Project => 'Groups selected work on /work and the project detail pages.',
            self::Insight => 'Labels journal entries on /insights and filters the index.',
        };
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }
}
