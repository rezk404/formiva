<?php

declare(strict_types=1);

namespace App\Enums;

enum ClientStatus: string
{
    case Prospect = 'prospect';
    case Active = 'active';
    case Past = 'past';
}
