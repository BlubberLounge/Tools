<?php

namespace App\Enums;

use App\Interfaces\EnumTypeOrStatus;

/**
 * FeedbackType enumaration
 */
enum FeedbackType: string implements EnumTypeOrStatus
{
    case GENERAL = 'general';
    case BUG = 'bug';
    case INFORMATION = 'information';
    case ENHANCEMENT = 'enhancement';

    public static function fromString(string $string): FeedbackType
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
            FeedbackType::GENERAL => 'var(--bl-clr-gray-50)',
            FeedbackType::BUG => 'var(--bl-clr-bd-red)',
            FeedbackType::INFORMATION => 'var(--bl-clr-blue)',
            FeedbackType::ENHANCEMENT => 'var(--bl-clr-yellow)',
        };
    }
}
