<?php

namespace App\Enums;

enum FilterResourceType:string
{
    case USSD_SESSIONS = 'ussd sessions';
    case USSD_ACCOUNTS = 'ussd accounts';

    public static function values(): array
    {
        return array_map(fn(self $unit) => $unit->value, self::cases());
    }
}
