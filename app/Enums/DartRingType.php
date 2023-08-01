<?php

namespace App\Enums;

use App\Interfaces\EnumTypeOrStatus;

/**
 * DartFieldType enumaration
 */
enum DartRingType: string implements EnumTypeOrStatus
{
    case S = 'S';   // Single
    case D = 'D';   // Double
    case T = 'T';   // Tripple

    public static function fromString(string $string): DartRingType
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
            DartRingType::S => 'no color',
            DartRingType::D => 'no color',
            DartRingType::T => 'no color',
        };
    }
}
