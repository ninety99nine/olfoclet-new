<?php

namespace App\Enums;

enum CacheName:string
{
    case IS_SUPER_ADMIN = 'is super admin';

    public static function values(): array
    {
        return array_map(fn(self $unit) => $unit->value, self::cases());
    }
}
