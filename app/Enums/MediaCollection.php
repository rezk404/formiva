<?php

declare(strict_types=1);

namespace App\Enums;

enum MediaCollection: string
{
    case Gallery = 'gallery';
    case Cover = 'cover';
    case Logo = 'logo';
}
