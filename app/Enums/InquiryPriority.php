<?php

declare(strict_types=1);

namespace App\Enums;

enum InquiryPriority: string
{
    case Low = 'low';
    case Normal = 'normal';
    case High = 'high';
}
