<?php

namespace App\Enums;

use App\Interfaces\EnumTypeOrStatus;

/**
 * DartGameType enumaration
 */
enum DartGameType: string implements EnumTypeOrStatus
{
    case X01 = 'X01';
    case aroundTheClock = 'aroundTheClock';
    case cricket = 'cricket';

    public static function fromString(string $string): DartGameType
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
            DartGameType::X01 => 'no color',
            DartGameType::aroundTheClock => 'no color',
            DartGameType::cricket => 'no color',
        };
    }
}
