<?php

namespace toubilib\core\domain\exceptions;

/**
 * Exception lancée en cas d'échec d'authentification
 */
class AuthenticationException extends \Exception
{
    public function __construct(string $message = "Authentication failed", int $code = 401)
    {
        parent::__construct($message, $code);
    }
}