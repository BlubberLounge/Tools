<?php

namespace App\Enums;

use App\Interfaces\EnumTypeOrStatus;

enum DartTournamentMatchStatus: string implements EnumTypeOrStatus
{
    case PENDING = 'pending';
    case READY = 'ready';
    case RUNNING = 'running';
    case DONE = 'done';
    case BYE = 'bye';

    public static function fromString(string $string): DartTournamentMatchStatus
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
            DartTournamentMatchStatus::PENDING => 'secondary',
            DartTournamentMatchStatus::READY => 'info',
            DartTournamentMatchStatus::RUNNING => 'primary',
            DartTournamentMatchStatus::DONE => 'success',
            DartTournamentMatchStatus::BYE => 'light',
        };
    }
}
