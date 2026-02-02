<?php

namespace toubilib\core\domain\exceptions;

/**
 * Exception lancée en cas de refus d'autorisation
 */
class AuthorizationException extends \Exception
{
    public function __construct(string $message = "Access denied", int $code = 403)
    {
        parent::__construct($message, $code);
    }
}