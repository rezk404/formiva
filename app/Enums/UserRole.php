<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Editor = 'editor';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Editor => 'Editor',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Admin => 'Full access, including site settings and staff accounts.',
            self::Editor => 'Manages content. No access to settings or staff accounts.',
        };
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }
}
