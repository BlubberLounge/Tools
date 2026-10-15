<?php

namespace App\Minecraft;

use RuntimeException;

/**
 * The chosen recipes of a chain loop without reaching a raw resource, or the
 * solver could not find a plan.
 */
final class ChainLoopException extends RuntimeException
{
    /**
     * @param string[] $items items on the loop (may be empty when unknown)
     */
    public function __construct(string $message, public readonly array $items = [], public readonly string $status = 'loop')
    {
        parent::__construct($message);
    }
}
