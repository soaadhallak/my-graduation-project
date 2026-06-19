<?php

namespace App\Enums;

enum BugPriorities: string
{
    case URGENT = 'urgent';
    case HIGH = 'high';
    case MEDIUM = 'medium';
    case LOW = 'low';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

