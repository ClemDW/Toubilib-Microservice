<?php

namespace toubilib\core\application\exceptions;

use Exception;
use DateTime;

/**
 * Exception lancée lorsqu'un praticien n'est pas disponible pour un créneau
 */
class PraticienNotAvailableException extends Exception
{
    public function __construct(string $praticienId, DateTime $debut, DateTime $fin)
    {
        parent::__construct(
            "Le praticien avec l'ID '{$praticienId}' n'est pas disponible pour le créneau du " . 
            $debut->format('Y-m-d H:i') . " au " . $fin->format('Y-m-d H:i') . ".", 
            409
        );
    }
}