<?php

namespace toubilib\core\domain\exceptions;

/**
 * Exception lancée en cas de défaut d'authentification
 */
class AuthenticationException extends \Exception
{
    public function __construct(string $message = "Not authenticated", int $code = 401)
    {
        parent::__construct($message, $code);
    }
}
