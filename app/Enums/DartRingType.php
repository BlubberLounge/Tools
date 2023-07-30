<?php

namespace App\Enums;

use App\Interfaces\EnumTypeOrStatus;

/**
 * DartFieldType enumaration
 */
enum DartFieldType: string implements EnumTypeOrStatus
{
    case S = 'single';
    case D = 'double';
    case T = 'tripple';

    public static function fromString(string $string): DartFieldType
    {
        foreach (self::cases() as $type)
            if (strtoupper($string) === $type->name)
                return $type;

        throw new \ValueError("$string is not a valid backing value for enum " . self::class );
    }

    public function color(): string
    {
        return match($this)
        {
            DartFieldType::S => 'no color',
            DartFieldType::D => 'no color',
            DartFieldType::T => 'no color',
        };
    }
}
