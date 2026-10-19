<?php

use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;
use App\Models\User;

/**
 *
 * Home
 *
 */
Breadcrumbs::for('home', function (BreadcrumbTrail $trail)
{
    $trail->push('Home', route('home'), ['faIcon' => 'fa-solid fa-house']);
});



/**
 *
 * Appointment
 *
 */
Breadcrumbs::for('appointment.index', function (BreadcrumbTrail $trail)
{
    $trail->parent('home');
    $trail->push('Event Dashboard', route('appointment.index'));
});



/**
 *
 * Dart
 *
 */
Breadcrumbs::for('dart.index', function (BreadcrumbTrail $trail)
{
    $trail->parent('home');
    $trail->push('dart dashboard', route('dart.index'));
});

Breadcrumbs::for('dart.show-info', function (BreadcrumbTrail $trail)
{
    $trail->parent('home');
    $trail->push('dart information', route('dart.show-info'));
});

Breadcrumbs::for('dart.show-checkout-calculator', function (BreadcrumbTrail $trail)
{
    $trail->parent('home');
    $trail->push('dart checkout calculator', route('dart.show-checkout-calculator'));
});



/**
 *
 * Dart Game
 *
 */
Breadcrumbs::for('dart.game.index', function (BreadcrumbTrail $trail)
{
    $trail->parent('home');
    $trail->push('dart game', route('dart.game.index'));
});



/**
 *
 * Minecraft
 *
 */
Breadcrumbs::for('minecraft.index', function (BreadcrumbTrail $trail)
{
    $trail->parent('home');
    $trail->push(__('minecraft'), route('minecraft.index'), ['faIcon' => 'fa-solid fa-cube']);
});

Breadcrumbs::for('minecraft.anvil-planner', function (BreadcrumbTrail $trail)
{
    $trail->parent('minecraft.index');
    $trail->push(__('anvil enchantment planner'), route('minecraft.anvil-planner'));
});

Breadcrumbs::for('minecraft.farm-catalogue', function (BreadcrumbTrail $trail)
{
    $trail->parent('minecraft.index');
    $trail->push(__('farm module catalogue'), route('minecraft.farm-catalogue'));
});

Breadcrumbs::for('minecraft.chain-planner', function (BreadcrumbTrail $trail)
{
    $trail->parent('minecraft.index');
    $trail->push(__('farm chain planner'), route('minecraft.chain-planner'));
});

Breadcrumbs::for('minecraft.farm-viewer', function (BreadcrumbTrail $trail)
{
    $trail->parent('minecraft.index');
    $trail->push(__('3D farm viewer'), route('minecraft.farm-viewer'));
});

Breadcrumbs::for('minecraft.portal-calculator', function (BreadcrumbTrail $trail)
{
    $trail->parent('minecraft.index');
    $trail->push(__('nether portal calculator'), route('minecraft.portal-calculator'));
});

Breadcrumbs::for('minecraft.stack-calculator', function (BreadcrumbTrail $trail)
{
    $trail->parent('minecraft.index');
    $trail->push(__('stack calculator'), route('minecraft.stack-calculator'));
});

Breadcrumbs::for('minecraft.material-calculator', function (BreadcrumbTrail $trail)
{
    $trail->parent('minecraft.index');
    $trail->push(__('material calculator'), route('minecraft.material-calculator'));
});

Breadcrumbs::for('minecraft.xp-calculator', function (BreadcrumbTrail $trail)
{
    $trail->parent('minecraft.index');
    $trail->push(__('XP calculator'), route('minecraft.xp-calculator'));
});

/**
 *
 * User
 *
 */
Breadcrumbs::for('user.index', function (BreadcrumbTrail $trail)
{
    $trail->parent('home');
    $trail->push('User', route('user.index'));
});

Breadcrumbs::for('user.settings', function (BreadcrumbTrail $trail)
{
    $trail->parent('user.index');
    $trail->push('Settings', route('user.settings'));
});

Breadcrumbs::for('user.edit', function (BreadcrumbTrail $trail, User $user)
{
    $trail->parent('user.index');
    $trail->push($user->full_name, route('user.edit', $user));
    // $trail->push('Edit - '. $user->full_name, route('user.edit', $user));
});

Breadcrumbs::for('user.edit-image', function (BreadcrumbTrail $trail, User $user)
{
    $trail->parent('user.edit', $user);
    $trail->push('Edit image', route('user.edit-image', $user));
});



/**
 *
 * Administration
 *
 */
Breadcrumbs::for('administration', function (BreadcrumbTrail $trail)
{
    $trail->parent('home');
    $trail->push('administration', '');
});



/**
 *
 * Invitation
 *
 */
Breadcrumbs::for('invitation.index', function (BreadcrumbTrail $trail)
{
    $trail->parent('administration');
    $trail->push('Zugangsanfragen', route('invitation.index'));
});



/**
 *
 * Feedback
 *
 */
Breadcrumbs::for('feedback.create', function (BreadcrumbTrail $trail)
{
    $trail->parent('administration');
    $trail->push('Feedback', route('feedback.create'));
});



/**
 *
 * FAQ
 *
 */
Breadcrumbs::for('faq.index', function (BreadcrumbTrail $trail)
{
    $trail->parent('administration');
    $trail->push('Feedback', route('faq.index'));
});
