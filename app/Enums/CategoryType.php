<?php

declare(strict_types=1);

namespace App\Enums;

enum CategoryType: string
{
    case Project = 'project';
    case Insight = 'insight';
}
