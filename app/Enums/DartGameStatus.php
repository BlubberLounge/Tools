<?php

namespace App\Enums;

use App\Interfaces\EnumTypeOrStatus;

/**
 * DartGameStatus enumaration
 */
enum DartGameStatus: string implements EnumTypeOrStatus
{
    case UNKOWN = 'unkown';
    case CREATED = 'created';
    case STARTED = 'started';
    case RUNNING = 'running';
    case DONE = 'done';
    case ABORTED = 'aborted';
    case ERROR = 'error';
    case INITIALISED = 'initialised';
    case PAUSED = 'paused';
    case PLAYER_WON = 'playerWon';
    case FINISHED = 'finished';

    public static function fromString(string $string): DartGameStatus
    {
        foreach (self::cases() as $status)
            if (strtoupper($string) === $status->name)
                return $status;

        throw new \ValueError("$string is not a valid backing value for enum " . self::class );
    }

    public function color(): string
    {
        return match($this)
        {
            DartGameStatus::UNKOWN => '#6b7280',
            DartGameStatus::CREATED => '#22c55e',
            DartGameStatus::STARTED => '#22c55e',
            DartGameStatus::RUNNING => '#3b82f6',
            DartGameStatus::DONE => '#f59e0b',
            DartGameStatus::ABORTED => '#ec4899',
            DartGameStatus::ERROR => '#ef4444',
            DartGameStatus::INITIALISED => '#06b6d4',
            DartGameStatus::PAUSED => '#64748b',
            DartGameStatus::PLAYER_WON => '#22c55e',
            DartGameStatus::FINISHED => '#f59e0b',
        };
    }
}
