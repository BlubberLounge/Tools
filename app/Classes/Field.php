<?php

namespace App\Classes;


/**
 * Dartboard
 */
class Field
{
    /**
     *
     */
    public int $index;

    /**
     *
     */
    public int $value;

    /**
     *
     */
    public function __construct(int $index, int $value)
    {
        $this->index = $index;
        $this->value = $value;
    }
}
