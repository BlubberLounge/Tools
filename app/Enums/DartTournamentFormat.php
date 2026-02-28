<?php

namespace App\Enums;

use App\Interfaces\EnumTypeOrStatus;

enum DartTournamentFormat: string implements EnumTypeOrStatus
{
    case SINGLE_ELIMINATION = 'single_elimination';
    case ROUND_ROBIN = 'round_robin';

    public static function fromString(string $string): DartTournamentFormat
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
            DartTournamentFormat::SINGLE_ELIMINATION => 'danger',
            DartTournamentFormat::ROUND_ROBIN => 'info',
        };
    }
}
