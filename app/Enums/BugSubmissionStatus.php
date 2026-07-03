<?php
namespace App\Enums;

enum BugSubmissionStatus: string
{
    case PENDING       = 'pending';
    case Approved      = 'approved';
    case REJECTED      = 'rejected';


    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}