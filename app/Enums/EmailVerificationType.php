<?php

namespace App\Enums;

enum EmailVerificationType:string {
    case UPDATED_EMAIL = 'updated email';
    case INVITED_EMAIL = 'invited email';
    case REGISTRATION_EMAIL = 'registration email';

    public static function values(): array
    {
        return array_map(fn(self $unit) => $unit->value, self::cases());
    }
}
