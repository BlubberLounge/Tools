<?php

namespace App\Enums;

use App\Interfaces\EnumTypeOrStatus;

/**
 * InvitationStatus enumaration
 */
enum InvitationStatus: string implements EnumTypeOrStatus
{
    case NEW = 'new';
    case UNKOWN = 'unkown';
    case APPROVED = 'approved';
    case DENIED = 'denied';
    case EXPIRED = 'expired';

    public static function fromString(string $string): InvitationStatus
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
            InvitationStatus::NEW => 'var(--bl-clr-yellow)',
            InvitationStatus::UNKOWN => 'var(--bl-clr-blue)',
            InvitationStatus::APPROVED => 'var(--bl-clr-green)',
            InvitationStatus::DENIED => 'var(--bl-clr-red)',
            InvitationStatus::EXPIRED => 'var(--bl-clr-red)',
        };
    }
}
