<?php
namespace App\Enums;

enum BugStatuses: string
{
    case BACKLOG       = 'backlog';
    case OPEN          = 'open';
    case IN_PROGRESS   = 'in_progress';
    case IN_REVIEW     = 'in_review';
    case READY_FOR_QA  = 'ready_for_qa';
    case CLOSED        = 'closed';
    case REJECTED      = 'rejected';
    case REOPENED      = 'reopened';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}