<?php

namespace App\Enums;

enum UploadFolderName:string
{
    case APP_LOGO = 'app_logo';

    public static function values(): array
    {
        return array_map(fn(self $unit) => $unit->value, self::cases());
    }
}
