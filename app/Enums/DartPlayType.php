<?php

namespace App\Enums;

use App\Interfaces\EnumTypeOrStatus;

enum DartPlayType: string implements EnumTypeOrStatus
{
    case STANDARD = 'standard';
    case TEAM = 'team';
    case TOURNAMENT = 'tournament';

    public static function fromString(string $string): DartPlayType
    {
        foreach (self::cases() as $type)
            if (strtoupper($string) === strtoupper($type->name))
                return $type;

        throw new \ValueError("$string is not a valid backing value for enum " . self::class);
    }

    public function color(): string
    {
        return match($this)
        {
            DartPlayType::STANDARD => 'primary',
            DartPlayType::TEAM => 'warning',
            DartPlayType::TOURNAMENT => 'danger',
        };
    }
}
