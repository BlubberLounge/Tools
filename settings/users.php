<?php

use DarkGhostHunter\Laraconfig\Facades\Setting;

/*
 *  DESIGN - SETTINGS
 */
Setting::name('darkMode')
    ->string()
    ->default('auto')
    ->group('design');

/*
 *  NOTIFICATION - SETTINGS
 */
Setting::name('sendEmails')
    ->boolean()
    ->default(true)
    ->group('notification');

Setting::name('notifyMeWhenDeviceIsHijacked')
    ->boolean()
    ->default(true)
    ->group('notification');


/*
 *  DEVICE - SETTINGS
 */
Setting::name('trackMyDevices')
    ->boolean()
    ->default(true)
    ->group('device');


/*
 *  LANGUAGE - SETTINGS
 */
Setting::name('language')
    ->string()
    ->default('de')
    ->group('language');
