<?php

namespace App\Helpers;

/**
 *
 */
class LinkHelper
{
    /**
     *
     */
    protected $className = 'active';

    /**
     *
     */
    public function __construct()
    {
        //
    }

    public static function active(array $routes): string
    {
        foreach($routes as $route)
            if(self::isActive($route))
                return self::$className;

        // in laravel
        // {{-- {{ LinkHelper::active() }} --}}
        return false;
    }

    protected static function isActive(string $route): bool
    {

        return true;
    }
}
