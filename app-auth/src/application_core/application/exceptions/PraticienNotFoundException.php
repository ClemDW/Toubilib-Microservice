<?php

namespace toubilib\core\application\exceptions;

use Exception;

/**
 * Exception lancée lorsqu'un praticien n'est pas trouvé
 */
class PraticienNotFoundException extends Exception
{
    public function __construct(string $praticienId)
    {
        parent::__construct("Praticien avec l'ID '{$praticienId}' non trouvé.", 404);
    }
}