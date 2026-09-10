<?php

declare(strict_types=1);

namespace App\Enums;

enum IntakeOptionKind: string
{
    case ProjectType = 'project_type';
    case Budget = 'budget';
    case Timeline = 'timeline';
    case CompanySize = 'company_size';
    case Service = 'service';

    public function label(): string
    {
        return match ($this) {
            self::ProjectType => 'Project types',
            self::Budget => 'Budget ranges',
            self::Timeline => 'Timelines',
            self::CompanySize => 'Company sizes',
            self::Service => 'Services in the room',
        };
    }

    /** The intake step this group of options is asked on. */
    public function step(): string
    {
        return match ($this) {
            self::ProjectType => 'Step 02 — What are you building?',
            self::Budget => 'Step 05 — What range is realistic?',
            self::Timeline => 'Step 06 — When should it move?',
            self::CompanySize => 'Step 03 — Give us the context.',
            self::Service => 'Step 04 — What needs to be in the room?',
        };
    }

    /**
     * Whether the group column carries meaning for this kind. Project types
     * declare the group; budgets are chosen by it. Everything else is a flat
     * list and stores the shared "all" group.
     */
    public function usesGroups(): bool
    {
        return $this === self::ProjectType || $this === self::Budget;
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }
}
