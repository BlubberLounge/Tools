<?php

namespace App\Services\BlubberLoungeAuth\Exceptions;

use RuntimeException;
use Throwable;

/**
 * The single sign-on login could not be completed.
 *
 * The message is shown to the user on the login page, so it must not
 * contain technical details. Those go into the previous exception / log.
 */
class OAuthLoginException extends RuntimeException
{
    public function __construct(string $message, public readonly string $reason = 'error', ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
