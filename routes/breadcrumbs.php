<?php

use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;
use App\Models\User;

// Home
Breadcrumbs::for('home', function (BreadcrumbTrail $trail)
{
    $trail->push('Home', route('home'));
});

Breadcrumbs::for('dart.index', function (BreadcrumbTrail $trail)
{
    $trail->parent('home');
    $trail->push('Dart', route('dart.index'));
});

Breadcrumbs::for('user.index', function (BreadcrumbTrail $trail)
{
    $trail->parent('home');
    $trail->push('User Management', route('user.index'));
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

Breadcrumbs::for('feedback.create', function (BreadcrumbTrail $trail)
{
    $trail->parent('home');
    $trail->push('Feedback', route('feedback.create'));
});
