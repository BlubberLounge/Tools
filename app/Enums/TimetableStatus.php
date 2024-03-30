<?php
namespace App\Enums;

/**
 * TimetableStatus enumaration
 */
enum TimetableStatus: string
{
    case AVAILABLE = 'available';
    case MAYBE = 'maybe';
    case NO_TIME = 'noTime';
}
