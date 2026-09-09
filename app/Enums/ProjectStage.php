<?php

declare(strict_types=1);

namespace App\Enums;

enum ProjectStage: string
{
    case Scoping = 'scoping';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case OnHold = 'on_hold';
}
