<?php

declare(strict_types=1);

namespace App\Enums;

enum SettingType: string
{
    case String = 'string';
    case Text = 'text';
    case Json = 'json';
    case Bool = 'bool';
    case Media = 'media';
}
