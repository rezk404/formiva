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
}
