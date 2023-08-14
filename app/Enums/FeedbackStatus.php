<?php

namespace App\Enums;

use App\Interfaces\EnumTypeOrStatus;

/**
 * FeedbackStatus enumaration
 */
enum FeedbackStatus: string implements EnumTypeOrStatus
{
    case NEW = 'new';
    case SEEN = 'seen';
    case GOOD = 'good';
    case BAD = 'bad';

    public static function fromString(string $string): FeedbackStatus
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
            FeedbackStatus::NEW => 'var(--bl-clr-yellow)',
            FeedbackStatus::SEEN => 'var(--bl-clr-blue)',
            FeedbackStatus::GOOD => 'var(--bl-clr-green)',
            FeedbackStatus::BAD => 'var(--bl-clr-red)',
        };
    }
}
