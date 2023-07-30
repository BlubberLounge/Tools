<?php

namespace App\Enums;

use App\Interfaces\EnumTypeOrStatus;

/**
 * DartGameStatus enumaration
 */
enum DartGameStatus: string implements EnumTypeOrStatus
{
    case unkown = 'unkown';
    case created = 'created';
    case started = 'started';
    case running = 'running';
    case done = 'done';
    case aborted = 'aborted';
    case error = 'error';

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
            DartGameStatus::unkown => 'no color',
            DartGameStatus::created => 'no color',
            DartGameStatus::started => 'no color',
            DartGameStatus::running => 'no color',
            DartGameStatus::done => 'no color',
            DartGameStatus::aborted => 'no color',
            DartGameStatus::error => 'no color',
        };
    }
}
