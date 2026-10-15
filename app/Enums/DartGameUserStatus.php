<?php

namespace App\Enums;

use App\Interfaces\EnumTypeOrStatus;

/**
 * DartGameStatus enumaration
 */
enum DartGameUserStatus: string implements EnumTypeOrStatus
{
    case PENDING = 'pending';
    case ACCEPTED = 'accepted';
    case DENIED = 'denied';

    public static function fromString(string $string): DartGameUserStatus
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
            DartGameUserStatus::PENDING => '#6b7280',
            DartGameUserStatus::ACCEPTED => '#22c55e',
            DartGameUserStatus::DENIED => '#ef4444',
        };
    }
}
