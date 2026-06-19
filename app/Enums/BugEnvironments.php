<?php

namespace App\Enums;

enum BugEnvironments: string
{
    case LOCAL      = 'local';
    case STAGING    = 'staging';
    case PRODUCTION = 'production';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}