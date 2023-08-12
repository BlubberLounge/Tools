<?php

namespace App\Enums;

/**
 * DeviceType enumaration
 */
enum DeviceType: string
{
    case UNKOWN = 'unkown';
    case MOBILE = 'mobile';
    case TABLET = 'tablet';
    case DESKTOP = 'desktop';
    case BOT = 'bot';
}
