<?php

namespace App\Enums;

use App\Interfaces\EnumTypeOrStatus;

enum DartTournamentRoundStatus: string implements EnumTypeOrStatus
{
    case PENDING = 'pending';
    case RUNNING = 'running';
    case DONE = 'done';

    public static function fromString(string $string): DartTournamentRoundStatus
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
            DartTournamentRoundStatus::PENDING => 'secondary',
            DartTournamentRoundStatus::RUNNING => 'primary',
            DartTournamentRoundStatus::DONE => 'success',
        };
    }
}
