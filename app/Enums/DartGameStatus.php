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
            DartGameStatus::UNKOWN => 'var(--bs-gray)',
            DartGameStatus::CREATED => 'var(--bs-success)',
            DartGameStatus::STARTED => 'var(--bs-success)',
            DartGameStatus::RUNNING => 'var(--bs-primary)',
            DartGameStatus::DONE => 'var(--bs-warning)',
            DartGameStatus::ABORTED => 'var(--bs-pink)',
            DartGameStatus::ERROR => 'var(--bs-danger)',
        };
    }
}
