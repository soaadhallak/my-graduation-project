<?php

namespace App\Enums;

enum InvitationStatus:string
{
    case PENDING='pending';
    case ACCEPTED='accepted';
    case REVOKED='revoked';
    case EXPIRED='expired';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
