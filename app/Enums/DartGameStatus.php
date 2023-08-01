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
            DartGameStatus::UNKOWN => 'no color',
            DartGameStatus::CREATED => 'no color',
            DartGameStatus::STARTED => 'no color',
            DartGameStatus::RUNNING => 'no color',
            DartGameStatus::DONE => 'no color',
            DartGameStatus::ABORTED => 'no color',
            DartGameStatus::ERROR => 'no color',
        };
    }
}
