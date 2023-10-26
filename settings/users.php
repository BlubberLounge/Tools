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
Setting::name('receiveNewsletter')
    ->boolean()
    ->default(false)
    ->group('notification');

Setting::name('receiveNewDeviceLogin')
    ->boolean()
    ->default(false)
    ->group('notification');

Setting::name('receiveDartGameInvitation')
    ->boolean()
    ->default(true)
    ->group('notification');

Setting::name('receiveDartGameReport')
    ->boolean()
    ->default(true)
    ->group('notification');

Setting::name('receiveDartGameWeeklyReport')
    ->boolean()
    ->default(false)
    ->group('notification');

Setting::name('receiveAccountChanges')
    ->boolean()
    ->default(true)
    ->group('notification');

/*
 *  Privacy and Security - SETTINGS
 */
Setting::name('isProfilePicturePublic')
    ->boolean()
    ->default(true)
    ->group('privacyAndSecurity');

Setting::name('isOnlineStatusPublic')
    ->boolean()
    ->default(false)
    ->group('privacyAndSecurity');

Setting::name('dartGameInvitation')
    ->boolean()
    ->default(true)
    ->group('privacyAndSecurity');

Setting::name('isDartGameStatisticPublic')
    ->boolean()
    ->default(false)
    ->group('privacyAndSecurity');

/*
 *  PRESETS - SETTINGS
 */
Setting::name('defaultDartGameTitle')
    ->string()
    ->default('')
    ->group('preset');


/*
 *  LANGUAGE - SETTINGS
 */
Setting::name('language')
    ->string()
    ->default('de')
    ->group('language');

Setting::name('languageEmail')
    ->string()
    ->default('de')
    ->group('language');


/*
 *  Testing
 */
Setting::name('notify_email')
    ->boolean()
    ->default(true)
    ->bag('notifications');
