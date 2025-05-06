<?php

namespace App\Enums;

enum CategoryStatus: int
{
    case Enabled = 1;
    case Disabled = 2;

    public function label(): string
    {
        return match ($this) {
            self::Enabled => 'Enabled',
            self::Disabled => 'Disabled',
        };
    }
}
