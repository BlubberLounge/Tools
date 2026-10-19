<?php

namespace App\Services\BlubberLoungeAuth\Exceptions;

use RuntimeException;

/**
 * A JWT from the Auth server (id_token or logout_token) failed validation.
 */
class InvalidTokenException extends RuntimeException {}
