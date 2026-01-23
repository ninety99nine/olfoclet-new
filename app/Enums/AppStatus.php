<?php

namespace App\Enums;

enum AppStatus: string
{
    case LIVE = 'live';
    case DRAFT = 'draft';

    public static function values(): array
    {
        return array_map(fn(self $unit) => $unit->value, self::cases());
    }
}
