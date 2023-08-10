<?php

namespace App\Classes;


/**
 * Dartboard
 */
class Dartboard
{
    /**
     * Direction: clockwise
     */
    protected const FIELD_ORDER = [20, 1, 18, 4, 13, 6, 10, 15, 2, 17, 3, 19, 7, 16, 8, 11, 14, 9 ,12, 5];

    /**
     * From inside to outside relative size of each ring
     */
    protected const RING_DEFINITION = [6, 6, 32, 7, 28, 8, 13];

    /**
     *
     */
    protected array $fields;

    /**
     *
     */
    public function __construct() {
        $this->init();
    }

    /**
     *
     */
    private function init()
    {
        foreach($this::FIELD_ORDER as $i => $field)
            $fields[] = new Field($i, $field);
    }
}
