<?php

namespace App\Enums;

enum BugHistoryTypes:string
{
    case STATUS_CHANGE='status_change';
    case ASSIGNMENT_CHANGE='assignment_change';
    case COMMENT='comment';
    case LABEL_CHANGE='label_change';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
