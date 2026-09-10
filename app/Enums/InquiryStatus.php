<?php

declare(strict_types=1);

namespace App\Enums;

enum InquiryStatus: string
{
    case New = 'new';
    case Reviewing = 'reviewing';
    case Qualified = 'qualified';
    case Converted = 'converted';
    case Declined = 'declined';
    case Spam = 'spam';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::Reviewing => 'Reviewing',
            self::Qualified => 'Qualified',
            self::Converted => 'Converted',
            self::Declined => 'Declined',
            self::Spam => 'Spam',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::New => 'neutral',
            self::Reviewing => 'info',
            self::Qualified => 'positive',
            self::Converted => 'positive',
            self::Declined => 'muted',
            self::Spam => 'warning',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::New => 'New lead waiting for an initial review.',
            self::Reviewing => 'Under review by the studio team.',
            self::Qualified => 'Qualified and moving toward conversion.',
            self::Converted => 'Converted into a client record.',
            self::Declined => 'Not moving forward at this time.',
            self::Spam => 'Flagged as spam or invalid inbound submission.',
        };
    }
}
