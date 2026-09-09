<?php

declare(strict_types=1);

namespace App\Enums;

enum InquiryKind: string
{
    case Project = 'project';
    case General = 'general';
}
