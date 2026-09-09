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
}
