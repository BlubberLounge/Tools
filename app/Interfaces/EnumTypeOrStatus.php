<?php

namespace App\Interfaces;

/**
 * EnumTypeOrStatus interface
 */
interface EnumTypeOrStatus
{
    public function color(): string;
    public static function fromString(string $string);
}
