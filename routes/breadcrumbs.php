<?php

use Diglactic\Breadcrumbs\Breadcrumbs;
use Diglactic\Breadcrumbs\Generator as BreadcrumbTrail;

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
