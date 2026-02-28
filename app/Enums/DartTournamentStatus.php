<?php

namespace App\Enums;

use App\Interfaces\EnumTypeOrStatus;

enum DartTournamentStatus: string implements EnumTypeOrStatus
{
    case CREATED = 'created';
    case SEEDED = 'seeded';
    case RUNNING = 'running';
    case DONE = 'done';
    case ABORTED = 'aborted';

    public static function fromString(string $string): DartTournamentStatus
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
            DartTournamentStatus::CREATED => 'secondary',
            DartTournamentStatus::SEEDED => 'info',
            DartTournamentStatus::RUNNING => 'primary',
            DartTournamentStatus::DONE => 'success',
            DartTournamentStatus::ABORTED => 'danger',
        };
    }
}
