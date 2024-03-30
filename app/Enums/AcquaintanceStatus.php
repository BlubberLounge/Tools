<?php

namespace App\Enums;

/**
 * AcquaintanceStatus enumaration
 */
enum AcquaintanceStatus: string
{
    case PENDING = 'pending';
    case ACCEPTED = 'accepted';
    case DENIED = 'denied';
}
